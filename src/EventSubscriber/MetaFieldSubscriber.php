<?php

namespace App\EventSubscriber;

use App\Entity\EntityWithMetaFields;
use App\Entity\MetaTableTypeInterface;
use App\Entity\TimesheetMeta;
use App\Event\TimesheetMetaDefinitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class MetaFieldSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            TimesheetMetaDefinitionEvent::class => ['loadTimesheetMeta', 200],
        ];
    }

    public function loadTimesheetMeta(TimesheetMetaDefinitionEvent $event)
    {
        $this->prepareEntity($event->getEntity(), new TimesheetMeta());
    }


    private function prepareEntity(EntityWithMetaFields $entity, MetaTableTypeInterface $definition)
    {
        $definition
            ->setLabel('Fees')
            ->setOptions(['help' => 'Enter the fees related to this activity'])
            ->setName('fees')
            ->setType(MoneyType::class)
            ->setIsVisible(true);

        $entity->setMetaField($definition);
    }
}
?>
