<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Accounts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    const CLIENT_ID='6e336200a7f446a78b125602b90989cc';
    const CLIENT_SECRET='5e9449ed34a141d3925c852a4f6baa7e';
    const RESPONSE_TYPE='code';
    const REDIRECT_URL='http://instastellar.su/get_token';
    const SCOPE='likes+comments+relationships';

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('landing.html.twig');
    }

    /**
     * @Route("/purchase", name="purchase")
     */
    public function purchaseAction()
    {
        return $this->render('app/purchase.html.twig');
    }

    /**
     * @Route("/info", name="info")
     */
    public function infoxAction()
    {
        return $this->render('info.html.twig');
    }

    /**
     * @Route("/accounts", name="accounts")
     */
    public function accountsAction()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $accounts = $em->getRepository('AppBundle:Accounts')->findBy(array(
            'user' => $user->getId())
        );

        return $this->render(
            'accounts/index.html.twig',
            [
                'accounts' => $accounts,
                'user' => $user
            ]
        );
    }

    /**
     * @Route("/account/add", name="add_account")
     */
    public function addAction()
    {
        return $this->redirect('https://instagram.com/oauth/authorize/?client_id=6e336200a7f446a78b125602b90989cc&response_type=code&redirect_uri=http://instastellar.su/get_token&scope=likes+comments+relationships');
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
            'code'=>$code);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $account_id=$response->user->id;

/*        $exist= $em->getRepository('AppBundle:Accounts')->findOneBy(array('account_id' => $account_id));
        if(isset($exist))
            return $this->redirectToRoute('accounts');
*/

        $accounts = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(), 'account_id' => $account_id));

        if(!isset($accounts)){

            $count = $em->getRepository('AppBundle:Accounts')->findBy(array('user' => $user->getId()));
            if(isset($count))
                if(count($count)>1) {
                    $v=count($count);
                    $this->addFlash(
                        'notice',
                        "Нельзя добавить более 2х аккаунтов!"
                    );
                    return $this->redirectToRoute('accounts');
                }

            $account= new Accounts();
            $account->setUser($user);
            $account->setUsername($response->user->username);
            $account->setToken($response->access_token);
            $account->setAccountId($response->user->id);

            $em->persist($account);
            $em->flush();

            $proxy = $em->getRepository('AppBundle:Proxy')->findAll();
            $proxy_count=$account->getId() % count($proxy);
           // var_dump($proxy);
            var_dump($proxy[$proxy_count]->getId());
            $account->setProxy($proxy[$proxy_count]->getId());

            $em->persist($account);
            $em->flush();

            $this->addFlash(
                'notice',
                'Аккаунт добавлен!'
            );
        }
        else{
            $accounts->setToken($response->access_token);
            $em->persist($accounts);
            $em->flush();
            $this->addFlash(
                'notice',
                'Токен обновлен!'
            );
        }
      return $this->redirectToRoute('accounts');
    }
}
