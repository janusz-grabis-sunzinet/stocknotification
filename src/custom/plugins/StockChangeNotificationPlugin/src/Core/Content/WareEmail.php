<?php declare(strict_types=1);

namespace StockChangeNotificationPlugin\Core\Content;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class WareEmail extends Entity
{

    use EntityIdTrait;

    protected string $email;

    protected string $productId;


}