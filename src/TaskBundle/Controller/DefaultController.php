<?php

namespace TaskBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;
use TaskBundle\Command\WriteCommand;
use TaskBundle\Entity\Tasks;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/tasks/add/{account_id}", name="add_tasks")
     */
    public function addAction(Request $request,$account_id)
    {
        $task = new Tasks();

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id'=>$account_id));

        $task->setAccountId($account);
        $task->setStatus(0);

        $form = $this->createFormBuilder($task)
            ->add('type', 'choice', array(
                'choices'  => array('0' => 'following','1' => 'liking'),))
            ->add('count', 'text')
            ->add('tags', 'textarea')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $task->onPrePersist();
            $em->persist($task);
            $em->flush();

           /* $command = new WriteCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(array('id' => $account->getId()));
            $output = new NullOutput();
            $resultCode = $command->run($input, $output);

            echo $resultCode;
            echo var_dump($output);*/
            return $this->redirectToRoute('accounts');
        }


        return $this->render('tasks/new.html.twig', array(
            'form' => $form->createView(),
            'account_id' => $account_id,
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
