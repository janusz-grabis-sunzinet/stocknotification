<?php declare(strict_types=1);

namespace StockChangeNotificationPlugin;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class StockChangeNotificationPlugin extends Plugin
{
    public function uninstall(UninstallContext $uninstallContext): void
    {

        //TODO: run destructive migrations here
        //$uninstallContext->getMigrationCollection()->migrateInPlace();
    }

    public function install(InstallContext $installContext): void
    {
        //TODO: run migrations here
        //$installContext->getMigrationCollection()->migrateDestructiveInPlace();
    }


}