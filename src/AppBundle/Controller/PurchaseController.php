<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Accounts;
use Proxies\__CG__\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use TaskBundle\Entity\Errors;
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
        $params=[];
        $params['notification_type'] = $request->get('notification_type');
        $params['operation_id']  = $request->get('operation_id');
        $params['amount']  = $request->get('amount');
        //$params['withdraw_amount'] = $request->get('withdraw_amount');
        $params['currency'] = $request->get('currency');
        $params['datetime'] = $request->get('datetime');
        $params['sender']= $request->get('sender');
        $params['codepro']= $request->get('codepro');
        $params['notification_secret'] = 'nzyqKS9YdRwGoNZ+OrFfQh0D';
        $params['label'] = $request->get('label');
        $sha1 = $request->get('sha1_hash');

        $str= implode($params, '&');

        $em = $this->getDoctrine()->getManager();
        $errors = new Errors();
        $task = $em->getRepository('TaskBundle:Tasks')->find(-1);
        $errors->setTaskId($task);
        $errors->setMessage( $str . '&' . $sha1);
        $em->persist($errors);
        $em->flush();
        //p2p-incomingtest-notification&456.25643&2015-06-04T17:09:47Z41001000040falsenzyqKS9YdRwGoNZ+OrFfQh0D|c925797120f62f3ac09137881d00ef1c1db79636
//|c925797120f62f3ac09137881d00ef1c1db79636
        $user = $em->getRepository('UserBundle:User')->find($params['label']);
        if(isset($user)){
            if(sha1($str) == $sha1){
                $date = new \DateTime();
                $date->add(new \DateInterval('P30D'));
                $user->setValidUntil($date);

                $this->get('fos_user.user_manager')->updateUser($user, false);
                $em->flush();
                return new JsonResponse('200 OK');
            }
        }
        return new JsonResponse('400');
    }


    /**
     * @Route("/purchase/fail")
     */
    public function purchaseFailAction(Request $request)
    {
        return new JsonResponse('WMI_RESULT=OK');
    }
}
