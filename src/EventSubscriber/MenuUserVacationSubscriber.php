<?php

namespace App\EventSubscriber;

use App\Event\ConfigureMainMenuEvent;
use App\Event\ConfigureAdminMenuEvent;
use App\Utils\MenuItemModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MenuUserVacationSubscriber implements EventSubscriberInterface
{

    public function __construct()
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMainMenuEvent::class => ['onAdminMenuConfigure', 100],
        ];
    }

    public function onAdminMenuConfigure(ConfigureMainMenuEvent $event)
    {
        $event->getAdminMenu()->addChild(
            new MenuItemModel('vacation_user_admin', 'Users vacations', 'vacation_user_admin', [], 'invoice')
        );
    }

}

?>
