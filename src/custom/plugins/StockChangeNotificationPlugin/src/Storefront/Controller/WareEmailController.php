<?php

namespace StockChangeNotificationPlugin\Storefront\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Storefront\Controller\StorefrontController;
use StockChangeNotificationPlugin\Service\WareEmailService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

//use Sabre\Xml\Service;

#[Route(defaults: ['_routeScope' => ['storefront']])] class WareEmailController extends StorefrontController
{

    protected $em;

    protected WareEmailService $wareEmailService;

    public function __construct(WareEmailService $wareEmailService)
    {
        $this->wareEmailService = $wareEmailService;
    }

    #[Route(path: '/wareemail/save', name: 'ware.email.save', defaults: [])]
    public function emailProductSave(Request $request, Context $context)
    {
        $email = $request->request->get('wareemail-customer-email');
        $productNumber = $request->request->get('wareemail-product-number');

        $this->wareEmailService->saveWareEmail($email, $productNumber, $context);

        $route = $request->headers->get('referer');
        return $this->redirect($route);
    }

    #[Route(path: '/waremail/save/dynamic', name: 'ware.email.save.dynamic', defaults: [])]
    public function emailProductSaveDynamic(Request $request, Context $context)  {

        $content = $request->getContent();
        $payload = json_decode($content, true);

        $this->wareEmailService->saveWareEmail($payload['email'], $payload['product_number'], (int)$payload['min_stock_count'], $context);

        return new JsonResponse(['result' => 0]);
    }

}