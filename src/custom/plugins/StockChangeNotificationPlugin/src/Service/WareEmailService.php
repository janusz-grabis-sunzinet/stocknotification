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

    protected function sendStockChangeEmail($to, $productName) {

        //TODO: use swift mailer instead
        $subject = "$productName is now available";
        $message = "Your product is now available";
        $headers = array(
            'From' => 'noreply@myshop.com',
            'X-Mailer' => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        return mail($to, $subject, $message, $headers);
    }

    public function  checkStockChange() {

        $context = Context::createDefaultContext();
        $wareEmails  = $this->wareEmailRepository->search(new Criteria(), $context);

        foreach ($wareEmails as $wareEmail) {

            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('id', $wareEmail->productId));
            $product  = $this->productRepository->search($criteria, $context)->first();

            //TODO: Load full product
            //TODO: Make sure we have the product before sending email

            $this->sendStockChangeEmail($wareEmail->email, $product->productNumber); //TODO: change to actual name of a product
        }
    }
}