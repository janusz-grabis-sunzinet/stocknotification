<?php declare(strict_types=1);

use StockChangeNotificationPlugin\Core\Content\WareEmail;

class WareEmailCollection extends EntityCollection
{

    protected function getExpectedClass(): string
    {
        return WareEmail::class;
    }
}
