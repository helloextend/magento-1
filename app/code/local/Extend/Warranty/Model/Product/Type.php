<?php

class Extend_Warranty_Model_Product_Type extends Mage_Catalog_Model_Product_Type_Simple
{
    const TYPE_CODE = 'warranty';
    const WARRANTY_ID = 'warranty_id';
    const ASSOCIATED_PRODUCT = 'associated_product';
    const TERM = 'warranty_term';
    const LEAD_TOKEN = 'lead_token';
    const DYNAMIC_SKU = 'bundle_sku';
    const RELATED_ITEM_ID = 'related_item_id';
    const BUY_REQUEST = 'info_buyRequest';
    const TYPE_AFFILIATE = 'affilated';
    const XML_PATH_AUTHENTICATION = 'catalog/affilated/authentication';

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function deleteTypeSpecificData($product)
    {
        return;
    }

    /**
     * Check is virtual product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isVirtual($product = null)
    {
        return true;
    }

    /**
     * Default action to get weight of product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return decimal
     */
    public function getWeight($product = null)
    {
        return 0.0;
    }

    /**
     * @param Mage_Catalog_Model_Product|null $product
     * @return array
     */
    public function getOrderOptions($product = NULL)
    {
        $options = parent::getOrderOptions($product);

        if ($warrantyId = $product->getCustomOption(self::WARRANTY_ID)) {
            $options[self::WARRANTY_ID] = $warrantyId->getValue();
        }

        if ($associatedProduct = $product->getCustomOption(self::ASSOCIATED_PRODUCT)) {
            $options[self::ASSOCIATED_PRODUCT] = $associatedProduct->getValue();
        }

        if ($term = $product->getCustomOption(self::TERM)) {
            $options[self::TERM] = $term->getValue();
        }

        if ($leadToken = $product->getCustomOption(self::LEAD_TOKEN)) {
            $options[self::LEAD_TOKEN] = $leadToken->getValue();
        }

        if ($dynamicSku = $product->getCustomOption(self::DYNAMIC_SKU)) {
            $options[self::DYNAMIC_SKU] = $dynamicSku->getValue();
        }

        if ($relatedItemId = $product->getCustomOption(self::RELATED_ITEM_ID)) {
            $options[self::RELATED_ITEM_ID] = $relatedItemId->getValue();
        }

        return $options;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getWarrantyInfo($product)
    {
        $warrantyProperties = [
            self::ASSOCIATED_PRODUCT => 'Product',
            self::TERM => 'Term'
        ];

        $options = [];
        foreach ($warrantyProperties as $property => $label) {
            if ($attributesOption = $product->getCustomOption($property)) {
                $data = $attributesOption->getValue();
                if (!$data) {
                    continue;
                }

                if ($property === self::TERM) {
                    $data = ((int)$data) / 12;
                    $data .= $data > 1 ? ' years' : ' year';
                }
                $options[] = [
                    'label' => $label,
                    'value' => $data
                ];
            }
        }

        return $options;
    }

    /**
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return Mage_Catalog_Model_Product
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $price = Mage::helper('warranty')->removeFormatPrice($buyRequest->getPrice());
        $buyRequest->setData('original_custom_price', $price);
        $product->setFinalPrice($price);
        $product->addCustomOption(self::WARRANTY_ID, $buyRequest->getData('planId'));
        $product->addCustomOption(self::ASSOCIATED_PRODUCT, $buyRequest->getProduct());
        $product->addCustomOption(self::TERM, $buyRequest->getTerm());

        if ($buyRequest->hasDynamicSku()) {
            $product->addCustomOption(self::DYNAMIC_SKU, $buyRequest->getDynamicSku());
        }

        if ($buyRequest->hasRelatedItemId()) {
            $product->addCustomOption(self::RELATED_ITEM_ID, $buyRequest->getRelatedItemId());
        }

        if ($buyRequest->getData('leadToken')) {
            $product->addCustomOption(self::LEAD_TOKEN, $buyRequest->getData('leadToken'));
        }
        $product->addCustomOption(self::BUY_REQUEST, serialize($buyRequest->getData()));

        if ($this->_isStrictProcessMode($processMode)) {
            $product->setCartQty($buyRequest->getQty());
        }
        $product->setQty($buyRequest->getQty());

        return $product;
    }
}
