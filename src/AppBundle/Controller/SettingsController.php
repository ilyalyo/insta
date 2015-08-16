<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SettingsController extends Controller
{
    /**
    * @Route("/settings", name="settings")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

         $formTz = $this->get('form.factory')->createNamedBuilder( 'form1name', 'form', $user)
            ->add('timezone', 'timezone')
            ->getForm();

        $formFactory = $this->get('fos_user.change_password.form.factory');

        $formP = $formFactory->createForm();
        $formP->setData($user);

        if('POST' === $request->getMethod()) {
            $userManager = $this->get('fos_user.user_manager');
            if ($request->request->has('form1name')) {
                $formTz->handleRequest($request);
                if ($formTz->isValid()) {
                    $userManager->updateUser($user);
                }
            }

            if ($request->request->has('form2name')) {
                $formP->handleRequest($request);
                if ($formP->isValid()) {
                    $user->setPlainPassword($formP->get('password')->getData());
                    $userManager->updateUser($user);
                }
            }
            return  $this->redirectToRoute('settings');
        }

        return $this->render(
            'settings/index.html.twig', array(
            'formTz' => $formTz->createView(),
            'formP' => $formP->createView()
        ));
    }
}
