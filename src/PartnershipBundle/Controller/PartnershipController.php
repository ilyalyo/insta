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
     * @Route("/become_partner", name="become_partner")
     */
    public function selfPromoteToPartner()
    {
        $role_text='a:1:{i:0;s:12:"ROLE_PARTNER";}';
        $user = $this->getUser();
        $id = $user->getId();
        $sql = "update fos_user set roles='$role_text' where id='$id'; commit;";
        $stmt = $this->getDoctrine()->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

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
