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

            foreach($history as $h){
                $followedBy[]=$h->getFollowedBy();
                $followers[]=$h->getFollows();
            }

            $series = array(
                array("name" => "Подписчики",    "data" => $followedBy),
                array("name" => "Вы подписаны",    "data" => $followers)
            );

            $ob = new Highchart();
            $ob->chart->renderTo('linechart');  // The #id of the div where to render the chart
            $ob->title->text('Аналитика');
            $ob->xAxis->title(array('text'  => "Время"));
            $ob->yAxis->title(array('text'  => "Количество"));
            $ob->series($series);
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
