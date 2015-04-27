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
    public function indexAction()
    {
        $series = array(
            array("name" => "Data Serie Name",    "data" => array(1,2,4,5,6,3,8))
        );

        $ob = new Highchart();
        $ob->chart->renderTo('linechart');  // The #id of the div where to render the chart
        $ob->title->text('Chart Title');
        $ob->xAxis->title(array('text'  => "Horizontal axis title"));
        $ob->yAxis->title(array('text'  => "Vertical axis title"));
        $ob->series($series);

        return $this->render('app/index.html.twig', array(
            'chart' => $ob
        ));
    }
}
