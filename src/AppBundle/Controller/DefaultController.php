<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Accounts;
use AppBundle\Entity\RemovedAccounts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        if(!isset($_COOKIE['instastellar_ref_cookie']))
        {
            $ref = 0;
        }
        $this_url = basename($_SERVER['REQUEST_URI']);
        $matches = array();
        $em = $this->getDoctrine()->getManager();
        if(preg_match("/\?ref=[0-9]+$/", $this_url) && preg_match("/[0-9]+$/", $this_url, $matches))
        {
            if($em->getRepository('UserBundle:User')->findOneBy(array('id' => $matches[0])))
            { $ref = $matches[0]; }
            else
            { $ref = 0; }
        }
        if(isset($ref))
        { setcookie("instastellar_ref_cookie",$ref, strtotime('+60 days')); }

        return $this->render('landing.html.twig');
    }

    /**
     * @Route("/additional_info", name="additional_info")
     */
    public function additional_infoAction()
    {
        return $this->render('offer_and_confidentiality.html.twig');
    }

    /**
     * @Route("/info", name="info")
     */
    public function infoAction()
    {
        return $this->render('info.html.twig');
    }

    /**
     * @Route("/accounts", name="accounts")
     */
    public function accountsAction()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $accounts = $em->getRepository('AppBundle:Accounts')->findBy(array(
            'user' => $user->getId())
        );

        return $this->render(
            'accounts/index.html.twig',
            [
                'accounts' => $accounts,
                'user' => $user
            ]
        );
    }
    /**
     * @Route("/accounts/edit/{id}", name="accounts_edit")
     */
    public function accounts_editAction(Request $request, $id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('user' => $user->getId(),'id' => $id));
        if (!isset($account))
            throw new NotFoundHttpException("Page not found");

        $form = $this->createFormBuilder($account)
            ->setAction($this->generateUrl('accounts_edit',array('id' => $id )))
            ->add('instLogin', 'text', array('label' => 'Логин'))
            //->add('instPass', 'password', array(
              //  'label' => 'Пароль', 'required' => false))
            ->add('country', 'entity', array(
                'class' => 'AppBundle:Countries',
                'property' => 'country_name',
                'label' => 'Страна'))
            ->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {

            //если изменилась страна привязки, то меняем и прокси
            $uow = $em->getUnitOfWork();
            $uow->computeChangeSets();
            $changes = $uow->getEntityChangeSet($account);

            //необходимо из за использования computeChangeSets
            $em->persist($account);
            $em->flush();

            if(array_key_exists ('country', $changes)){

                //все прокси выбранной страны
                $proxy = $em->getRepository('AppBundle:Proxy')->findBy(
                    array('country' => $account->getCountry())
                );

                //все аккаунты использующие прокси выбранной страны
                $all_proxy_by_country = $em->getRepository('AppBundle:Accounts')->findBy(array('country' => $account->getCountry()));

                $proxy_count = (count($all_proxy_by_country) + (count($proxy))) % (count($proxy));

                $account->setProxy($proxy[$proxy_count]);
            }

            $em->persist($account);
            $em->flush();
            return  $this->redirectToRoute('accounts');
        }
        return $this->render(
            'accounts/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }



    /**
     * @Route("/account/add", name="add_account")
     */
    public function addAction()
    {
        return $this->redirect('https://instagram.com/oauth/authorize/?client_id=6e336200a7f446a78b125602b90989cc&response_type=code&redirect_uri=http://instastellar.su/get_token&scope=likes+comments+relationships');
    }

    /**
     * @Route("/account/delete/{id}", name="delete_account")
     */
    public function deleteAction($id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository('AppBundle:Accounts')->findOneBy(array('id' => $id, 'user'=>$user->getId()));
        if(!isset($account))
            throw new NotFoundHttpException("Page not found");

        //if user expired we save login in db to prevent him to use this
        // instagram acc with different instastellar acc
        /*if($user->getIsPro() == 0) {*/
            $removed_account = new RemovedAccounts();
            $removed_account->setIdDeleted($account->getId());
            $removed_account->setInstLogin($account->getInstLogin());
            $removed_account->setAccountId($account->getAccountId());
            $removed_account->setUser($user);
            $em->persist($removed_account);
        /*}*/

        $em->remove($account);
        $em->flush();

        return  $this->redirectToRoute('accounts');
    }

}
