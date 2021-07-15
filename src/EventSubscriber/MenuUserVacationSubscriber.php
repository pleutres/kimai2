<?php

namespace App\EventSubscriber;

use App\Event\ConfigureMainMenuEvent;
use App\Event\ConfigureAdminMenuEvent;
use App\Twig\IconExtension;
use App\Utils\MenuItemModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuUserVacationSubscriber implements EventSubscriberInterface
{

    /**
     * @var IconExtension
     */
    private $icons;

    public function __construct()
    {
        $this->icons = new IconExtension();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMainMenuEvent::CONFIGURE => ['onAdminMenuConfigure', 100],
        ];
    }

    public function onAdminMenuConfigure(ConfigureMainMenuEvent $event)
    {
        $event->getAdminMenu()->addChild(
            new MenuItemModel('vacation_user_admin', 'Users vacations', 'vacation_user_admin', [], $this->getIcon('invoice'))
        );
    }

    private function getIcon(string $icon)
    {
        return $this->icons->icon($icon, $icon);
    }
}

?>
