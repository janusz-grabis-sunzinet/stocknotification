<?php declare(strict_types=1);

namespace StockChangeNotificationPlugin\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class WareEmailService
{

    protected EntityRepository $wareEmailRepository;

    protected EntityRepository $productRepository;

    public function __construct(EntityRepository $wareEmailRepository, EntityRepository $productRepository)
    {

        $this->wareEmailRepository = $wareEmailRepository;
        $this->productRepository = $productRepository;

    }

    /**
     * Creates new product-email entry
     * @param string $email
     * @param string $productNumber
     * @param Context $context
     * @return void
     */
    public function saveWareEmail(string $email, string $productNumber, Context $context): void
    {

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
        $product  = $this->productRepository->search($criteria, $context)->first();

        //TODO: before creating new entry - check if there is no existing one already

        $this->wareEmailRepository->create([
            [
                'email' => $email,
                'productId' => $product->get('id')
            ]], $context
        );
    }

    public function  checkStockChange() {

        $f = fopen("/tmp/dmp.txt", "w+");
        if ($f) {
            fwrite($f, "test, test");
            fflush($f);
            fclose($f);
        }


    }
}