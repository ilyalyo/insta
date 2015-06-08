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
use Symfony\Component\Validator\Exception\ValidatorException;
use TaskBundle\Command\WriteCommand;
use TaskBundle\Entity\Actions;
use TaskBundle\Entity\Lists;
use TaskBundle\Entity\Tasks;
use Symfony\Component\HttpFoundation\Request;
use TaskBundle\Entity\TaskType;
use TaskBundle\Form\Type\FollowByIdType;
use TaskBundle\Form\Type\FollowByListType;
use TaskBundle\Form\Type\FollowByTagsType;

class DefaultController extends Controller
{
    /**
     * @Route("/tasks/add/followByList/{id}", name="followByList")
     */
    public function followByListAction(Request $request, $id)
    {
        $task = new Tasks();
        $task->setType(20);
        $form = $this->createForm(new FollowByListType(), $task);

        $form->handleRequest($request);
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id' => $id));
        if (!isset($account))
            throw new NotFoundHttpException("Page not found");

        if($user->isExpired())
            $form->get('tmp_tags')->addError(new FormError('Срок действия вашего аккаунта истек'));

        $running_task = $em->getRepository('TaskBundle:Tasks')->countRunning($id);
        if($running_task > 0)
            $form->get('tmp_tags')->addError(new FormError('У вас уже есть работающая задача'));

        if ($form->isValid()) {

            $without_spaces = str_replace(' ', '', $task->getTmpTags());

            $exp_ids = explode("\r\n", $without_spaces);
            if(count($exp_ids) > 500){
                $form->get('tags')->addError(new FormError('В списке должно быть Не более 500 ID'));
                return $this->render('tasks/followByList.html.twig', array(
                    'form' => $form->createView(),
                    'account' =>$account
                ));
            }

            $task->setCount(count($exp_ids));
            $task->setAccountId($account);
            $task->setStatus(Tasks::CREATED);
            $task->setTags('');
            $task->onPrePersist();
            $em->persist($task);

            $list = new Lists();
            $list->setList($without_spaces);
            $list->setTask($task);
            $em->persist($list);

            $em->flush();

            $command = new WriteCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array('id' => $task->getId()));
            $output = new NullOutput();
            $command->run($input, $output);

            return  $this->redirectToRoute('accounts');
        }

        return $this->render('tasks/followByList.html.twig', array(
            'form' => $form->createView(),
            'account' =>$account
        ));
    }

    /**
     * @Route("/tasks/add/{type}/{id}", name="add_tasks")
     */
    public function addAction(Request $request,$type,$id)
    {
        $task = new Tasks();
        $task->setType($type);
        $label="Задача на лайкинг";
        if(in_array($task->getType(), [0 , 10, 20]))
            $label = "Задача на фоловинг";

        switch($task->getType()){
            case 0:
                $form = $this->createForm(new FollowByIdType(), $task);
                break;
            case 10:
                $form = $this->createForm(new FollowByTagsType(), $task);
                break;
            default:
                $form = $this->createForm(new FollowByTagsType(), $task);
        }


        $form->handleRequest($request);
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id' => $id));
        if (!isset($account))
            throw new NotFoundHttpException("Page not found");

        if($user->isExpired())
            $form->get('tags')->addError(new FormError('Срок действия вашего аккаунта истек'));

        $running_task = $em->getRepository('TaskBundle:Tasks')->countRunning($id);
        if($running_task > 0)
            $form->get('tags')->addError(new FormError('У вас уже есть работающая задача'));

        if ($form->isValid()) {

            $r=str_replace(" ","",$task->getTags());
            $t=trim($r,"#");
            $task->setTags($t);
            $task->setAccountId($account);
            $task->setStatus(Tasks::CREATED);
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

        return $this->render('tasks/new.html.twig', array(
            'form' => $form->createView(),
            'account' =>$account

        ));
    }


    /**
     * @Route("/tasks/stop/{id}", name="stop_tasks")
     */
    public function stopAction(Request $request, $id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $user_id = $em->getRepository('TaskBundle:Tasks')->getUserId($id);
        if (!isset($user_id) || $user->getId() != $user_id)
            throw new NotFoundHttpException("Page not found");

        $task = $em->getRepository('TaskBundle:Tasks')->findOneBy(array('id' => $id));
        $task->setStatus(3);
        $em->persist($task);
        $em->flush();
        return $this->redirectToRoute('accounts');
    }
}
