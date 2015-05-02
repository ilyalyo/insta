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
use TaskBundle\Command\WriteCommand;
use TaskBundle\Entity\Tasks;
use Symfony\Component\HttpFoundation\Request;
use TaskBundle\Entity\TaskType;

class DefaultController extends Controller
{
    /**
     * @Route("/tasks", name="tasks")
     */
    public function indexAction()
    {
        $user = $this->getUser();

        $repository = $this->getDoctrine()
            ->getRepository('TaskBundle:Tasks');

        $query = $repository->createQueryBuilder('t')
            ->select('t as task, a.username')
            ->join('t.account_id', 'a')
            ->where('a.user = :user_id')
            ->setParameter('user_id', $user->getId())
            ->getQuery();

        $tasks = $query->getResult();

        return $this->render(
            'tasks/index.html.twig',
            [
                'tasks' => $tasks,
            ]
        );
    }

    /**
     * @Route("/tasks/add/{isbyUsername}/{type}/{id}", name="add_tasks")
     */
    public function addAction(Request $request,$isbyUsername,$type,$id)
    {
        $task = new Tasks();
        $task->setByUsername($isbyUsername);
        $task->setType($type);
        $label="Задача на лайкинг";
        if($task->getType()==0)
            $label = "Задача на фоловинг";

        if($task->getByUsername()==0) {
            $form = $this->createFormBuilder($task)
                ->add('count', 'text', array('label' => 'Количество'))
                ->add('tags', 'textarea', array('label' => 'Тэги'))
                ->getForm();
        }
        else
            $form = $this->createFormBuilder($task)
                ->add('count', 'text',array('label' => 'Количество'))
                ->add('tags', 'text',array('label' => 'ID'))
                ->getForm();

        $em = $this->getDoctrine()->getManager();
        $running_task = $em->getRepository('TaskBundle:Tasks')->findBy(array(
            'account_id'=>$id,
            'status' => array(Tasks::RUNNING, Tasks::CREATED),
        ));

        if(isset($running_task)){
            $form->get('tags')->addError(new FormError('У вас уже есть работающая задача'));
            return $this->render('tasks/new.html.twig', array(
                'form' => $form->createView(),
                'label' => $label
            ));
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            //проверка на иньекцию чуждого акк ид

            $user = $this->getUser();
            $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id'=>$id));
            if (!isset($account))
                throw new NotFoundHttpException("Page not found");

            $task->setAccountId($account);
            $task->setStatus(Tasks::CREATED);
            $em = $this->getDoctrine()->getManager();
            $task->onPrePersist();
            $em->persist($task);
            $em->flush();

            $command = new WriteCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array('id' => $task->getId()));
            $output = new NullOutput();
            $command->run($input, $output);

            return  $this->redirectToRoute('accounts');
        }
/*
        if($request->getMethod()=='POST'){
            $formErrors = $this->get('form_errors')->getArray($form);
            return new JsonResponse($formErrors);
        }*/

        return $this->render('tasks/new.html.twig', array(
            'form' => $form->createView(),
            'label' => $label
        ));
    }


    /**
     * @Route("/tasks/stop/{id}", name="stop_tasks")
     */
    public function stopAction(Request $request, $id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository('TaskBundle:Tasks')->findOneBy(array('id' => $id));
        $task->setStatus(3);
        $em->persist($task);
        $em->flush();
        return $this->redirectToRoute('accounts');
    }
}
