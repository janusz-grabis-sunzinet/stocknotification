<?php declare(strict_types=1);

namespace StockChangeNotificationPlugin\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1700737154MinStockCount extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1700737154;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
ALTER TABLE `ware_email` 
ADD COLUMN `min_stock_count` INT NOT NULL DEFAULT 1;

SQL;
        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
        $query = "ALTER TABLE `ware_email` DROP COLUMN min_stock_count`";
        $connection->executeStatement($query);
    }

}
