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
        $chart='';
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $accounts = $em->getRepository('AppBundle:Accounts')->findBy(array(
                'user' => $user->getId())
        );
        $id = $request->get('id');

        if(isset($id)){
            $history = $em->getRepository('AppBundle:History')->findBy(array(
                'account_id' => $id));

            $n = 0;
            $count = count($history);
            $followedBy=array();
            $followers=array();
            $d=array();
            foreach($history as $h){
                $date = gmdate("d/m/Y H:00 ", time() - (($count - $n++) * 3600));
                $followedBy[]=  array($date, $h->getFollowedBy());
                $followers[]=   array($date,$h->getFollows());
                $d[]=   $date;
            }

            $series = array(
                array("name" => "Подписчики",    "data" => $followedBy),
                array("name" => "Вы подписаны",    "data" => $followers)
            );
           // pointInterval: 24 * 3600 * 1000,
         //   pointStart: Date.UTC(2006, 0, 1)

            $ob = new Highchart();
            $ob->chart->renderTo('linechart');  // The #id of the div where to render the chart
            $ob->chart->type('spline');
            $ob->chart->zoomType('x');
            $ob->title->text('Аналитика');
            $ob->xAxis->title(array('text'  => "Время"));
            $ob->yAxis->title(array('text'  => "Количество"));

            $ob->xAxis->type('datetime');
            $ob->xAxis->dateTimeLabelFormats(array('month' => '%e. %b', ' year' => '%b' ));
            $ob->series($series);
            $ob->xAxis->categories($d);
            $ob->xAxis->tickInterval(24);

        }

        return $this->render(
            'app/index.html.twig',
            [
                'accounts' => $accounts,
                'chart' => isset($ob) ? $ob : null
            ]
        );
    }
}
