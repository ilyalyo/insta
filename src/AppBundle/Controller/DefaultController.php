<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Accounts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @Route("/settings")
     */
    public  function setTimezone()
    {
        $form = $this->createFormBuilder()
            ->add('states', 'timezone', array(
                'preferred_choices' => array('Europe/Moscow', 'Europe/Kiev'),
            ))
            ->getForm();


        return $this->render('settings/index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/account/add", name="add_account")
     */
    public function addAction()
    {
        return $this->redirect('https://instagram.com/oauth/authorize/?client_id=6e336200a7f446a78b125602b90989cc&response_type=code&redirect_uri=http://instastellar.su/get_token&scope=likes+comments+relationships');
    }
}
