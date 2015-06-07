<?php

namespace AppBundle\Controller;

use Ob\HighchartsBundle\Highcharts\Highchart;
use AppBundle\Entity\Accounts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AnalyticController extends Controller
{
    /**
     * @Route("/analytic", name="analytic")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $accounts = $em->getRepository('AppBundle:Accounts')->findBy(array(
                'user' => $user->getId())
        );
        $id = $request->get('id');

        if(!isset($id) && isset($accounts)  && isset($accounts[0]))
            $id = $accounts[0]->getId();

            $history = $em->getRepository('AppBundle:History')->findBy(array(
                'account_id' => $id));

            $n = 0;
            $count = count($history);
            $followedBy = array();
            $followers = array();
            foreach ($history as $h) {
                $date = gmdate("d/m/Y H:00 ", time() - (($count - $n++) * 3600));
                $dates[] = $date;
                $followedBy[] = $h->getFollowedBy();
                $followers[] =$h->getFollows();
            }

        $local = $user->getTimezone();
        return $this->render(
        'analytic/analytic.html.twig',
        [
            'accounts' => $accounts,
            'followedBy' => $followedBy,
            'followers' => $followers,
            'date' => (time() - ($count)* 3600 + (new \DateTimeZone($local))->getOffset(new \DateTime())) * 1000
        ]
    );
    }
}
