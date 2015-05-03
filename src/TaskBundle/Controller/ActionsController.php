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
class ActionsController extends Controller
{
    /**
     * @Route("/actions/{id}", name="actions")
     */
    public function indexAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository('TaskBundle:Tasks')->find($id);

        return $this->render(
            'actions/index.html.twig',
            [
                'task' => $task,
            ]
        );
    }
}
