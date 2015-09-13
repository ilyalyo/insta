<?php

namespace AppBundle\Controller;

use AppBundle\Command\AuthCheckCommand;
use AppBundle\Entity\AccountsLog;
use AppBundle\Utils\InstWorker;
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
    const REDIRECT_URL='http://instastellar.su/get_token';
    const SCOPE='likes+comments+relationships';

    /**
     * @Route("/account/add_login_password", name="add_login_password_account")
     */
    public function addLoginPasswordAction(Request $request)
    {

        $account = new Accounts();
        $user = $this->getUser();
        $account->setUser($user);
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder($account)
            ->add('instLogin', 'text', array('label' => 'Логин'))
            ->add('instPass', 'password', array(
                'label' => 'Пароль'))
            ->add('country', 'entity', array(
                'class' => 'AppBundle:Countries',
                'property' => 'country_name',
                'label' => 'Страна'))
            ->getForm();

        $accounts = $em->getRepository('AppBundle:Accounts')->findBy(array(
            'user'=>$user->getId()
        ));

        $form->handleRequest($request);

        if(count($accounts) >= $user->getMaxAccounts())
            $form->get('instLogin')->addError(new FormError('Превышен лимит числа аккаунтов'));

        $exist = $em->getRepository('AppBundle:Accounts')->findOneBy(array(
            'instLogin'=>$account->getInstLogin()
        ));

        if(isset($exist))
            $form->get('instLogin')->addError(new FormError('Аккаунт с таким логином уже существует'));

        /*Если этот акк уже удален этим пользователем, то мы его позволяем ему обратно добавить:*/
        $created_before = $em->getRepository('AppBundle:RemovedAccounts')->findOneBy(array(
            'instLogin' => $account->getInstLogin()
        ));

        if(count($created_before) > 0)
        {
            $ex_user = $created_before->getUser()->getId();
            /*При этом присваиваем старый айдишник, который был в базе, для сохранения статистики, если он сохранился(у старых акков он null):*/
            /*Пока что делаем это наивно, т.е. смотрим на логин, а не на id*/
            if($ex_user != $user->getId() || is_null($created_before->getIdDeleted()))
            {   $form->get('instLogin')->addError(new FormError('Этот аккаунт уже добавлялся, обратитесь в тех. поддержку'));   }
        }

        if ($form->isValid()) {

            $proxy = $this->chooseProxy($account->getCountry());

            $iw = new InstWorker(
                $account->getInstLogin(),
                $account->getInstPass(),
                $user->getId(),//используем id юзера, а не акка, тк у акка еще нет ид, удаляем его сразу же
                $proxy->getIp() . ':' . $proxy->getPort()
                );

            $iw->Login();
            $iw->InstallApp('easytogo');
            $iw->removeCookie();

            /* $command = new AuthCommand();
             $command->setContainer($this->container);
             $input = new ArrayInput(array(
                 'username'=>$account->getInstLogin(),
                 'password' =>$account->getInstPass(),
                 'proxy' => $proxy->getIp() . ':' . $proxy->getPort()
             ));

             $output = new NullOutput();
             $command->run($input, $output);
 */
            $new_account = $em->getRepository('AppBundle:Accounts')->findOneBy(array(
                'instLogin' => $account->getInstLogin(),
                'user' => null));

            if(!isset($new_account)){
                $accountsLog = new AccountsLog($account);
                $accountsLog->setIp($request->getClientIp());
                $xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=" . $request->getClientIp());
                $accountsLog->setIp($request->getClientIp());
                $accountsLog->setCountryFrom($xml->geoplugin_countryCode);
                $em->persist($accountsLog);
                $em->flush();
                $form->get('instLogin')->addError(new FormError('Возможно неправильная пара логин и пароль'));
                return $this->render('accounts/login_password.html.twig',
                    array('form' => $form->createView()));
            }

            $new_account->setUser($this->getUser());
            $new_account->setInstPass($account->getInstPass());
            $new_account->setCountry($account->getCountry());
            $new_account->setProxy($proxy);
            $em->persist($new_account);
            $em->flush();

            /*Делаем это здесь потому, что выше автоинкремент присваивает новый ID, игнорируя подобные изменения. Поэтому нужно делать после автоинкремента.*/
            /*Доп. проверка не нужна, т.к. она есть выше*/
            if(isset($ex_user))
            {
                $newid = $created_before->getIdDeleted();
                $qb = $em->createQueryBuilder();
                $q = $qb->update('AppBundle:Accounts', 'acc')
                    ->set('acc.id', '?1')
                    ->where('acc.id = ?2')
                    ->setParameter(1, $newid)
                    ->setParameter(2, $new_account->getId())
                    ->getQuery();
                $q->execute();
                $em->remove($created_before);
                $em->flush();
                $new_account = $em->getRepository('AppBundle:Accounts')->find($newid);
            }

            $token = new \AppBundle\Entity\Token();
            $token->setClient('easytogo');
            $token->setAccount($new_account);
            $token->setToken($new_account->getToken());
            $em->persist($token);
            $em->flush();

            $this->addProvider($new_account,'stapico');
            $this->addProvider($new_account,'collecto');
            $this->addProvider($new_account,'test-socialhammer-app');

            return $this->redirectToRoute('accounts');
        }

        return $this->render('accounts/login_password.html.twig',
            array('form' => $form->createView()));
    }

    private function addProvider($account, $client){
        $token = new \AppBundle\Entity\Token();
        $token->setClient($client);
        $token->setAccount($account);
        $token->setToken('null');
        $em = $this->getDoctrine()->getManager();
        $em->persist($token);
        $em->flush();
    }

    private function chooseProxy($country){
        $em = $this->getDoctrine()->getManager();
        $proxy = $em->getRepository('AppBundle:Proxy')->findBy(
            array('country' => $country)
        );
        $all_proxy_by_country = $em->getRepository('AppBundle:Accounts')->findBy(array('country' => $country));
        $proxy_count = count($all_proxy_by_country) % (count($proxy));

        return $proxy[$proxy_count];
    }


    /**
     * @Route("/get_token", name="get_token")
     */
    public function getTokenAction(Request $request)
    {
        $url = 'https://api.instagram.com/oauth/access_token';
        $code = $request->get('code');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $params=array(
            'client_id'=>self::CLIENT_ID,
            'client_secret'=>self::CLIENT_SECRET,
            'grant_type'=>'authorization_code',
            'redirect_uri'=>self::REDIRECT_URL,
            'code'=> $code);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(['account_id' => $response->user->id ]);
        if(isset($account)){
            $account->setToken($response->access_token);
            $em->persist($account);
            $em->flush();
            return new JsonResponse($account->getId());
        }

        $account = new Accounts();
        $account->setUsername($response->user->username);
        $account->setInstLogin($response->user->username);
        $account->setToken($response->access_token);
        $account->setAccountId($response->user->id);
        $account->setPicture($response->user->profile_picture);
        $em->persist($account);
        $em->flush();

        return new JsonResponse($account->getId());
    }
}
