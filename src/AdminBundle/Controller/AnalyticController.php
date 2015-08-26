<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\Support;
use AppBundle\Form\Type\SupportType;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        //var_dump(\DateTime::createFromFormat('Y-m-d','2015-08-25'));
        //var_dump(new DateTime('2012-02-01'));
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('UserBundle:User')->findAll();
        $accounts = $em->createQuery(
            'SELECT a
    FROM AppBundle:Accounts a
    WHERE a.createdAt > :date
    ORDER BY a.createdAt'
        )->setParameter('date',   \DateTime::createFromFormat('Y-m-d H:i','2015-08-25 02:30'),\Doctrine\DBAL\Types\Type::DATETIME)
        ->getResult();
        //$em->getRepository('AppBundle:Accounts')->findby();
        $failed_accounts = $em->createQuery(
            'SELECT a
    FROM AppBundle:AccountsLog a
    GROUP BY a.instLogin
    ORDER BY a.createdAt'
        )->getResult();
        //$failed_accounts = $em->getRepository('AppBundle:AccountsLog')->findAll();
        $index = 0;
        foreach ($users as $u) {
            $userDates[] = $u->getCreatedAt();
            $usersCount[] = '[' . $u->getCreatedAt()->getTimestamp() *1000   . ',' . $index++ . ']';
        }
        $index = $em->createQuery(
            'SELECT COUNT(a)
    FROM AppBundle:Accounts a
    WHERE a.createdAt <= :date
    ORDER BY a.createdAt'
        )->setParameter('date',   \DateTime::createFromFormat('Y-m-d H:i','2015-08-25 02:30'),\Doctrine\DBAL\Types\Type::DATETIME)
            ->getSingleScalarResult();;
        foreach ($accounts as $u) {
            $userDates_a[] = $u->getCreatedAt();
            $usersCount_a[] = '[' . $u->getCreatedAt()->getTimestamp() *1000   . ',' . $index++ . ']';
        }
        $index = 0;
        foreach ($failed_accounts as $u) {
            $userDates_f[] = $u->getCreatedAt();
            $usersCount_f[] = '[' . $u->getCreatedAt()->getTimestamp() *1000   . ',' . $index++ . ']';
        }

        return $this->render(
            'admin/analytic.html.twig',
            [
                'userDates' => $userDates,
                'usersCount' => $usersCount,
                'userDates_a' => $userDates_a,
                'usersCount_a' => $usersCount_a,
                'userDates_f' => $userDates_f,
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
        $all_proxy = $em->getRepository('AppBundle:Proxy')->findAll(['id' => 'proxy']);
        foreach ($all_proxy as $p) {
            $proxy[$p->getId()] = [];
        }
        $results = $statement->fetchAll();
        foreach ($results as $u) {
            foreach ($all_proxy as $p) {
                if($u['proxy'] == $p->getId()){
                    $proxy[$p->getId()][] = '['.  $u['date']*1000 .','. $u['sum'] . ']';
                    //array_push($proxy[$p->getId()],'['.  $u['date']*1000 .','. $u['sum'] . ']') ;
                    break;
                }
            }
        }

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
                'proxy' => $proxy,
                'tasks' => count($tasks),
                'acc_pro' =>  $acc_pro->getSingleScalarResult(),
                'acc_free' => $acc_free->getSingleScalarResult(),
            ]
        );
    }
}
