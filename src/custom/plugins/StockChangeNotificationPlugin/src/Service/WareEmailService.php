<?php declare(strict_types=1);

namespace StockChangeNotificationPlugin\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;


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

    protected function sendStockChangeEmail($to, $productName) : boolean {

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

    /**
     * @param array<string> $productPrimaryKeys
     * @return void
     */
    public function notifyMultipleProductsStockChange(array $productPrimaryKeys) : void
    {
        $productFilters = [];
        foreach ($productPrimaryKeys as $productPrimaryKey) {
            $productFilters[] = new EqualsFilter('productId', $productPrimaryKey);
        }
        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addFilter(new OrFilter($productFilters));

        $context = Context::createDefaultContext();
        $wareEmails = $this->wareEmailRepository->search($criteria, $context);

        $this->notifyProductStockChanges($wareEmails);

    }

    public function checkStockChange() {

        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addAssociation('product.translations');
        $wareEmails  = $this->wareEmailRepository->search($criteria, Context::createDefaultContext());

        $this->notifyProductStockChanges($wareEmails);

    }

    protected function notifyProductStockChanges($wareEmails) {

        $context = Context::createDefaultContext();
        $criteria = new Criteria();

        //TODO: select language based on system settings
        $criteria->addFilter(new EqualsFilter('name', 'English'));
        $language = $this->languageRepository->search($criteria, $context)->first();

        foreach ($wareEmails as $wareEmail) {
            //TODO: Load prodocut translation
            //TODO: Prepare nice email template (twig?)

            if ($wareEmail->product->availableStock > 0) {

                //$nameTranslated = $wareEmail->product->getTranslation('name', $language->getId());

                $this->sendStockChangeEmail($wareEmail->email, $wareEmail->product->productNumber); //TODO: change to actual name of a product
            }
        }
    }

}