<?php

class Extend_Warranty_Model_Api_Databuilder_Contract
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @param $warranties
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function prepareInfo($order, $warranties)
    {
        $contracts = [];
        $warrantyHelper = Mage::helper('warranty');
        foreach ($warranties as $key => $warranty) {
            $productSku = $warranty->getProductOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT);
            $dynamicSku = $warranty->getProductOptionByCode(Extend_Warranty_Model_Product_Type::DYNAMIC_SKU);

            $associatedSku = [$productSku];
            if ($dynamicSku) {
                $associatedSku[] = $dynamicSku;
            }

            $warrantyId = $warranty->getProductOptionByCode(Extend_Warranty_Model_Product_Type::WARRANTY_ID);

            if (empty($productSku) || empty($warrantyId)) {
                continue;
            }

            foreach ($order->getAllItems() as $item) {
                $itemSku = $warrantyHelper->getComplexOrderItemSku($item);
                if (in_array($itemSku, $associatedSku)) {
                    $quoteItem = $item;
                    $product = $item->getProduct();
                }
            }

            //No related warrantable items were found
            if (!$product || !$quoteItem) {
                continue;
            }

            if (!$product && $productSku) {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
            } else {
                throw new Exception("Product is not found for $productSku");
            }

            $billing = $order->getBillingAddress();
            $shipping = $order->getShippingAddress();

            $contracts[$key] = [
                'transactionId' => $order->getIncrementId(),
                'transactionTotal' => [
                    "currencyCode" => "USD",
                    "amount" => Mage::helper('warranty')->formatPrice($order->getGrandTotal())
                ],
                'customer' => [
                    'phone' => $billing->getTelephone(),
                    'email' => $order->getCustomerEmail(),
                    'name' => $order->getCustomerName(),
                    'billingAddress' => [
                        "postalCode" => $billing->getPostcode(),
                        "city" => $billing->getCity(),
                        "countryCode" => Mage::getModel('directory/country')->load($billing->getCountryId())->getIso3Code(),
                        "region" => $billing->getRegion()
                    ],
                    'shippingAddress' => $shipping ? [
                        "postalCode" => $shipping->getPostcode(),
                        "city" => $shipping->getCity(),
                        "countryCode" => Mage::getModel('directory/country')->load($shipping->getCountryId())->getIso3Code(),
                        "region" => $shipping->getRegion()
                    ] : []
                ],
                'product' => [
                    'referenceId' => $productSku,
                    'purchasePrice' => [
                        "currencyCode" => "USD",
                        "amount" => Mage::helper('warranty')->formatPrice($product->getFinalPrice()),
                    ],
                    'title' => $product->getName(),
                    'qty' => (int)$warranty->getQtyOrdered()
                ],
                'currency' => Mage::app()->getStore()->getCurrentCurrencyCode(),
                'transactionDate' => $order->getCreatedAt() ? strtotime($order->getCreatedAt()) : strtotime('now'),
                'source' => [
                    "platform" => "magento"
                ],
                'plan' => [
                    'purchasePrice' => [
                        "currencyCode" => "USD",
                        "amount" => Mage::helper('warranty')->formatPrice($warranty->getPrice()),
                    ],
                    'planId' => $warrantyId
                ]
            ];

            $billingStreet = $billing->getStreet();
            $billingFormat = $this->formatStreet($billingStreet);

            $contracts[$key]['customer']['billingAddress'] = array_merge(
                $contracts[$key]['customer']['billingAddress'],
                $billingFormat
            );

            if ($shipping) {
                $shippingStreet = $shipping->getStreet();
                $shippingFormat = $this->formatStreet($shippingStreet);

                $contracts[$key]['customer']['shippingAddress'] = array_merge(
                    $contracts[$key]['customer']['shippingAddress'],
                    $shippingFormat
                );
            }

            if (!$order->getCustomerIsGuest()) {
                $contracts[$key]['customer']['customerId'] = $order->getCustomerId();
            }

        }
        return $contracts;
    }

    /**
     * @param $street
     * @return array
     */
    private function formatStreet($street)
    {
        $address = [];

        $address['address1'] = array_shift($street);
        if (!empty($street)) {
            $address['address2'] = implode(",", $street);
        }

        return $address;
    }
}
