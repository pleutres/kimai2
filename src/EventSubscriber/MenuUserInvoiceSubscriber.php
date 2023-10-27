<?php

namespace App\EventSubscriber;

use App\Event\ConfigureMainMenuEvent;
use App\Event\ConfigureAdminMenuEvent;
use App\Utils\MenuItemModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MenuUserInvoiceSubscriber implements EventSubscriberInterface
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
            new MenuItemModel('invoice_user_admin', 'Users invoices', 'invoice_user_admin', [], 'invoice')
        );
    }
}

?>
