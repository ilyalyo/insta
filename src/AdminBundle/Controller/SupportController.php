<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\Type\SupportType;
use AppBundle\Entity\Support;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SupportController extends Controller
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin", name="admin")
     */
    public function indexAction()
    {
        return $this->render(
            'admin/index.html.twig'
        );
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/support", name="admin_support")
     */
    public function supportAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('users', 'entity', array(
             'class' => 'UserBundle:User',
             'query_builder' => function(EntityRepository $repository) {
                 return $repository->createQueryBuilder('u')->orderBy('u.username', 'ASC');
             },
             'property' => 'username'))
            ->getForm();

        $em = $this->getDoctrine()->getManager();
        $users_with_new_msg = $em->getRepository('AppBundle:Support')->getUsersWithNewMessages();
        $top10Users = $em->getRepository('AppBundle:Support')->getTop10Users();

        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->redirect($this->generateUrl('admin_support_msg',array('id' => $form->get('users')->getViewData() )));
        }

        return $this->render('admin/support.html.twig', array(
            'form' => $form->createView(),
            'users_with_new_msg' => $users_with_new_msg,
            'top10Users' => $top10Users
        ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/support/{id}", name="admin_support_msg")
     */
    public function supportMsgAction(Request $request, $id)
    {
        $support = new Support();
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('UserBundle:User')->find($id);
        $support->setUser($client);
        $support->setIsRead(0);
        $support->setIsAnswer(1);
        $form = $this->createForm(new SupportType(), $support);

        $history = $em->getRepository('AppBundle:Support')->findBy(array('user' => $id));
        $history_unread = $em->getRepository('AppBundle:Support')->findBy(array('user' => $id,'isRead' => 0, 'isAnswer' => 0));

        foreach($history_unread as $h)
        {
            $h->setIsRead(1);
            $em->persist($h);
        }
        $em->flush();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $client->setUnRead($client->getUnRead() + 1);
            $em->persist($client);
            $em->persist($support);
            $em->flush();

            if($support->getIsDuplicateToEmail()){
                $message = \Swift_Message::newInstance()
                    ->setSubject('Обращение из тех. поддержки')
                    ->setFrom('support@instastellar.su')
                    ->setTo($client->getEmail())
                    ->setBody(
                        'Вам поступило новое сообщение из тех. поддержки сервиса Instastellar:' .
                        $support->getMessage()
                    );
                $this->get('mailer')->send($message);
            }

            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('admin/support_msg.html.twig', array(
            'form' => $form->createView(),
            'history' =>$history,
            'client' =>$client,
        ));
    }
}
