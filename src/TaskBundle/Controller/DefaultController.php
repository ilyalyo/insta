<?php

namespace TaskBundle\Controller;

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
     * @Route("/tasks/add", name="add_tasks")
     */
    public function addAction(Request $request)
    {
        $task = new Tasks();

        $form = $this->createFormBuilder($task)
            ->add('byUsername', 'choice', array(
                'choices'  => array('0' => 'tags','1' => 'name'),
                'label'=>'Action by: '))
            ->add('type', 'choice', array(
                'choices'  => array('0' => 'following','1' => 'liking'),))
            ->add('count', 'text')
            ->add('tags', 'textarea')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            //проверка на иньекцию чуждого акк ид
            $acc_id = $request->request->get('account_id');
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id'=>$acc_id));
            if (!isset($account))
                throw new NotFoundHttpException("Page not found");

            $running_task = $em->getRepository('TaskBundle:Tasks')->findOneBy(array(
                'account_id'=>$acc_id,
                'status' => array(Tasks::RUNNING, Tasks::CREATED),
                'type'=> $task->getType()));

            if(isset($running_task))
                if($task->getType()==TaskType::FOLLOWING)
                    return new JsonResponse(array('byUsername' => 'У вас уже есть работающая задача на фоловинг'));
                else
                    return new JsonResponse(array('byUsername' => 'У вас уже есть работающая задача на лайкинг'));

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

            return new JsonResponse('success');
        }

        if($request->getMethod()=='POST'){
            $formErrors = $this->get('form_errors')->getArray($form);
            return new JsonResponse($formErrors);
        }

        return $this->render('tasks/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }


    /**
     * @Route("/tasks/stop/{task_id}", name="stop_tasks")
     */
    public function stopAction(Request $request, $task_id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository('TaskBundle:Tasks')->findOneBy(array('id' => $task_id));
        $task->setStatus(3);
        $em->persist($task);
        $em->flush();
        return $this->redirectToRoute('accounts');
    }

        /**
     * @Route("/tasks/unfollow/{task_id}", name="unfollow_tasks")
     */
    public function unfollowAction($task_id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id'=>$task_id));
        shell_exec("php /home/c/cc25673/public_html/unfollow.php '".$task_id."' "); //> /dev/null &

        //$result = null;
        return $this->redirectToRoute('accounts');
    }
}
