<?php


class Extend_Warranty_Model_Api_Databuilder_Order
{

    /**
     * Platform code
     */
    const PLATFORM_CODE = 'magento';

    private static $encodeIdKey = 'HFPoqklsdh1';

    protected $_leads = [];

    protected $_warranties = [];


    /**
     * Alias for build method
     * @param $order
     * @param $warranties
     */
    public function prepareInfo($order)
    {
        return $this->build($order);
    }

    /**
     * @return Extend_Warranty_Helper_Data|Mage_Core_Helper_Abstract
     */
    public function getHelper()
    {
        return Mage::helper('warranty');
    }

    /**
     * @return Extend_Warranty_Helper_Connector
     */
    public function getConnector()
    {
        return Mage::helper('warranty/connector');
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param $warranties
     * @return array
     */
    public function build($order)
    {
        $orderStore = $order->getStore();

        $this->getConnector()->setStore($orderStore);

        $currencyCode = $order->getBaseCurrencyCode();
        $transactionTotal = $this->getHelper()->formatPrice($order->getBaseGrandTotal());
        $lineItems = [];
        $warrantyItems = [];
        $productItems = [];

        $quantities = [];

        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == Extend_Warranty_Model_Product_Type::TYPE_CODE) {
                $relatedProductSku = $item->getProductOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT);
                $warrantyItems[$relatedProductSku][] = $item;
            } else {
                $quantities[$item->getSku()] = $item->getQtyInvoiced();
                $productItems[$item->getSku()] = $item;
            }
        }

        foreach ($warrantyItems as $productSku => $warranties) {
            foreach ($warranties as $warranty) {
                $quantities[$productSku] -= $warranty->getQtyInvoiced();
                if (isset($productItems[$productSku])) {
                    $lineItems[] = $this->prepareLineItem($productItems[$productSku], $warranty->getQtyInvoiced(), $warranty);
                } else {
                    $lineItems[] = $this->prepareLineItem($warranty, $warranty->getQtyInvoiced(), $warranty);
                }
            }
        }

        foreach ($productItems as $productSku => $productItem) {
            if ($quantities[$productSku] > 0) {
                $lineItems[] = $this->prepareLineItem($productItem, $quantities[$productSku]);
            }
        }

        $saleOrigin = [
            'platform' => self::PLATFORM_CODE,
        ];

        $payload = [
            'isTest' => !$this->getConnector()->isLiveMode(),
            'currency' => $currencyCode,
            'createdAt' => strtotime($order->getCreatedAt()),
            'customer' => $this->getCustomerData($order),
            'lineItems' => $lineItems,
            'total' => $transactionTotal,
            'storeId' => $this->getConnector()->getApiStoreId(),
            'storeName' => $this->getConnector()->getApiStoreName(),
            'transactionId' => $order->getIncrementId(),
            'saleOrigin' => $saleOrigin,
        ];

        return $payload;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function getCustomerData($order)
    {
        $billingAddress = $order->getBillingAddress();
        $billingCountryId = $billingAddress->getCountryId();
        $billingStreet = $this->formatStreet($billingAddress->getStreet());

        $customer = [
            'name' => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
            'email' => $order->getCustomerEmail(),
            'phone' => $billingAddress->getTelephone(),
            'billingAddress' => [
                'address1' => isset($billingStreet['address1']) && $billingStreet['address1'] ? $billingStreet['address1'] : '',
                'address2' => isset($billingStreet['address2']) && $billingStreet['address2'] ? $billingStreet['address2'] : '',
                'city' => $billingAddress->getCity(),
                'countryCode' => Mage::getModel('directory/country')->load($billingCountryId)->getIso3Code(),
                'postalCode' => $billingAddress->getPostcode(),
                'province' => $billingAddress->getRegionCode() ? $billingAddress->getRegionCode() : ''
            ]
        ];

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $shippingCountryId = $shippingAddress->getCountryId();
            $shippingStreet = $this->formatStreet($shippingAddress->getStreet());

            $customer['shippingAddress'] = [
                'address1' => isset($shippingStreet['address1']) && $shippingStreet['address1'] ? $shippingStreet['address1'] : '',
                'address2' => isset($shippingStreet['address2']) && $shippingStreet['address2'] ? $shippingStreet['address2'] : '',
                'city' => $shippingAddress->getCity(),
                'countryCode' => Mage::getModel('directory/country')->load($shippingCountryId)->getIso3Code(),
                'postalCode' => $shippingAddress->getPostcode(),
            ];
        }

        return $customer;
    }

    /**
     * Format street
     *
     * @param array $street
     * @return array
     */
    protected function formatStreet(array $street = [])
    {
        $address = [];

        $address['address1'] = array_shift($street);
        if (!empty($street)) {
            $address['address2'] = implode(",", $street);
        }

        return $address;
    }

    /**
     *
     * This method prepares the lineItem payload by orderItem and qty.
     * order item is what goes to ['product'] field, if product has warranty connected
     * it should be provided in $warrantyItem argument so it goes to ["plan"] field.
     *
     * The qty argument this value depends on if this product has warranties or not
     * When warranty is set qty should be warranty->qty if warranty is not set so
     * qty = OrderItem->qty - SUM(warrantyItem->qty)
     *
     * Some products could have warranties only for a part of cart not for all qty
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param $qty
     * @param null $warrantyItem
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function prepareLineItem($orderItem, $qty, $warrantyItem = null)
    {
        /**
         * lineItemTransactionId field is using to connect lineItem from response with OrderItem
         **/
        $lineItem = [
            'status' => $this->getStatus($orderItem),
            'lineItemTransactionId' => $this->encodeId($orderItem->getId()),
            'quantity' => $qty,
            'storeId' => $this->getConnector()->getApiStoreId(),
            'orderId' => $orderItem->getOrder()->getIncrementId(),
            'warrantable' => false
        ];

        /*
         *  @TODO: add logic to check if product has warranties on extend side and set         $lineItem['warrantable'] = true;
         */
        $lineItem['warrantable'] = true;

        if ($warrantyItem !== null) {
            /**
             * if warrantyItem is set lineItemTransactionId should point to warranty item instead of product item.
             * So we will know that lineItemId and ContractId is goes to WarrantyItem.
             */
            $lineItem['lineItemTransactionId'] = $this->encodeId($warrantyItem->getId());

            $warrantyId = $warrantyItem->getProductOptionByCode(Extend_Warranty_Model_Product_Type::WARRANTY_ID);
            $term = $warrantyItem->getProductOptionByCode(Extend_Warranty_Model_Product_Type::TERM);
            $leadToken = $warrantyItem->getProductOptionByCode(Extend_Warranty_Model_Product_Type::LEAD_TOKEN);
            if ($leadToken) {
                $lineItem['leadToken'] = $leadToken;
            }
            $price = $warrantyItem->getBasePrice();

            if ($warrantyId && $term) {
                $lineItem['warrantable'] = true;
                $lineItem['plan'] = [
                    "id" => $warrantyId,
                    "purchasePrice" => $this->getHelper()->formatPrice($price),
                    "termsVersion" => $term,
                    "version" => ""
                ];

            }
        }

        $lineItem['product'] = $this->prepareProduct($orderItem);


        return $lineItem;
    }

    /**
     * @param $id
     * @return string
     */
    public function encodeId($id)
    {
        return md5(self::$encodeIdKey . $id);
    }

    /**
     * @param $orderItem
     * @return array
     */
    protected function prepareProduct($orderItem)
    {
        if ($orderItem->getProductType() == Extend_Warranty_Model_Product_Type::TYPE_CODE) {
            $productSku = $orderItem->getProductOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT);
            $associatedOrderItem = $this->getAssociatedOrderItem($orderItem);
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
        } else {
            $productSku = $orderItem->getProduct()->getSku();
            $product = $orderItem->getProduct();
        }
        $listPrice = $product ? $product->getPrice() : 0;
        try {
            $purchasePrice = $product ? $product->getFinalPrice() : 0;
        } catch (Exception $e) {
            $purchasePrice = $listPrice;
        }
        $productName = $product ? $product->getName() : '';

        return [
            'id' => $productSku,
            'listPrice' => $listPrice,
            'name' => $productName,
            'purchasePrice' => $purchasePrice
        ];
    }

    /**
     *
     * Status of the line item.
     * Enum: "canceled" "cancel_failed" "contract_failed" "contract_pending" "fulfilled" "lead_failed" "non_warrantable" "pending" "unfulfilled"
     *
     * @param $orderItem
     * @return string
     */
    protected function getStatus($orderItem)
    {
        /*
         * @todo make this status calculated by orderitem status and logic from config
         */

        $status = '';
        if (!$this->getConnector()->getOrdersApiCreateMode()) {
            $status = 'fulfilled';
        } else {
            $status = 'unfulfilled';
        }

        return $status;
    }

    protected function getAssociatedOrderItem($orderItem)
    {
        if ($orderItem->getProductType() != Extend_Warranty_Model_Product_Type::TYPE_CODE) {
            return false;
        }

        $associatedSku = $orderItem->getProductOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT);

        $orderItems = $orderItem->getOrder()->getAllItems();
        foreach ($orderItems as $item) {
            if ($item->getSku() == $associatedSku) {
                return $item;
            }
        }
    }

    /**
     * @depracted
     * @param Mage_Sales_Model_Order_Item $checkOrderItem
     */
    protected function getWarranty($checkOrderItem)
    {
        $order = $checkOrderItem->getOrder();

        $orderItems = $order->getAllItems();
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getId() == $checkOrderItem->getId()) {
                continue;
            }

            if (
                $orderItem->getProductType() === Extend_Warranty_Model_Product_Type::TYPE_CODE
                && $orderItem->getOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT)->getValue() === $checkOrderItem->getSku()
            ) {
                return $orderItem;
            }
        }
        return false;
    }

    protected function getWarrantyProduct($warrantyItem)
    {
        $order = $warrantyItem->getOrder();

        $orderItems = $order->getAllItems();
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getId() == $warrantyItem->getId()) {
                continue;
            }

            if (
                $orderItem->getProductType() != Extend_Warranty_Model_Product_Type::TYPE_CODE
                && $warrantyItem->getOptionByCode(Extend_Warranty_Model_Product_Type::ASSOCIATED_PRODUCT)->getValue() === $orderItem->getSku()
            ) {
                return $orderItem;
            }
        }
        return false;
    }
}