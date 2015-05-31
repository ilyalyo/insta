<?php
namespace UserBundle\EventListener;

use UserBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
class UserSubscriber implements EventSubscriberInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            //here, you can do whatever you want after user login successfully: send mail, set last login, set your timezone, ...

            //this is timezone you can detect or you define
            $request = $event->getRequest();
            $timezoneDetect = $request->request->get('timezone-detect');

            if ($timezoneDetect != null) {
                $user->setTimezone($timezoneDetect);
                /** @var UserManagerInterface $userManager */
                $userManager = $this->container->get('fos_user.user_manager');
                $userManager->updateUser($user);
            }

        }

    }
}