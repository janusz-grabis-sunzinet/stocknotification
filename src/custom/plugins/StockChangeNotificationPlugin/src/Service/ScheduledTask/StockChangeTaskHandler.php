<?php declare(strict_types=1);

namespace StockChangeNotificationPlugin\Service\ScheduledTask;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use StockChangeNotificationPlugin\Service\WareEmailService;

#[AsMessageHandler]
class StockChangeTaskHandler extends ScheduledTaskHandler  {

    protected WareEmailService $wareEmailService;

    public function __construct(EntityRepository $scheduledTaskRepository, WareEmailService $wareEmailService) {
        parent::__construct($scheduledTaskRepository);
        $this->wareEmailService = $wareEmailService;

    }

    public static function getHandledMessages(): iterable
    {
        return [ StockChangeTask::class ];
    }

    public function run(): void
    {
        $this->wareEmailService->checkStockChange();
    }

}
