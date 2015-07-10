<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Accounts;
use AppBundle\Entity\RemovedAccounts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('landing.html.twig');
    }

    /**
     * @Route("/additional_info", name="additional_info")
     */
    public function additional_infoAction()
    {
        return $this->render('offer_and_confidentiality.html.twig');
    }

    /**
     * @Route("/info", name="info")
     */
    public function infoAction()
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
     * @Route("/account/delete/{id}", name="delete_account")
     */
    public function deleteAction($id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('id' => $id, 'user'=>$user->getId()));
        if(!isset($account))
            throw new NotFoundHttpException("Page not found");

        //if user expired we save login in db to prevent him to use this
        // instagram acc with different instastellar acc
        if($user->getIsPro() == 0) {
            $removed_account = new RemovedAccounts();
            $removed_account->setInstLogin($account->getInstLogin());
            $removed_account->setAccountId($account->getAccountId());
            $removed_account->setUser($user);
            $em->persist($removed_account);
        }

        $em->remove($account);
        $em->flush();

        return  $this->redirectToRoute('accounts');
    }

}
