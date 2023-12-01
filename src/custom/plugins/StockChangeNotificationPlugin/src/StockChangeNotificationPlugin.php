<?php declare(strict_types=1);

namespace StockChangeNotificationPlugin;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class StockChangeNotificationPlugin extends Plugin
{

    public function uninstall(UninstallContext $uninstallContext): void
    {
        $connection = $this->container->get('Doctrine\DBAL\Connection');

        //check if table exist before running migrations
        $query = "SELECT count(*) as table_count FROM `information_schema`.`columns` WHERE table_name = 'ware_email'";
        $result = $connection->executeQuery($query);
        $row = $result->fetchAssociative();
        $tableCount = $row['table_count'];

        if ($tableCount > 0) {
            $uninstallContext->getMigrationCollection()->migrateDestructiveInPlace();
        }

    }

    public function install(InstallContext $installContext): void
    {
        //Apparently migrations run automatically when plugin is installed

    }


}