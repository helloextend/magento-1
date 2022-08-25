<?php

class Extend_Warranty_Model_Observer_Warranty_Addtocart
{

    protected $_request;

    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $warrantyHelper = Mage::helper('warranty');
        $this->_request = $request;
        $qty = $request->getPost('qty');
        $product = $observer->getProduct();
        $warrantyData = $request->getPost('warranty');
        if ($warrantyData && $this->_request->getPost('bundle_option') && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $warrantyData['dynamic_sku'] = $warrantyHelper->getComplexProductSku($product);
        }
        if (!empty($warrantyData)) {
            $this->addWarrantyToCart($warrantyData, $product, $qty);
        }

        $warranties = $request->getPost('warranties');
        if ($warranties) {
            $bundleQty = $request->getPost('bundle_option_qty');
            foreach ($warranties as $optionId => $warrantyData) {
                $optionQty = 1;
                if (isset($bundleQty[$optionId])) {
                    $optionQty = $bundleQty[$optionId];
                }
                $this->addWarrantyToCart($warrantyData, $product, $optionQty * $qty);
            }
        }
    }

    protected function addWarrantyToCart($warrantyData, $product, $qty)
    {
        $cart = Mage::getModel('checkout/cart');
        $cart->init();
        $warrantyHelper = Mage::helper('warranty');
        $price = $warrantyHelper->removeFormatPrice($warrantyData['price']);
        $warranty = $warrantyHelper->getWarrantyProduct();

        if (!$warranty) {
            Mage::getSingleton('core/session')->addError('Oops! There was an error adding the protection plan product.');
            Mage::getModel('warranty/logger')->error('Oops! There was an error finding the protection pan product, please ensure the Extend protection plan product is in your catalog and is enabled!');
            return;
        }

        $errors = Mage::helper('warranty/api')->validateWarranty($warrantyData);
        if (!empty($errors)) {
            Mage::getSingleton('core/session')->addError('Sorry! We can\'t add this product protection to your shopping cart right now.');
            $errorsAsString = implode(' ', $errors);
            Mage::getModel('warranty/logger')->error(
                'Invalid warranty data. ' . $errorsAsString . ' Warranty data: ' . Mage::helper('warranty/api')->getWarrantyDataAsString($warrantyData)
            );
            return;
        }

        $relatedItem = $cart->getQuote()->getItemByProduct($product);
        $warranty = Mage::getModel('catalog/product')->load($warranty->getId());
        $warrantyData['qty'] = $qty;
        $warrantyData['related_item_id'] = $relatedItem->getId();

        try {
            $cart->addProduct($warranty, $warrantyData);
            $item = $cart->getQuote()->getItemByProduct($warranty);
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
            $cart->getQuote()->removeAllAddresses();
            $cart->save();
            $quote = Mage::getModel('checkout/session')->getQuote();
            $quote->collectTotals()->save();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError('Oops! There was an error adding the protection plan product.');
        }
    }
}
