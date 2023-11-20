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
        return 300; // 5 minutes
    }
}