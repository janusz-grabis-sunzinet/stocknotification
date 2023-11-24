<?php declare(strict_types=1);

namespace StockChangeNotificationPlugin\Service;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\Uuid\Uuid;


class WareEmailService
{

    protected EntityRepository $wareEmailRepository;

    protected EntityRepository $productRepository;

    protected EntityRepository $languageRepository;

    protected EntityRepository $productTranslationRepository;

    protected Connection $connection;

    protected TranslationService $ts;


    public function __construct(EntityRepository $wareEmailRepository,
                                EntityRepository $productRepository,
                                EntityRepository $languageRepository,
                                EntityRepository $productTranslationRepository,
                                Connection $connection)
    {

        $this->wareEmailRepository = $wareEmailRepository;
        $this->productRepository = $productRepository;
        $this->languageRepository = $languageRepository;
        $this->productTranslationRepository = $productTranslationRepository;
        $this->connection = $connection;
    }

    /**
     * Creates new product-email entry
     * @param string $email
     * @param string $productNumber
     * @param Context $context
     * @return void
     */
    public function saveWareEmail(string $email, string $productNumber, int $minStockCount, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
        $product  = $this->productRepository->search($criteria, $context)->first();

        //TODO: before creating new entry - check if there is no existing one already

        $this->wareEmailRepository->create([
            [
                'email' => $email,
                'productId' => $product->get('id'),
                'minStockCount' => $minStockCount
            ]], $context
        );
    }

    public function checkStockChange() {

        $criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->addAssociation('product.translations');
        $wareEmails  = $this->wareEmailRepository->search($criteria, Context::createDefaultContext());

        $this->notifyProductStockChanges($wareEmails);

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

    /**
     * Updates custom field that holds total number of items
     * that customers would like to buy once product is available again
     */
    public function updateNumberOfItemsWantedByCustomers() {

        //1. calculate totals for all products
        $productIdToItemCount = [];
        $query = "select sum(min_stock_count) as item_count, product_id from ware_email group by product_id";
        $result = $this->connection->executeQuery($query);
        while ($row = $result->fetchAssociative()) {
            $productIdToItemCount[Uuid::fromBytesToHex($row['product_id'])] = $row['item_count'];
        }

        //2. read custom fields of a product, if there are users that subscribed for that product add update to the list
        $context = Context::createDefaultContext();
        $customFieldsToUpdate = [];
        $products  = $this->productRepository->search(new Criteria(),  $context);
        foreach ($products as $product) {
            if (isset($productIdToItemCount[$product->id])) {
                $customFields = ['custom_product_number_of_items_waiting' => $productIdToItemCount[$product->id]];
                $customFieldsToUpdate[] = ['id' => $product->id, 'customFields' => $customFields];
            }
        }

        //3. write custom fields back to database
        if (!empty($customFieldsToUpdate)) {
            $this->productRepository->update($customFieldsToUpdate, $context);
        }
    }

    protected function sendStockChangeEmail($to, $productName) : bool {

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

    protected function notifyProductStockChanges($wareEmails) {

        $context = Context::createDefaultContext();
        $criteria = new Criteria();

        //TODO: select language based on system settings
        $criteria->addFilter(new EqualsFilter('name', 'English'));
        $language = $this->languageRepository->search($criteria, $context)->first();

        foreach ($wareEmails as $wareEmail) {

            //TODO: Load prodocut translation
            //TODO: Prepare nice email template (twig?)
            #Since we are calling this code in response to PRODUCT_WRITTEN_EVENT even and in that even value of stock_available
            #is NOT yet updated, we need to check here value of stock (not stock_available)
            #(stock_available will be updated later)
            if ($wareEmail->product->stock >= $wareEmail->minStockCount) {

                //$nameTranslated = $wareEmail->product->getTranslation('name', $language->getId());

                $this->sendStockChangeEmail($wareEmail->email, $wareEmail->product->productNumber); //TODO: change to actual name of a product
            }
        }
    }

}