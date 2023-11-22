<?php
namespace StockChangeNotificationPlugin\Service\ScheduledTask;


use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class StockChangeTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'ware_email.stockchangetask';
    }

    public static function getDefaultInterval(): int
    {
        return 60*60*24; // every 24 hours
    }
}