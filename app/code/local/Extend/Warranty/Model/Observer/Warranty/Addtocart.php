<?php

class Extend_Warranty_Model_Observer_Warranty_Addtocart
{
    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function execute(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $cart = Mage::getModel('checkout/cart');
        $cart->init();
        $qty = $request->getPost('qty');
        $warrantyData = $request->getPost('warranty');
        $warrantyHelper = Mage::helper('warranty');
        $price = $warrantyHelper->removeFormatPrice($warrantyData['price']);

        if (!empty($warrantyData)) {
            $warranty = $warrantyHelper->getWarrantyProduct();
            if (!$warranty) {
                Mage::getSingleton('core/session')->addError('Oops! There was an error adding the protection plan product.');
                Mage::getModel('warranty/logger')->error('Oops! There was an error finding the protection pan product, please ensure the Extend protection plan product is in your catalog and is enabled!');
                return;
            }

            $errors = Mage::helper('warranty/api')->validateWarranty($warrantyData);
            if(!empty($errors)){
                Mage::getSingleton('core/session')->addError('Sorry! We can\'t add this product protection to your shopping cart right now.');
                $errorsAsString = implode(' ', $errors);
                Mage::getModel('warranty/logger')->error(
                    'Invalid warranty data. ' . $errorsAsString . ' Warranty data: ' . Mage::helper('warranty/api')->getWarrantyDataAsString($warrantyData)
                    );
                return;
            }
            $warranty = Mage::getModel('catalog/product')->load($warranty->getId());
            $warrantyData['qty'] = $qty;

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
}
