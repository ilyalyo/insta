<?php

namespace ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;

class UserController extends FOSRestController
{
    /**
     * Create a Page from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Login",
     *   requirements={
     *      {
     *          "name"="username",
     *          "dataType"="string",
     *      },
     *      {
     *          "name"="password",
     *          "dataType"="string",
     *      }
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
    public function loginAction(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');

        $em = $this->getDoctrine()->getManager();
        $factory = $this->get('security.encoder_factory');

        $user = $em->getRepository('UserBundle:User')->findOneBy(['username' => $username ]);

        if(!isset($user)){
            $view = $this->view(["error" => "User doesn't exist"], 404);
            return $this->handleView($view);
        }

        $encoder = $factory->getEncoder($user);
        $bool = ($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) ? true : false;

        if(!$bool){
            $view = $this->view(["error" => "User doesn't exist"], 404);
            return $this->handleView($view);
        }

        $tokenGenerator = $this->container->get('fos_user.util.token_generator');
        $token = $tokenGenerator->generateToken();

        $user->setAccessToken($token);
        $em->persist($user);
        $em->flush();
        $view = $this->view(["access_token" => $token], 200);

        return $this->handleView($view);
    }
}
