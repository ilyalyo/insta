<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\Support;
use AppBundle\Form\Type\SupportType;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AnalyticController extends Controller
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/admin/analytic", name="admin_analytic")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('UserBundle:User')->findAll();
        $index = 0;
        foreach ($users as $u) {
            $userDates[] = $u->getCreatedAt();
            $usersCount[] = '[' . $u->getCreatedAt()->getTimestamp() * 1000 . ',' . $index++ . ']';
        }

        return $this->render(
            'admin/analytic.html.twig',
            [
                'userDates' => $userDates,
                'usersCount' => $usersCount,
            ]
        );
    }
}
