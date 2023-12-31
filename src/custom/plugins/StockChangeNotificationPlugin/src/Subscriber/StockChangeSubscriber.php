<?php

namespace StockChangeNotificationPlugin\Subscriber;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use StockChangeNotificationPlugin\Service\WareEmailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StockChangeSubscriber implements EventSubscriberInterface
{
    protected WareEmailService $wareEmailService;

    public function __construct(WareEmailService $wareEmailService) {
        $this->wareEmailService = $wareEmailService;
    }

    public static function getSubscribedEvents(): array
    {
        // Return the events to listen to as array like this:  <event to listen to> => <method to execute>
        return [
            ProductEvents::PRODUCT_WRITTEN_EVENT => 'onProductsWritten'
        ];
    }

    public function onProductsWritten(EntityWrittenEvent $event)
    {

        $productsToNotify = [];

        $writeResults = $event->getWriteResults();
        foreach ($writeResults as $writeResult) {
            if ($writeResult->getEntityName() === ProductDefinition::ENTITY_NAME
                && $writeResult->getOperation() === EntityWriteResult::OPERATION_UPDATE)
            {
                $productsToNotify[] = $writeResult->getPrimaryKey();
            }
        }

        $this->wareEmailService->notifyProductsStockChangeUsingPrimaryKeys($productsToNotify);
    }
}
