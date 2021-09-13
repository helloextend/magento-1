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
        foreach ($warranties as $key => $warranty) {
            $productSku = $warranty->getProductOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT);
            $warrantyId = $warranty->getProductOptionByCode(Extend_Warranty_Model_Product_Type::WARRANTY_ID);

            if (empty($productSku) || empty($warrantyId)) {
                continue;
            }

            foreach($order->getAllItems() as $item){
                if($item->getSku() !== $productSku) {
                    continue;
                }

                $product = $item->getProduct();
            }

            $billing = $order->getBillingAddress();
            $shipping = $order->getShippingAddress();

            $contracts[$key] = [
                'transactionId'    => $order->getIncrementId(),
                'transactionTotal' => [
                    "currencyCode" => "USD",
                    "amount"       => Mage::helper('warranty')->formatPrice($order->getGrandTotal())
                ],
                'customer'         => [
                    'phone'           => $billing->getTelephone(),
                    'email'           => $order->getCustomerEmail(),
                    'name'            => $order->getCustomerName(),
                    'billingAddress'  => [
                        "postalCode"  => $billing->getPostcode(),
                        "city"        => $billing->getCity(),
                        "countryCode" => Mage::getModel('directory/country')->load($billing->getCountryId())->getIso3Code(),
                        "region"      => $billing->getRegion()
                    ],
                    'shippingAddress' => [
                        "postalCode"  => $shipping->getPostcode(),
                        "city"        => $shipping->getCity(),
                        "countryCode" => Mage::getModel('directory/country')->load($shipping->getCountryId())->getIso3Code(),
                        "region"      => $shipping->getRegion()
                    ]
                ],
                'product'          => [
                    'referenceId'   => $product->getSku(),
                    'purchasePrice' => [
                        "currencyCode" => "USD",
                        "amount"       => Mage::helper('warranty')->formatPrice($product->getFinalPrice()),
                    ],
                    'title'         => $product->getName(),
                    'qty'           => (int)$warranty->getQtyOrdered()
                ],
                'currency'         => Mage::app()->getStore()->getCurrentCurrencyCode(),
                'transactionDate'  => $order->getCreatedAt() ? strtotime($order->getCreatedAt()) : strtotime('now'),
                'source'           => [
                    "platform" => "magento"
                ],
                'plan'             => [
                    'purchasePrice' => [
                        "currencyCode" => "USD",
                        "amount"       => Mage::helper('warranty')->formatPrice($warranty->getPrice()),
                    ],
                    'planId'        => $warrantyId
                ]
            ];

            $billingStreet = $billing->getStreet();
            $billingFormat = $this->formatStreet($billingStreet);

            $contracts[$key]['customer']['billingAddress'] = array_merge(
                $contracts[$key]['customer']['billingAddress'],
                $billingFormat
            );

            $shippingStreet = $shipping->getStreet();
            $shippingFormat = $this->formatStreet($shippingStreet);

            $contracts[$key]['customer']['shippingAddress'] = array_merge(
                $contracts[$key]['customer']['shippingAddress'],
                $shippingFormat
            );

            if (!$order->getCustomerIsGuest()) {
                $contracts[$key]['customer']['customerId'] = $order->getCustomerId();
            }

        }
        return $contracts;
    }

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
