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
use TaskBundle\Entity\Errors;
use TaskBundle\Entity\Lists;
use TaskBundle\Entity\ScheduleTasks;
use TaskBundle\Entity\Tasks;
use Symfony\Component\HttpFoundation\Request;
use TaskBundle\Entity\TaskType;
use TaskBundle\Form\Type\FollowByGeoType;
use TaskBundle\Form\Type\FollowByIdType;
use TaskBundle\Form\Type\FollowByListType;
use TaskBundle\Form\Type\FollowByTagsType;
use TaskBundle\Form\Type\LikeByGeoType;
use TaskBundle\Form\Type\LikeByTagsType;
use TaskBundle\Form\Type\SchedulerType;

class CreateController extends Controller
{
    /**
     * @Route("/tasks/add/FollowByList/{id}", name="add_task_follow_by_list")
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
        try{
            $without_spaces = str_replace(' ', '', $task->getTmpTags());

            $exp_ids = explode("\r\n", $without_spaces);
            if(count($exp_ids) > 500){
                $form->get('tmp_tags')->addError(new FormError('В списке должно быть Не более 500 ID'));
                return $this->render('tasks/follow/byList.html.twig', array(
                    'form' => $form->createView(),
                    'account' =>$account
                ));
            }

            if($task->getOptionAddLike() == 1)
                $task->setCount(count($exp_ids) * 2);
            else
                $task->setCount(count($exp_ids));

            $task->setAccountId($account);
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

        } catch (\Exception $e) {
        $errors = new Errors();
        $task = $em->getRepository('TaskBundle:Tasks')->find(-1);
        $errors->setTaskId($task);
        $m=substr($e->getMessage(),0,200);
        $errors->setMessage($m);
        $em->persist($errors);
        $em->flush();
    }
            return  $this->redirectToRoute('accounts');
        }

        return $this->render('tasks/follow/byList.html.twig', array(
            'form' => $form->createView(),
            'account' =>$account
        ));
    }

    /**
     * @Route("/tasks/add/FollowById/{id}", name="add_task_follow_by_id")
     */
    public function followByIdAction(Request $request, $id)
    {
        $task = new Tasks();
        $task->setType(0);
        $form = $this->createForm(new FollowByIdType(), $task);

        $form->handleRequest($request);
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id' => $id));
        if (!isset($account))
            throw new NotFoundHttpException("Page not found");
        $task->setAccountId($account);

        if($user->isExpired())
            $form->get('tags')->addError(new FormError('Срок действия вашего аккаунта истек'));

        $running_task = $em->getRepository('TaskBundle:Tasks')->countRunning($id);
        if($running_task > 0)
            $form->get('tags')->addError(new FormError('У вас уже есть работающая задача'));

        if($task->getOptionAddLike() == 1 && $task->getCount() > 500)
            $form->get('count')->addError(new FormError('При подписке с опцией лайкинг, количество должно быть менее 500'));

        if ($form->isValid()) {

            if($task->getOptionAddLike() == 1)
                $task->setCount($task->getCount() * 2);

            $tags=str_replace(" ","",$task->getTags());
            $task->setTags($tags);
            if($request->get('isScheduleTask'))
                $task->setStatus(Tasks::SCHEDULE_STEP1);
            $em->persist($task);
            $em->flush();

            if($request->get('isScheduleTask')){
                return  $this->redirectToRoute('add_task_scheduler', array('id' => $task->getId()));
            }

            $command = new WriteCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array('id' => $task->getId()));
            $output = new NullOutput();
            $command->run($input, $output);

            return  $this->redirectToRoute('accounts');
        }

        return $this->render('tasks/follow/byId.html.twig', array(
            'form' => $form->createView(),
            'account' =>$account
        ));
    }

    /**
     * @Route("/tasks/add/FollowByTags/{id}", name="add_task_follow_by_tags")
     */
    public function followByTagsAction(Request $request, $id)
    {
        $task = new Tasks();
        $task->setType(10);
        $form = $this->createForm(new FollowByTagsType(), $task);

        $form->handleRequest($request);
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id' => $id));
        if (!isset($account))
            throw new NotFoundHttpException("Page not found");
        $task->setAccountId($account);

        if($user->isExpired())
            $form->get('tags')->addError(new FormError('Срок действия вашего аккаунта истек'));

        $running_task = $em->getRepository('TaskBundle:Tasks')->countRunning($id);
        if($running_task > 0)
            $form->get('tags')->addError(new FormError('У вас уже есть работающая задача'));

        if($task->getOptionAddLike() == 1 && $task->getCount() > 500)
            $form->get('count')->addError(new FormError('При подписке с опцией лайкинг, количество должно быть менее 500'));

        if ($form->isValid()) {

            if($task->getOptionAddLike() == 1)
                $task->setCount($task->getCount() * 2);

            $tmp = str_replace(" ","",$task->getTags());
            $tags=trim($tmp,"#");
            $task->setTags($tags);
            $em->persist($task);
            $em->flush();

            $command = new WriteCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array('id' => $task->getId()));
            $output = new NullOutput();
            $command->run($input, $output);

            return  $this->redirectToRoute('accounts');
        }

        return $this->render('tasks/follow/byTags.html.twig', array(
            'form' => $form->createView(),
            'account' =>$account
        ));
    }

    /**
     * @Route("/tasks/add/FollowByGeo/{id}", name="add_task_follow_by_geo")
     */
    public function followByGeoAction(Request $request, $id)
    {
        $task = new Tasks();
        $task->setType(30);
        $form = $this->createForm(new FollowByGeoType(), $task);

        $form->handleRequest($request);
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id' => $id));
        if (!isset($account))
            throw new NotFoundHttpException("Page not found");
        $task->setAccountId($account);

        if($user->isExpired())
            $form->get('tags')->addError(new FormError('Срок действия вашего аккаунта истек'));

        $running_task = $em->getRepository('TaskBundle:Tasks')->countRunning($id);
        if($running_task > 0)
            $form->get('tags')->addError(new FormError('У вас уже есть работающая задача'));

        if($task->getOptionAddLike() == 1 && $task->getCount() > 500)
            $form->get('count')->addError(new FormError('При подписке с опцией лайкинг, количество должно быть менее 500'));

        if ($form->isValid()) {

            if($task->getOptionAddLike() == 1)
                $task->setCount($task->getCount() * 2);

            $tmp = str_replace(" ","",$task->getTags());
            $tags=trim($tmp,"#");
            $task->setTags($tags);
            $em->persist($task);
            $em->flush();

            $command = new WriteCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array('id' => $task->getId()));
            $output = new NullOutput();
            $command->run($input, $output);

            return  $this->redirectToRoute('accounts');
        }

        return $this->render('tasks/follow/byGeo.html.twig', array(
            'form' => $form->createView(),
            'account' =>$account
        ));
    }


    /**
     * @Route("/tasks/add/LikeByGeo/{id}", name="add_task_like_by_geo")
     */
    public function likeByGeoAction(Request $request, $id)
    {
        $task = new Tasks();
        $task->setType(31);
        $form = $this->createForm(new LikeByGeoType(), $task);

        $form->handleRequest($request);
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id' => $id));
        if (!isset($account))
            throw new NotFoundHttpException("Page not found");
        $task->setAccountId($account);

        if($user->isExpired())
            $form->get('count')->addError(new FormError('Срок действия вашего аккаунта истек'));

        $running_task = $em->getRepository('TaskBundle:Tasks')->countRunning($id);
        if($running_task > 0)
            $form->get('count')->addError(new FormError('У вас уже есть работающая задача'));

        if($task->getOptionAddLike() == 1 && $task->getCount() > 500)
            $form->get('count')->addError(new FormError('При подписке с опцией лайкинг, количество должно быть менее 500'));

        if ($form->isValid()) {

            $em->persist($task);
            $em->flush();

            $command = new WriteCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array('id' => $task->getId()));
            $output = new NullOutput();
            $command->run($input, $output);

            return  $this->redirectToRoute('accounts');
        }

        return $this->render('tasks/like/byGeo.html.twig', array(
            'form' => $form->createView(),
            'account' =>$account
        ));
    }

    /**
     * @Route("/tasks/add/LikeByTags/{id}", name="add_task_like_by_tags")
     */
    public function likeByTagsAction(Request $request, $id)
    {
        $task = new Tasks();
        $task->setType(11);
        $form = $this->createForm(new LikeByTagsType(), $task);

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

            $tmp =str_replace(" ","",$task->getTags());
            $tags=trim($tmp,"#");
            $task->setTags($tags);
            $task->setAccountId($account);
            $em->persist($task);
            $em->flush();

            $command = new WriteCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array('id' => $task->getId()));
            $output = new NullOutput();
            $command->run($input, $output);

            return  $this->redirectToRoute('accounts');
        }

        return $this->render('tasks/like/byTags.html.twig', array(
            'form' => $form->createView(),
            'account' =>$account
        ));
    }

    /**
     * @Route("/tasks/unFollow/{id}", name="add_task_unfollow")
     */
    public function unFollowAction($id,Request $request)
    {
        $task = new Tasks();
        $task->setType(3);
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id'=>$id));

        $task->setAccountId($account);

        $form = $this->createFormBuilder($task)
            ->add('count', 'text', array('label' => 'Количество'))
            ->getForm();

        $user = $this->getUser();

        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id' => $id));
        if (!isset($account))
            throw new NotFoundHttpException("Page not found");

        if($user->isExpired())
            $form->get('tags')->addError(new FormError('Срок действия вашего аккаунта истек'));

        $running_task = $em->getRepository('TaskBundle:Tasks')->countRunning($id);
        if($running_task > 0)
            $form->get('tags')->addError(new FormError('У вас уже есть работающая задача'));

        $form->handleRequest($request);

        if ($form->isValid()) {

            $task->setAccountId($account);
            $task->setSpeed(1);
            $em->persist($task);
            $em->flush();

            $command = new WriteCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array('id' => $task->getId()));
            $output = new NullOutput();
            $command->run($input, $output);

            return $this->redirectToRoute('accounts');
        }

        return $this->render('tasks/unfollow/unfollow.html.twig', array(
            'form' => $form->createView(),
            'account' =>$account
        ));
    }

    /**
     * @Route("/tasks/Scheduler/{id}", name="add_task_scheduler")
     */
    public function schedulerAction($id, Request $request)
    {
        $scheduler_task = new ScheduleTasks();
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $task = $em->getRepository('TaskBundle:Tasks')->find($id);
        if (!isset($task))
            throw new NotFoundHttpException("Page not found");

        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id' => $task->getAccountId()));
        if (!isset($account))
            throw new NotFoundHttpException("Page not found");

        $form = $this->createForm(new SchedulerType(), $scheduler_task);
            //, array(       'view_timezone' => $user->getTimezone()));

        $form->handleRequest($request);

        if ($form->isValid()) {

            $task->setStatus(Tasks::SCHEDULE_DONE);
            foreach($scheduler_task->getDays() as $day)
            {
                $scheduler_task = new ScheduleTasks();
                $scheduler_task->setTaskId($task);
                $scheduler_task->setRunAt( (new \DateTime())->add(new \DateInterval("P" . $day . "D")));
                $em->persist($scheduler_task);
            }
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('accounts');
        }


        return $this->render('tasks/scheduler.html.twig', array(
            'form' => $form->createView(),
            'account' =>$account
        ));
    }
}
