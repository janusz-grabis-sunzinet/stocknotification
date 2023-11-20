<?php declare(strict_types=1);

namespace StockChangeNotificationPlugin\Service;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ScheduledCheckStockStack extends ScheduledTask {

    public static function getTaskName(): string
    {
        return 'wareemail.task';
    }

    public static function getDefaultInterval(): int
    {
        return 60*60*24; // once per day
    }

    public function run() : void
    {
        //TODO: check if there are changes in the stock and send notifications
    }

}
