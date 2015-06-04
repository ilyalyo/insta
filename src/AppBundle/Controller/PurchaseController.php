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
        $withdraw_amount = $request->get('withdraw_amount');
        $params['notification_type'] = $request->get('notification_type');
        $params['operation_id']  = $request->get('operation_id');
        $params['amount']  = $request->get('amount');
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
        $errors->setMessage($withdraw_amount . '|' . $str . '&' . $sha1);
        $em->persist($errors);
        $em->flush();

        $user = $em->getRepository('UserBundle:User')->find($params['label']);
        if(isset($user)){
            if(sha1($str) == $sha1){
                $date = $user->getValidUntil();
                if($date->getTimestamp() < time())
                    $date= new \DateTime();
                switch ($withdraw_amount){
                    case 1.00:
                        $date->add(new \DateInterval('P1M'));
                        break;
                    case 1999.00:
                        $date->add(new \DateInterval('P3M'));
                        break;
                    case 3999.00:
                        $date->add(new \DateInterval('P7M'));
                        break;
                }
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
