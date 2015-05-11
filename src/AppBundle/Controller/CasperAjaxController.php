<?php

namespace AppBundle\Controller;

use AppBundle\Command\AuthCommand;
use AppBundle\Entity\Accounts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
        $form = $this->createFormBuilder($account)
            ->add('instLogin', 'text', array('label' => 'Логин'))
            ->add('instPass', 'password', array(
                'label' => 'Пароль'))
            ->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($account);
            $em->flush();

            $command = new AuthCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array(
                'username'=>$account->getInstLogin(),
                'password' =>$account->getInstPass(),
                'account_id' => $account->getId()
            ));

            $output = new NullOutput();
            $command->run($input, $output);

            return new JsonResponse('step1');
        }
        return $this->render('accounts/login_password.html.twig',
            array('form' => $form->createView()));
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
            'redirect_uri'=>self::REDIRECT_URL,
            'code'=>$code);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        $em = $this->getDoctrine()->getManager();

        //$account= new Accounts();
        $account = $em->getRepository('AppBundle:Accounts')->find($account_id);
        $account->setUsername($response->user->username);
        $account->setToken($response->access_token);
        $account->setAccountId($response->user->id);

        //$em->persist($account);
        //$em->flush();

        $proxy = $em->getRepository('AppBundle:Proxy')->findAll();
        $proxy_count=$account->getId() % count($proxy);
        $account->setProxy($proxy[$proxy_count]);

        $em->persist($account);
        $em->flush();

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
     * @Route("/accounts/get/{account}", name="get_login_result")
     */
    public function loginResultAction($account)
    {
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('AppBundle:Accounts')->find($account);
        return new JsonResponse($account->getIsTrue());
    }
}
