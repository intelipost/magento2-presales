<?php
/*
 * @package     Intelipost_PreSales
 * @copyright   Copyright (c) 2016 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

namespace Intelipost\PreSales\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class PreSales extends \Intelipost\Quote\Model\Carrier\Intelipost
// extends \Magento\Shipping\Model\Carrier\AbstractCarrier
// implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const EVENT = 'presales_collectrates_event';

    protected $_code = 'presales';

    protected $_rateResultFactory;
    protected $_rateMethodFactory;
    protected $_rateErrorFactory;

    protected $_scopeConfig;
    protected $_quoteHelper;
    protected $_apiHelper;

    protected $_quoteFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $_logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Intelipost\Quote\Helper\Data $helper,
        \Intelipost\Quote\Helper\Api $api,
        \Intelipost\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Catalog\Model\ProductFactory $_productFactory,
        \Magento\Catalog\Model\ProductRepository $productRespository,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_rateErrorFactory  = $rateErrorFactory;

        $this->_scopeConfig = $scopeConfig;
        $this->_quoteHelper = $helper;
        $this->_apiHelper = $api;

        $this->_quoteFactory = $quoteFactory;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $_logger,
            $rateResultFactory,
            $rateMethodFactory,
            $helper,
            $api,
            $quoteFactory,
            $_productFactory,
            $productRespository,
            $data
        );
    }

    public function getAllowedMethods()
    {
        return ['presales' => $this->getConfigData('name')];
    }

    public function collectRates(RateRequest $request, $pickup = false)
    {
        if (!$this->getConfigFlag('active')) {
            // return false;

            return parent::collectRates($request, false);
        } elseif (!$request->getDestPostcode()) {
            return false;
        }

        // $this->_helper->removeQuotes($this->_code);

        // Default
        $preSalesAttribute = $this->_scopeConfig->getValue('carriers/presales/presales_attribute');
        $packageAttribute = $this->_scopeConfig->getValue('carriers/presales/package_attribute');
        $readyToGoAttribute = $this->_scopeConfig->getValue('carriers/presales/readytogo_attribute');
        $dateFormat = $this->_scopeConfig->getValue('carriers/presales/date_format');

        // Factory
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectProduct = $objectManager->get('Magento\Catalog\Model\ProductFactory');

        $result = [];

        /*
         * PreSales
         */
        $preSalesItems = null;

        $parentItem = null;

        foreach ($request->getAllItems() as $item) {
            if ($item->getProductType() != 'simple') {
                $parentItem = $item;

                continue;
            }

            $product = $objectProduct->create()->load($item->getProductId());

            $preSalesValue = $product->getData($preSalesAttribute);
            $preSalesReady = $product->getData($readyToGoAttribute);
            $packageValue  = $product->getData($packageAttribute);

            if ($preSalesValue && !$preSalesReady && !$packageValue) {
                if ($parentItem) {
                    $preSalesItems [$preSalesValue][] = $parentItem;
                }

                $preSalesItems [$preSalesValue][] = $item;
            }

            $parentItem = null;
        }

        $preSalesResult = null;

        if (is_array($preSalesItems)) {
            $min_date = $this->_quoteHelper->getShippedDate(false);

            $aditionalDeliveryDate = intval($this->_scopeConfig->getValue('carriers/intelipost/additional_delivery_date'));
            $moreDays = null;
            if ($aditionalDeliveryDate > 0) {
                $moreDays = $aditionalDeliveryDate > 1 ? "+{$aditionalDeliveryDate} days" : '+1 day';
            }

            foreach ($preSalesItems as $id => $children) {
                $objectRequest = clone $request;
                $objectRequest->setAllItems($children);

                $timestamp = \DateTime::createFromFormat('!' . $dateFormat, $id)->getTimestamp();

                if (!empty($moreDays)) {
                    $timestamp = strtotime($moreDays, $timestamp); // additional days
                }

                $final_timestamp = $timestamp > $min_date ? $timestamp : $min_date;

                $objectRequest->setAdditionalInformation(['shipped_date' => date('Y-m-d', $final_timestamp)]);

                $preSalesResult [] = parent::collectRates($objectRequest, false);

                $this->_removeQuotes = false; // keep between calls
            }
        }

        if (is_array($preSalesResult)) {
            foreach ($preSalesResult as $item) {
                if ($item instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                    return $item;
                }

                $rates = $item->getAllRates();
                if (is_array($rates)) {
                    $result = array_merge($result, $rates);
                }
            }
        }

        /*
         * Package
         */
        $packageItems = null;

        $parentItem = null;

        foreach ($request->getAllItems() as $item) {
            if ($item->getProductType() != 'simple') {
                $parentItem = $item;

                continue;
            }

            $product = $objectProduct->create()->load($item->getProductId());

            $preSalesValue = $product->getData($preSalesAttribute);
            $packageValue  = $product->getData($packageAttribute);

            if (!$preSalesValue && $packageValue) {
                if ($parentItem) {
                    $packageItems [$packageValue][] = $parentItem;
                }

                $packageItems [$packageValue][] = $item;
            }

            $parentItem = null;
        }

        $packageResult = null;

        if (is_array($packageItems)) {
            foreach ($packageItems as $id => $children) {
                $objectRequest = clone $request;
                $objectRequest->setAllItems($children);

                $timestamp = time();
                $days = intval($id);
                if ($days > 0) {
                    $timestamp = $days > 1 ? strtotime("+{$days} days") : strtotime("+1 day");
                }
                $objectRequest->setAdditionalInformation(['shipped_date' => date('Y-m-d', $timestamp)]);

                $packageResult [] = parent::collectRates($objectRequest, false);
            }
        }

        if (is_array($packageResult)) {
            foreach ($packageResult as $item) {
                if ($item instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                    return $item;
                }

                $rates = $item->getAllRates();
                if (is_array($rates)) {
                    $result = array_merge($result, $rates);
                }
            }
        }

        /*
         * Others
         */
        $otherItems = null;

        $parentItem = null;

        foreach ($request->getAllItems() as $item) {
            if ($item->getProductType() != 'simple') {
                $parentItem = $item;

                continue;
            }

            $product = $objectProduct->create()->load($item->getProductId());

            $preSalesValue = $product->getData($preSalesAttribute);
            $packageValue = $product->getData($packageAttribute);

            if (!$preSalesValue && !$packageValue) {
                if ($parentItem) {
                    $otherItems [] = $parentItem;
                }

                $otherItems [] = $item;
            }

            $parentItem = null;
        }

        $otherResult = null;

        if (is_array($otherItems)) {
            $objectRequest = clone $request;
            $objectRequest->setAllItems($otherItems);

            $otherResult [] = parent::collectRates($objectRequest, false);
        }

        if (is_array($otherResult)) {
            foreach ($otherResult as $item) {
                if ($item instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                    return $item;
                }

                $rates = $item->getAllRates();
                if (is_array($rates)) {
                    $result = array_merge($result, $rates);
                }
            }
        }

        if (!count($result)) {
            return false;
        }

        // Methods
        $rateResult = $this->_rateResultFactory->create();
        /*
            $sessionId = $this->_quoteHelper->getSessionId();

            $collection = $this->_quoteFactory->create()->getCollection();
            $collection->getSelect()->columns('SUM(final_shipping_cost) as total')
            ->where("carrier = '{$this->_code}' AND session_id = '{$sessionId}'")->group('delivery_method_id')
            ->order('delivery_estimate_business_days ASC');
            // ->order('delivery_method_id ASC')->order('final_shipping_cost ASC');
        */
        $collection = [];
        $resultQuotes = [];

        // WHERE carrier = $this->_code
        foreach ($this->_quoteHelper->getResultQuotes() as $quote) {
            if (!strcmp($quote->getCarrier(), $this->_code)) {
                $resultQuotes [] = $quote;
            }
        }

        // SUM(final_shipping_cost) as total
        $totalFinalShippingCost = [];
        foreach ($resultQuotes as $quote) {
            $deliveryMethodType = $quote->getDeliveryMethodType();
            if (isset($totalFinalShippingCost[$deliveryMethodType])) {
                $totalFinalShippingCost [$deliveryMethodType] += $quote->getFinalShippingCost();
            }
        }

        foreach ($resultQuotes as $quote) {
            $deliveryMethodType = $quote->getDeliveryMethodType();
            $deliveryEstimateBusinessDays = $quote->getDeliveryEstimateBusinessDays();

            if(isset($totalFinalShippingCost[$deliveryMethodType])) $quote->setTotal($totalFinalShippingCost[$deliveryMethodType]);

            // GROUP BY delivery_method_id
            if (empty($collection [$deliveryMethodType])) {
                $collection [$deliveryMethodType] = $quote;
            } else {
                $childQuote = $collection [$deliveryMethodType];

                // ORDER BY delivery_estimate_business_days ASC
                if ($childQuote->getDeliveryEstimateBusinessDays() > $deliveryEstimateBusinessDays) {
                    $collection [$deliveryMethodType] = $quote;
                }
            }
        }

        $stored = [];

        foreach ($collection as $child) {
            $deliveryMethodType = $child->getDeliveryMethodType();
            $finalShippingCost = $child->getFinalShippingCost();
            $total = $child->getTotal();

            usort($result, function ($a, $b) {
                return $a->getDeliveryEstimateDateExactIso() > $b->getDeliveryEstimateDateExactIso();
            });

            foreach ($result as $item) {
                $itemMethod = $item->getMethod();
                $itemType = $item->getDeliveryMethodType();

                if (in_array($itemType, $stored)) {
                    continue;
                }

                if (!strcmp($itemType, $deliveryMethodType)
                    // && $item->getPrice() == $finalShippingCost
                ) {
                    $item->setPrice($total);

                    $rateResult->append($item);

                    $stored [] = $itemType; // $itemMethod;

                    continue; // break;
                }
            }
        }

        // Event
        $eventManager = $objectManager->create('\Magento\Framework\Event\Manager');
        $eventManager->dispatch(self::EVENT, [
        'quoteResult' => $result,
        'collection'  => $collection,
        'rateResult'  => $rateResult,
        ]);

        return $rateResult;
    }
}
