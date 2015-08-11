<?php

namespace PartnershipBundle\Controller;
/*��� ��� ������ ������� �� ������ �������:*/
use AppBundle\Entity\Support;
use AppBundle\Form\Type\SupportType;
use UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Util\UserManipulator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class PartnershipController extends Controller
{
    /**
     * @Security("has_role('ROLE_PARTNER')")
     * @Route("/partnership", name="partnership")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $refs = $em->getRepository('UserBundle:User')->findBy(array('refDaddy' => $user->getId()));
        $payments = $em->getRepository('PartnershipBundle:PartnerPayments')->findBy(array('user'=>$user->getID()));
        return $this->render(
            'partnership/index.html.twig',
            [
                'user' => $user,
                'percent' => $user->getPartnerPercent(),
                'id' => $user->getId(),
                'refs_count' => count($refs),
                'payments' => $payments
            ]
        );
    }

    /**
     * @Route("/become_partner", name="become_partner")
     */
    public function selfPromoteToPartner()
    {
        $user = $this->getUser();
        $userManager = $this->get('fos_user.user_manager');
        $user->addRole('ROLE_PARTNER');
        $userManager->updateUser($user);

        $user = $this->getUser();
        /*Resetting token to reresh the privileges*/
        $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
            $user,
            null,
            'main',
            $user->getRoles()
        );
        $this->container->get('security.context')->setToken($token);
        $em = $this->getDoctrine()->getManager();
        $refs = $em->getRepository('UserBundle:User')->findBy(array('refDaddy' => $user->getId()));
        $payments = $em->getRepository('PartnershipBundle:PartnerPayments')->findBy(array('user'=>$user->getId()));
        return $this->render(
            'partnership/index.html.twig',
            [
                'percent' => $user->getPartnerPercent(),
                'id' => $user->getId(),
                'refs_count' => count($refs),
                'payments' => $payments
            ]
        );
    }

}
