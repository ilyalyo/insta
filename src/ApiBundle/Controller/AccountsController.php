<?php

namespace ApiBundle\Controller;

use ApiBundle\Serializer\Normalizer\AccountNormalizer;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class AccountsController extends FOSRestController
{
    /**
     * Create a Page from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Login",
     *   requirements={
     *      {
     *          "name"="access_token",
     *          "dataType"="string",
     *      },
     * },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface|View
     */
    public function getAccountsAction(Request $request)
    {
        $access_token = $request->get('access_token');

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('UserBundle:User')->findOneBy(['access_token' => $access_token ]);

        if(!isset($user)){
            $view = $this->view(["error" => "Token doesn't exist"], 404);
            return $this->handleView($view);
        }

        $data = $em->getRepository('AppBundle:Accounts')->findBy(['user' => $user ]);
        $encoders = array( new JsonEncoder());
        $normalizers = array(new AccountNormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($data, 'json');
        $view = $this->view(["result" => $jsonContent], 200);
        return $this->handleView($view);
    }
}
