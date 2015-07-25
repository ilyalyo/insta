<?php

namespace PartnershipBundle\Controller;
/*¬от тут нужные таблицы не забыть указать:*/
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
                'percent' => $user->getPartnerPercent(),
                'id' => $user->getId(),
                'refs_count' => count($refs),
                'payments' => $payments
            ]
        );
    }

    /**
     * User manager
     *
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @Route("/become_partner", name="become_partner")
     */
    public function selfPromoteToPartner()
    {
        $user = $this->getUser();
        $userfos = $this->userManager->findUserByUsername($user->getUsername());
        $userfos->addRole("ROLE_PARTNER");
        $this->userManager->updateUser($userfos);
    }

}
