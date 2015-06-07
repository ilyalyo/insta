<?php

namespace TaskBundle\Controller;

use Proxies\__CG__\TaskBundle\Entity\Actions;
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

class AjaxController extends Controller
{
    /**
     * @Route("/tasks/status", name="tasks_status")
     */
    public function addAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $query = $qb
            ->select('ac.id,COUNT(a.id) as done,t.count as shouldbedone, t.status,t.parsingStatus')
            ->from('TaskBundle\Entity\Tasks','t')
            ->leftJoin('t.actions','a', 'WITH', 'a.task_id=t.id')
            ->leftJoin('t.account_id','ac','WITH','t.account_id=ac.id')
            ->groupBy('t.id')
            ->where('ac.user=:user')
            ->andWhere('t.status in (0,2)')
            ->setParameter('user', $user->getId())
            ->getQuery();

        return new JsonResponse($query->getArrayResult());
    }
}
