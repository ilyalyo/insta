<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Support;
use AppBundle\Form\Type\SupportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SupportController extends Controller
{
    /**
     * @Route("/support", name="support")
     */
    public function indexAction(Request $request)
    {
        $support = new Support();
        $user = $this->getUser();
        $support->setUser($user);
        $support->setIsRead(0);
        $support->setIsAnswer(0);

        $form = $this->createForm(new SupportType() ,$support);

        $em = $this->getDoctrine()->getManager();

        $history = $em->getRepository('AppBundle:Support')->findBy(array('user' => $user->getId()), array('createdAt' => 'DESC'));
        $history_unreaded = $em->getRepository('AppBundle:Support')->findBy(array('user' => $user->getId(), 'isRead' => 0));

        foreach($history_unreaded as $h)
        {
            $h->setIsRead(1);
            $em->persist($h);
        }
        $user->setUnRead(0);
        $em->persist($user);
        $em->flush();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($support);
            $em->flush();
            return  $this->redirectToRoute('support');
        }

        return $this->render('support/support.html.twig', array(
            'form' => $form->createView(),
            'history' =>$history
        ));
    }
}
