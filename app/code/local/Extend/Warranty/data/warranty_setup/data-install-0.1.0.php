<?php
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
$product = Mage::getModel('catalog/product');

try {
    $websites = Mage::app()->getWebsites();
    $attributeSetId = Mage::getModel('catalog/product')->getDefaultAttributeSetId();
    $product
        ->setWebsiteIds(array_keys($websites)) //website ID the product is assigned to, as an array
        ->setAttributeSetId($attributeSetId) //ID of a attribute set named 'default'
        ->setTypeId(Extend_Warranty_Model_Product_Type::TYPE_CODE) //product type
        ->setCreatedAt(strtotime('now')) //product creation time
        ->setSku('WARRANTY-1') //SKU
        ->setName('Extend Protection Plan') //product name
        ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED) //product status (1 - enabled, 2 - disabled)
        ->setTaxClassId(0) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
        ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) //catalog and search visibility
        ->setPrice(0.0) //price in form 11.22
        ->setMediaGallery(array('images' => array(), 'values' => array())) //media gallery initialization
        ->addImageToMediaGallery(Mage::getModuleDir('data', 'Extend_Warranty') . DS . 'Resource/Extend_icon.png', array('image', 'thumbnail', 'small_image'), false, false) //assigning image, thumb and small image to media gallery
        ->setStockData(
            array(
                'use_config_manage_stock'     => 0,
                'is_in_stock'                 => 1,
                'qty'                         => 1000,
                'manage_stock'                => 0,
                'use_config_notify_stock_qty' => 0
            )
        );

    $product->save();
} catch (Exception $e) {
    Mage::log($e->getMessage());
}
