<?php

namespace AppBundle\Controller;

use AppBundle\Command\AuthCheckCommand;
use Symfony\Component\ExpressionLanguage\Token;
use Symfony\Component\Form\FormError;
use AppBundle\Command\AuthCommand;
use AppBundle\Entity\Accounts;
use Symfony\Component\Console\Input\ArrayInput;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use TaskBundle\TaskBundle;
use TaskBundle\Command\GetTokenCommand;

class CasperAjaxController extends Controller
{
    const CLIENT_ID='6e336200a7f446a78b125602b90989cc';
    const CLIENT_SECRET='5e9449ed34a141d3925c852a4f6baa7e';
    const RESPONSE_TYPE='code';
    const REDIRECT_URL='http://instastellar.su/get_token?account_id=';
    const SCOPE='likes+comments+relationships';

    /**
     * @Route("/account/add_login_password", name="add_login_password_account")
     */
    public function addLoginPasswordAction(Request $request)
    {

        $account = new Accounts();
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder($account)
            ->add('instLogin', 'text', array('label' => 'Логин'))
            ->add('instPass', 'password', array(
                'label' => 'Пароль'))
            ->getForm();

        $accounts = $em->getRepository('AppBundle:Accounts')->findBy(array(
            'user'=>$user->getId()
        ));

        if(count($accounts) >= $user->getMaxAccounts()){
            $form->get('instLogin')->addError(new FormError('Превышен лимит числа аккаунтов'));
            return $this->render('accounts/login_password.html.twig',
                array('form' => $form->createView()));
        }

        $form->handleRequest($request);

        $exist = $em->getRepository('AppBundle:Accounts')->findBy(array(
            'instLogin'=>$account->getInstLogin()
        ));

        if(count($exist) > 0)
            $form->get('instLogin')->addError(new FormError('Аккаунт с таким логином уже существует'));

        /*Если этот акк уже удален этим пользователем, то мы его позволяем ему обратно добавить:*/
        $created_before = $em->getRepository('AppBundle:RemovedAccounts')->findOneBy(array(
            'instLogin' => $account->getInstLogin()
        ));
        if(count($created_before) > 0 && $user->getIsPro() == 0)
        {
            $ex_user = $created_before->getUser()->getId();
            /*При этом присваиваем старый айдишник, который был в базе, для сохранения статистики, если он сохранился(у старых акков он null):*/
            /*Пока что делаем это наивно, т.е. смотрим на логин, а не на id*/
            if($ex_user != $user->getId() || is_null($created_before->getIdDeleted()))
            {   $form->get('instLogin')->addError(new FormError('Этот аккаунт уже добавлялся, обратитесь в тех. поддержку'));   }
        }

        if ($form->isValid()) {

            $command = new AuthCheckCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array(
                'username'=>$account->getInstLogin(),
                'password' =>$account->getInstPass()
            ));

            $output1 =  new BufferedOutput();
            $output2=  new BufferedOutput();
            $command->run($input,$output1);
            if($output1->fetch() != 1 ) {
                $form->get('instLogin')->addError(new FormError('Неправильная пара логин пароль'));
                return $this->render('accounts/login_password.html.twig',
                    array('form' => $form->createView()));
            }

            $command->run($input,$output2);

            if($output2->fetch() != 1) {
                $form->get('instLogin')->addError(new FormError('Проблема авторизации обратитесь в тех. поддержку'));
                return $this->render('accounts/login_password.html.twig',
                    array('form' => $form->createView()));
            }

            $account->setUser($user);
            $em->persist($account);
            $em->flush();

            /*Делаем это здесь потому, что выше автоинкремент присваивает новый ID, игнорируя подобные изменения. Поэтому нужно делать после автоинкремента.*/
            /*Доп. проверка не нужна, т.к. она есть выше*/
            if(isset($ex_user) && $user->getIsPro() == 0)
            {
                $newid = $created_before->getIdDeleted();
                $account->setId($newid);
                $em->persist($account);
                $em->flush();
            }

            $command = new AuthCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array(
                'username'=>$account->getInstLogin(),
                'password' =>$account->getInstPass(),
                'account_id' => $account->getId()
            ));

            $output = new NullOutput();
            $command->run($input, $output);

            $check_account = $em->getRepository('AppBundle:Accounts')->find($account->getId());
            if(!isset($check_account))
                return $this->redirectToRoute('accounts');

            $this->addProvider($account,'easytogo');
            $this->addProvider($account,'extragram');
            $this->addProvider($account,'stapico ');
            $this->addProvider($account,'collecto');
            $this->addProvider($account,'test-socialhammer-app');

            return $this->redirectToRoute('accounts');
        }

        return $this->render('accounts/login_password.html.twig',
            array('form' => $form->createView()));
    }

    private function addProvider($account, $client){
        $command = new GetTokenCommand();
        $command->setContainer($this->container);
        $input = new ArrayInput(array(
            'username'=>$account->getInstLogin(),
            'password' =>$account->getInstPass(),
            'client' => $client
        ));

        $output =  new BufferedOutput();
        $command->run($input, $output);

        $token = new \AppBundle\Entity\Token();
        $token->setClient($client);
        $token->setAccount($account);
        $app_token = trim($output->fetch());
        $token->setToken($app_token);
        $em = $this->getDoctrine()->getManager();
        $em->persist($token);
        $em->flush();
    }

    /**
     * @Route("/account/check_login_password", name="check_login_password_account")
     */
    public function checkLoginPasswordAction(Request $request)
    {
        $login = $request->get('login');
        $password = $request->get('password');

        $em = $this->getDoctrine()->getManager();
        $exist = $em->getRepository('AppBundle:Accounts')->findBy(array(
            'instLogin'=>$login
        ));
        if(count($exist) > 0)
            return new JsonResponse(-1);

        $command = new AuthCheckCommand();
        $command->setContainer($this->container);
        $input = new ArrayInput(array(
            'username'=>$login,
            'password' =>$password
        ));

        $output =  new BufferedOutput();
        $command->run($input,$output);
        if($output->fetch() == 1)
            return new JsonResponse(1);
        else
            return new JsonResponse(0);
    }

    /**
     * @Route("/get_token", name="get_token")
     */
    public function getTokenAction(Request $request)
    {
        $url = 'https://api.instagram.com/oauth/access_token';
        $code = $request->get('code');
        $account_id = $request->get('account_id');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $params=array(
            'client_id'=>self::CLIENT_ID,
            'client_secret'=>self::CLIENT_SECRET,
            'grant_type'=>'authorization_code',
            'redirect_uri'=>self::REDIRECT_URL . $account_id,
            'code'=>$code);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        var_dump($response);
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('AppBundle:Accounts')->find($account_id);

        //проверяем не добавлялся ли аккаунт до этого(с другим username)
      /*  $account_check = $em->getRepository('AppBundle:Accounts')->findOneBy(array('account_id' => $response->user->id));
        var_dump(1);
        $removed_account_check = $em->getRepository('AppBundle:RemovedAccounts')->findOneBy(array('accountId' => $response->user->id));
        var_dump(2);
        if(isset($account_check) || isset($removed_account_check)){
            var_dump(3);
            $em->remove($account);
            $em->flush();
            return new JsonResponse(0);
        }
*/
        $account->setUsername($response->user->username);
        $account->setToken($response->access_token);
        $account->setAccountId($response->user->id);
        $account->setPicture($response->user->profile_picture);
        var_dump(1);

        $proxy = $em->getRepository('AppBundle:Proxy')->findAll();
        $proxy_count=$account->getId() % count($proxy);
        var_dump(2);
        $account->setProxy($proxy[$proxy_count]);
        var_dump(3);
        $em->persist($account);
        $em->flush();
        var_dump(4);

        return new JsonResponse($account->getId());
    }

    /**
     * @Route("/accounts/set/{account}/{result}", name="set_login_result")
     */
    public function setLoginResultAction($account,$result)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('AppBundle:Accounts')->find($account);
        $account->setIsTrue($result);
        $em->persist($account);
        $em->flush();
        return new JsonResponse('1');
    }

    /**
     * @Route("/accounts/is_exist", name="is_account_exist")
     */
    public function loginResultAction(Request $request)
    {
        $account = $request->get('account');
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('AppBundle:Accounts')->find($account);
        $token=$account->getToken();
        if(isset($token))
            return new JsonResponse(1);
        else
            return new JsonResponse(0);
    }
}
