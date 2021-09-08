<?php

class Extend_Warranty_Model_Product_Type extends Mage_Catalog_Model_Product_Type_Simple //Mage_Catalog_Model_Product_Type_Abstract
{
    const TYPE_CODE = 'warranty';

    const WARRANTY_ID = 'warranty_id';
    const ASSOCIATED_PRODUCT = 'associated_product';
    const TERM = 'warranty_term';
    const BUY_REQUEST = 'info_buyRequest';

    const TYPE_AFFILIATE          = 'affilated';
    const XML_PATH_AUTHENTICATION = 'catalog/affilated/authentication';

    public function deleteTypeSpecificData(Product $product)
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

    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $price = Mage::helper('warranty')->removeFormatPrice($buyRequest->getPrice());

        $buyRequest->setData('original_custom_price', $price);
        $product->setFinalPrice($price);

        $product->addCustomOption(self::WARRANTY_ID, $buyRequest->getData('planId'));
        $product->addCustomOption(self::ASSOCIATED_PRODUCT, $buyRequest->getProduct());
        $product->addCustomOption(self::TERM, $buyRequest->getTerm());
        $product->addCustomOption(self::BUY_REQUEST, json_encode($buyRequest->getData()));

        if ($this->_isStrictProcessMode($processMode)) {
            $product->setCartQty($buyRequest->getQty());
        }
        $product->setQty($buyRequest->getQty());

        return $product;
    }

    public function getOrderOptions($product)
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
        return $options;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
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

                if ($property == self::TERM) {
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
}
