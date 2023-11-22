<?php declare(strict_types=1);

namespace StockChangeNotificationPlugin\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;


class WareEmailService
{

    protected EntityRepository $wareEmailRepository;

    protected EntityRepository $productRepository;

    protected EntityRepository $languageRepository;

    protected EntityRepository $productTranslationRepository;

    protected TranslationService $ts;


    public function __construct(EntityRepository $wareEmailRepository,
                                EntityRepository $productRepository,
                                EntityRepository $languageRepository,
                                EntityRepository $productTranslationRepository)
    {

        $this->wareEmailRepository = $wareEmailRepository;
        $this->productRepository = $productRepository;
        $this->languageRepository = $languageRepository;
        $this->productTranslationRepository = $productTranslationRepository;
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

        $criteria = new Criteria();

        //TODO: select language based on system settings
        $criteria->addFilter(new EqualsFilter('name', 'English'));
        $language = $this->languageRepository->search($criteria, $context)->first();

        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addAssociation('product.translations');
        $wareEmails  = $this->wareEmailRepository->search($criteria, $context);

        foreach ($wareEmails as $wareEmail) {
            //TODO: Load full product
            //TODO: Make sure we have the product before sending email

            /** Attempting to load translations for a product - DOES NOT WORK
            $criteria = new Criteria();
            $productId = $wareEmail->product->getId();
            $criteria->addFilter(new AndFilter([
                new EqualsFilter('productId', $wareEmail->product->getId()),
                new EqualsFilter('languageId', $language->getId())
                ]
            ));
            $productTranslation = $this->productTranslationRepository->search($criteria, $context)->first();
            */

            if ($wareEmail->product->availableStock > 0) {

                $a = $wareEmail->product->getTranslation('name', $language->getId());

                $this->sendStockChangeEmail($wareEmail->email, $wareEmail->product->productNumber); //TODO: change to actual name of a product
            }
        }
    }
}