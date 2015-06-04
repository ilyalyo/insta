<?php

namespace TaskBundle\Controller;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use TaskBundle\Command\UnFollowCommand;
use TaskBundle\Command\WriteCommand;
use TaskBundle\Entity\Tasks;
use Symfony\Component\HttpFoundation\Request;
use TaskBundle\Entity\TaskType;

class UnfollowController extends Controller
{
    /**
     * @Route("/tasks/unfollow/{id}", name="unfollow")
     */
    public function unfollowAction($id,Request $request)
    {
        $task = new Tasks();
        //проверка на иньекцию чуждого акк ид
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id'=>$id));

        $task->setAccountId($account);

        $form = $this->createFormBuilder($task)
            ->add('count', 'text', array('label' => 'Количество'))
            ->getForm();

        $user = $this->getUser();
        if($user->getValidUntil() < date('now')){
            $form->get('tags')->addError(new FormError('Срок действия вашего аккаунта истек'));
            return $this->render('tasks/new_unfollow.html.twig', array(
                'form' => $form->createView(),
            ));
        }

        $running_task = $em->getRepository('TaskBundle:Tasks')->findBy(array(
            'account_id'=>$id,
            'status' => array(Tasks::RUNNING, Tasks::CREATED),
        ));

        if(count($running_task)>0){
            $form->get('count')->addError(new FormError('У вас уже есть работающая задача'));
            return $this->render('tasks/new_unfollow.html.twig', array(
                'form' => $form->createView(),
            ));
        }

        $form->handleRequest($request);

        if ($form->isValid()) {

            $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id'=>$id));
            if (!isset($account))
                throw new NotFoundHttpException("Page not found");
            $task->setAccountId($account);
            $task->setStatus(Tasks::CREATED);
            $task->setTags('');
            $task->setType(3);
            $task->setSpeed(1);
            $em = $this->getDoctrine()->getManager();
            $task->onPrePersist();
            $em->persist($task);
            $em->flush();

            $command = new WriteCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array('id' => $task->getId()));
            $output = new NullOutput();
            $command->run($input, $output);

            return $this->redirectToRoute('accounts');
        }



        return $this->render('tasks/new_unfollow.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
