<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Accounts;
use Proxies\__CG__\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Zend\Json\Json;

class PurchaseController extends Controller
{
    /**
     * @Route("/purchase", name="purchase")
     */
    public function purchaseAction()
    {
        $user = $this->getUser();
        return $this->render('app/purchase.html.twig',
            array('user' => $user));
    }

    /**
     * @Route("/purchase/success", name="purchase_success")
     */
    public function purchaseSuccessAction(Request $request)
    {
        $login = $request->get('WMI_MERCHANT_ID');
        $user_id = $request->get('WMI_CUSTOMER_ID');
        $login = $request->get('WMI_PAYMENT_AMOUNT');
        $login = $request->get('WMI_COMMISSION_AMOUNT');
        $login = $request->get('WMI_CURRENCY_ID');
        $login = $request->get('WMI_PAYMENT_NO');
        $login = $request->get('WMI_ORDER_STATE');
        $login = $request->get('WMI_SIGNATURE');


        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('UserBundle:User')->find($user_id);
        $date = new \DateTime();
        $date->add(new \DateInterval('P30D'));
        $user->setValidUntil($date);

        return new JsonResponse('WMI_RESULT=OK');
    }


    /**
     * @Route("/purchase/fail")
     */
    public function purchaseFailAction(Request $request)
    {
        $login = $request->get('WMI_MERCHANT_ID');
        $login = $request->get('WMI_CUSTOMER_ID');
        $login = $request->get('WMI_PAYMENT_AMOUNT');
        $login = $request->get('WMI_COMMISSION_AMOUNT');
        $login = $request->get('WMI_CURRENCY_ID');
        $login = $request->get('WMI_PAYMENT_NO');
        $login = $request->get('WMI_ORDER_STATE');
        $login = $request->get('WMI_SIGNATURE');

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $accounts = $em->getRepository('AppBundle:Accounts')->findBy(array(
                'user' => $user->getId())
        );

        return new JsonResponse('WMI_RESULT=OK');
    }
}
