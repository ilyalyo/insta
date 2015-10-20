<?php

namespace AdminBundle\Controller;

use AdminBundle\Command\CheckCommand;
use AppBundle\Entity\Support;
use AppBundle\Form\Type\SupportType;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;

class AnalyticController extends Controller
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/analytic", name="admin_analytic")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare("
SELECT count(*) as sum,UNIX_TIMESTAMP(t.createdAt)  as date FROM fos_user t
WHERE t.createdAt > '2015-08-25 02:30'
GROUP BY DATE_FORMAT(t.createdAt,'%d-%m-%y')
ORDER BY 2 DESC");
        $statement->execute();
        $users = $statement->fetchAll();

        $statement = $connection->prepare("
SELECT count(*) as sum,UNIX_TIMESTAMP(t.createdAt)  as date FROM accounts t
WHERE t.createdAt > '2015-08-25 02:30'
GROUP BY DATE_FORMAT(t.createdAt,'%d-%m-%y')
ORDER BY 2 DESC");
        $statement->execute();
        $accounts = $statement->fetchAll();

        $statement = $connection->prepare("
SELECT count(*) as sum,UNIX_TIMESTAMP(t.createdAt)  as date FROM accounts_log t
GROUP BY DATE_FORMAT(t.createdAt,'%d-%m-%y'), t.instLogin
ORDER BY 2 DESC");
        $statement->execute();
        $failed_accounts = $statement->fetchAll();

        foreach ($users as $u) {
            $usersCount[] = '[' . $u['date'] *1000   . ',' . $u['sum'] . ']';
        }
        foreach ($accounts as $u) {
            $usersCount_a[] = '[' . $u['date'] *1000   . ',' . $u['sum'] . ']';
        }
        foreach ($failed_accounts as $u) {
            $usersCount_f[] = '[' . $u['date'] *1000   . ',' . $u['sum'] . ']';
        }

        return $this->render(
            'admin/analytic.html.twig',
            [
                'usersCount' => $usersCount,
                'usersCount_a' => $usersCount_a,
                'usersCount_f' => $usersCount_f,
            ]
        );
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/tasks-analytic", name="admin_analytic_tasks")
     */
    public function tasksAnalyticAction()
    {
        $em = $this->getDoctrine()->getManager();
        $command = new CheckCommand();
        $command->setContainer($this->container);
        $input = new ArrayInput([]);
        $output =  new BufferedOutput();
        $command->run($input, $output);
        $o = $output->fetch();
        $d = substr($o,0,1);
        $o = trim($o,$d);
        $ids = explode($d!=null ? $o : '|', $o!=null ? $o : '|');
        $ready_tasks = $em->getRepository('TaskBundle:Tasks')->findBy(['status' => [0,2]]);
        $forgotten_task = [];
        foreach ($ready_tasks as $t) {
            if(in_array($t->getId(),$ids))
                unset($ids[$t->getId()]);
            else
                $forgotten_task[] = $t->getId();
        }

        $tasks_failed = [];
        $tasks_stopped = [];
        $proxy = [];
        $tasks_all = [];
        $tasks_done = [];
        $connection = $em->getConnection();
        $statement = $connection->prepare("
SELECT count(*) as sum,UNIX_TIMESTAMP(t.createdAt)  as date FROM tasks t
WHERE status = 4 AND error_id is null or error_id=3
GROUP BY DATE_FORMAT(t.createdAt,'%d-%m-%y')
ORDER BY 2 DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u) {
            $tasks_failed[] = '[' . $u['date']*1000   . ',' . $u['sum'] . ']';
        }
        $statement = $connection->prepare("
SELECT count(*) as sum,UNIX_TIMESTAMP(t.createdAt)  as date FROM tasks t
WHERE status = 3
GROUP BY DATE_FORMAT(t.createdAt,'%d-%m-%y')
ORDER BY 2 DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u) {
            $tasks_stopped[] = '[' . $u['date']*1000   . ',' . $u['sum'] . ']';
        }
        $statement = $connection->prepare("
SELECT count(*) sum,UNIX_TIMESTAMP(sub.sdate) as date FROM
(
SELECT count(t.id) as actions, t.count, t.createdAt as sdate
FROM tasks t
LEFT JOIN
actions a
ON t.id =a.task_id
WHERE status = 1
GROUP BY  t.id
HAVING actions >= t.count
) as sub
GROUP BY  DATE_FORMAT(sub.sdate ,'%d-%m-%y')
ORDER BY UNIX_TIMESTAMP(sub.sdate )  DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u) {
            $tasks_done[] = '[' . $u['date']*1000   . ',' . $u['sum'] . ']';
        }
        $statement = $connection->prepare("
SELECT sub.proxy,SUM(sub.actions) sum,UNIX_TIMESTAMP(sub.sdate) as date
FROM
(
SELECT ac.proxy,count(t.id) as actions,t.createdAt as sdate
FROM tasks t
LEFT JOIN
actions a
ON t.id =a.task_id
INNER JOIN
accounts ac
ON t.account_id =ac.id
WHERE t.createdAt > NOW() - INTERVAL 7 DAY
GROUP BY  t.id
) as sub
GROUP BY  DATE_FORMAT(sub.sdate ,'%d-%m-%y'),sub.proxy
ORDER BY UNIX_TIMESTAMP( DATE_FORMAT(sub.sdate ,'%d-%m-%y') ) DESC, sub.proxy");
        $statement->execute();
        $all_proxy = $em->getRepository('AppBundle:Proxy')->findAll();
        foreach ($all_proxy as $p) {
            $proxy[$p->getId()] = [];
        }
        $results = $statement->fetchAll();
        foreach ($results as $u) {
            foreach ($all_proxy as $p) {
                if($u['proxy'] == $p->getId()){
                    $proxy[$p->getId()][] = '['.  $u['date']*1000 .','. $u['sum'] . ']';
                    break;
                }
            }
        }

        $statement = $connection->prepare("
SELECT count(*) as sum,UNIX_TIMESTAMP(t.createdAt)  as date FROM tasks t
GROUP BY DATE_FORMAT(t.createdAt,'%d-%m-%y')
ORDER BY 2 DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u) {
            $tasks_all[] = '[' . $u['date']*1000   . ',' . $u['sum'] . ']';
        }
$tasks_0=[];
$tasks_10=[];
$tasks_20=[];
$tasks_30=[];
$tasks_1=[];
$tasks_11=[];
$tasks_31=[];
$tasks_0=[];
$tasks_3=[];
       $statement = $connection->prepare("
SELECT COUNT(*) as sum, UNIX_TIMESTAMP(t.createdAt) as date
    FROM tasks t
    WHERE t.type = 0
    GROUP BY  DATE_FORMAT( t.createdAt,'%d-%m-%y')
    ORDER BY  t.createdAt DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u)
            $tasks_0[] = '[' . ($u['date']) * 1000   . ',' . $u['sum'] . ']';
        $statement = $connection->prepare("
SELECT COUNT(*) as sum, UNIX_TIMESTAMP(t.createdAt) as date
    FROM tasks t
    WHERE t.type = 10
    GROUP BY  DATE_FORMAT( t.createdAt,'%d-%m-%y')
    ORDER BY  t.createdAt DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u)
            $tasks_10[] = '[' . ($u['date']) * 1000   . ',' . $u['sum'] . ']';
        $statement = $connection->prepare("
SELECT COUNT(*) as sum, UNIX_TIMESTAMP(t.createdAt) as date
    FROM tasks t
    WHERE t.type = 20
    GROUP BY  DATE_FORMAT( t.createdAt,'%d-%m-%y')
    ORDER BY  t.createdAt DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u)
            $tasks_20[] = '[' . ($u['date']) * 1000   . ',' . $u['sum'] . ']';
        $statement = $connection->prepare("
SELECT COUNT(*) as sum, UNIX_TIMESTAMP(t.createdAt) as date
    FROM tasks t
    WHERE t.type = 30
    GROUP BY  DATE_FORMAT( t.createdAt,'%d-%m-%y')
    ORDER BY  t.createdAt DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u)
            $tasks_30[] = '[' . ($u['date']) * 1000   . ',' . $u['sum'] . ']';
        $statement = $connection->prepare("
SELECT COUNT(*) as sum, UNIX_TIMESTAMP(t.createdAt) as date
    FROM tasks t
    WHERE t.type = 1
    GROUP BY  DATE_FORMAT( t.createdAt,'%d-%m-%y')
    ORDER BY  t.createdAt DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u)
            $tasks_1[] = '[' . ($u['date']) * 1000   . ',' . $u['sum'] . ']';
        $statement = $connection->prepare("
SELECT COUNT(*) as sum, UNIX_TIMESTAMP(t.createdAt) as date
    FROM tasks t
    WHERE t.type = 11
    GROUP BY  DATE_FORMAT( t.createdAt,'%d-%m-%y')
    ORDER BY  t.createdAt DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u)
            $tasks_11[] = '[' . ($u['date']) * 1000   . ',' . $u['sum'] . ']';
        $statement = $connection->prepare("
SELECT COUNT(*) as sum, UNIX_TIMESTAMP(t.createdAt) as date
    FROM tasks t
    WHERE t.type = 31
    GROUP BY  DATE_FORMAT( t.createdAt,'%d-%m-%y')
    ORDER BY  t.createdAt DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u)
            $tasks_31[] = '[' . ($u['date']) * 1000   . ',' . $u['sum'] . ']';
        $statement = $connection->prepare("
SELECT COUNT(*) as sum, UNIX_TIMESTAMP(t.createdAt) as date
    FROM tasks t
    WHERE t.type = 3
    GROUP BY  DATE_FORMAT( t.createdAt,'%d-%m-%y')
    ORDER BY  t.createdAt DESC");
        $statement->execute();
        $results = $statement->fetchAll();
        foreach ($results as $u)
            $tasks_3[] = '[' . ($u['date']) * 1000   . ',' . $u['sum'] . ']';

        $tasks = $em->getRepository('TaskBundle:Tasks')->findBy(['status' => 2]);
        $acc_pro = $em->createQuery(
            'SELECT COUNT(u)
    FROM UserBundle:User u
    WHERE u.isPro = 1 AND u.validUntil > CURRENT_TIMESTAMP()'
        );
        $acc_free  = $em->createQuery(
            'SELECT COUNT(u)
    FROM UserBundle:User u
    WHERE u.isPro = 0 AND u.validUntil > CURRENT_TIMESTAMP()'
        );

        return $this->render(
            'admin/task_analytic.html.twig',
            [
                'tasks_failed' => $tasks_failed,
                'tasks_stopped' => $tasks_stopped,
                'tasks_done' => $tasks_done,
                'tasks_all' => $tasks_all,
                'proxy' => $proxy,
                'tasks_0' => $tasks_0,
                'tasks_10' => $tasks_10,
                'tasks_20' => $tasks_20,
                'tasks_30' => $tasks_30,
                'tasks_1' => $tasks_1,
                'tasks_11' => $tasks_11,
                'tasks_31' => $tasks_31,
                'tasks_3' => $tasks_3,
                'tasks' => count($tasks),
                'acc_pro' =>  $acc_pro->getSingleScalarResult(),
                'acc_free' => $acc_free->getSingleScalarResult(),
                'forgotten_task' => $forgotten_task,
                'ids' => $ids,
            ]
        );
    }
}
