<?php

class Extend_Warranty_CartController extends Mage_Core_Controller_Front_Action
{
    /**
     *
     */
    public function addAction()
    {
        $warrantyData = $this->getRequest()->getPost('warranty');
        try {
            $warranty = Mage::helper('warranty')->getWarrantyProduct();
            if (!$warranty) {
                Mage::getSingleton('core/session')->addError('Sorry! We can\'t add this product protection to your shopping cart right now.');
                Mage::getModel('warranty/logger')->error([], 'Oops! There was an error finding the protection plan product, please ensure the protection plan product is in your catalog and is enabled!');
                return $this->goBack();
            }

            //Check Qty
            $_relatedProduct = $warrantyData['product'];
            $_qty = 1;
            $_cart = Mage::getSingleton('checkout/cart');
            $_quote = $_cart->getQuote();
            foreach ($_quote->getAllVisibleItems() as $_item) {
                if ($_item->getSku() === $_relatedProduct) {
                    $_qty = $_item->getQty();
                }
            }
            $warrantyData['qty'] = $_qty;
            $warrantyHelper = Mage::helper('warranty');
            $price = $warrantyHelper->removeFormatPrice($warrantyData['price']);
            $warranty = Mage::getModel('catalog/product')->load($warranty->getId());
            $_cart->addProduct($warranty, $warrantyData);
            $item = $_cart->getQuote()->getItemByProduct($warranty);
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
            $_cart->getQuote()->removeAllAddresses();
            $_cart->save();
            $quote = Mage::getModel('checkout/session')->getQuote();
            $quote->collectTotals()->save();
            Mage::getSingleton('core/session')->addSuccess($this->__('You added "%s" to your shopping cart.', $warranty->getName()));
            return $this->goBack(null, $warranty);
        } catch (\Exception $e) {
            Mage::getSingleton('core/session')->addError($this->__('Sorry! We can\'t add this product protection to your shopping cart right now.'));
            Mage::getModel('warranty/logger')->error([], $e->getMessage());
            return $this->goBack();
        }
    }

    /**
     * @param null $backUrl
     * @param null $product
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $result = [];
        if ($backUrl || $backUrl = $this->_getRefererUrl()) {
            $result['backUrl'] = $backUrl;
        } elseif ($product && !$product->getIsSalable()) {
            $result['product'] = [
                'statusText' => __('Out of stock')
            ];
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
