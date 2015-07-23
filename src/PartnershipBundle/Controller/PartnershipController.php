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

class PartnershipController extends Controller
{
    /**
     * @Security("has_role('ROLE_PARTNER')")
     * @Route("/partnership", name="partnership")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        return $this->render(
            'partnership/index.html.twig',
            [
                'percent' => $user->getPartnerPercent()
            ]
        );
    }
}
