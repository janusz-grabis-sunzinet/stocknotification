<?php declare(strict_types=1);

namespace StockChangeNotificationPlugin\Core\Content;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class WareEmailCollection extends EntityCollection
{

    protected function getExpectedClass(): string
    {
        return WareEmail::class;
    }
}
