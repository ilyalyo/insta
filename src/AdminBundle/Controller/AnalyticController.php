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
        $accounts = $em->getRepository('AppBundle:Accounts')->findAll();
        $failed_accounts = $em->getRepository('AppBundle:AccountsLog')->findAll();
        $index = 0;
        foreach ($users as $u) {
            $userDates[] = $u->getCreatedAt();
            $usersCount[] = '[' . $u->getCreatedAt()->getTimestamp() *1000   . ',' . $index++ . ']';
        }
        $index = 0;
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
}
