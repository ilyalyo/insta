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
use TaskBundle\Form\Type\LikeByTagsType;

class CreateController extends Controller
{
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
