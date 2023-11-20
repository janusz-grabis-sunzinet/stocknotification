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

    public function saveWareEmail(string $email, string $productNumber, Context $context): void
    {

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));

        $product  = $this->productRepository->search($criteria, $context)->first();

        $this->wareEmailRepository->create([
            [
                'email' => "abc",
                'productId' => $product->get('id')
            ]], $context
        );
    }
}