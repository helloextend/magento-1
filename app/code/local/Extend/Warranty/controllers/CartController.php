<?php

class Extend_Warranty_CartController extends Mage_Core_Controller_Front_Action
{
    protected function _getSession()
    {
        return Mage::getSingleton('core/session');
    }

    public function leadAddAction()
    {
        try {

            $warrantyData = $this->getRequest()->getPost('warranty');

            $this->_validateWarranty($warrantyData);

            $warranty = Mage::helper('warranty')->getWarrantyProduct();
            $warranty = Mage::getModel('catalog/product')->load($warranty->getId());

            if ($warrantyData['qty']) {
                $warrantyData['lead_qty'] = $warrantyData['qty'];
            }

            $_cart = Mage::getSingleton('checkout/cart');
            $price = Mage::helper('warranty')->removeFormatPrice($warrantyData['price']);
            $_cart->addProduct($warranty, $warrantyData);
            $item = $_cart->getQuote()->getItemByProduct($warranty);

            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
            $_cart->getQuote()->removeAllAddresses();
            $_cart->save();

            $quote = Mage::getModel('checkout/session')->getQuote();
            $quote->collectTotals()->save();
            $this->_getSession()->addSuccess(sprintf('You added "%s" to your shopping cart.', $warranty->getName()));
            return $this->goBack(null, $warranty);
        } catch (\Exception $e) {
            $this->_getSession()->addError('Sorry! We can\'t add this product protection to your shopping cart right now.');
            Mage::getModel('warranty/logger')->error($e->getMessage());
            return $this->goBack();
        }
    }


    /**
     * @return Mage_Checkout_CartController|void
     */
    public function addAction()
    {
        $warrantyData = $this->getRequest()->getPost('warranty');

        /** @var Extend_Warranty_Helper_Data $warrantyHelper */
        $warrantyHelper = Mage::helper('warranty');

        try {
            $this->_validateWarranty($warrantyData);
            //Check Qty
            $_relatedProduct = [$warrantyData['product']];

            if (isset($warrantyData['dynamic_sku']) && $warrantyData['dynamic_sku']) {
                $_relatedProduct[] = $warrantyData['dynamic_sku'];
            }

            $_qty = 1;
            $_cart = Mage::getSingleton('checkout/cart');
            $_quote = $_cart->getQuote();
            foreach ($_quote->getAllVisibleItems() as $_item) {
                if (in_array($warrantyHelper->getComplexQuoteItemSku($_item), $_relatedProduct)) {
                    $_qty = $_item->getQty();
                }
            }
            $warrantyData['qty'] = $_qty;
            $price = $warrantyHelper->removeFormatPrice($warrantyData['price']);


            $warranty = $warrantyHelper->getWarrantyProduct();
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

            $this->_getSession()->addSuccess(sprintf('You added "%s" to your shopping cart.', $warranty->getName()));
            return $this->goBack(null, $warranty);
        } catch (\Exception $e) {
            $this->_getSession()->addError('Sorry! We can\'t add this product protection to your shopping cart right now.');
            Mage::getModel('warranty/logger')->error($e->getMessage());
            return $this->goBack();
        }
    }

    protected function _validateWarranty($warrantyData)
    {
        $warrantyApiHelper = Mage::helper('warranty/api');
        if (!$this->_validateFormKey()) {
            $this->_getSession()->addError('Sorry! We can\'t add this product protection to your shopping cart right now.');
            Mage::getModel('warranty/logger')->error('Invalid form key. Warranty data: ' . $warrantyApiHelper->getWarrantyDataAsString($warrantyData));
            return $this->goBack();
        }
        $warranty = Mage::helper('warranty')->getWarrantyProduct();

        if (!$warranty) {
            $this->_getSession()->addError('Sorry! We can\'t add this product protection to your shopping cart right now.');
            Mage::getModel('warranty/logger')->error('Oops! There was an error finding the protection plan product, please ensure the protection plan product is in your catalog and is enabled!');
            return $this->goBack();
        }

        $errors = $warrantyApiHelper->validateWarranty($warrantyData);
        if (!empty($errors)) {
            $this->_getSession()->addError(
                'Sorry! We can\'t add this product protection to your shopping cart right now.'
            );
            $errorsAsString = implode(' ', $errors);
            Mage::getModel('warranty/logger')->error(
                'Invalid warranty data. ' . $errorsAsString . ' Warranty data: ' . $warrantyApiHelper->getWarrantyDataAsString($warrantyData)
            );

            return $this->goBack();
        }
    }


    /**
     * @param null $backUrl
     * @param null $product
     * @return Mage_Checkout_CartController|void
     * @throws Mage_Exception
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_goBack();
            return;
        }

        $result = [];
        if ($backUrl || $backUrl = $this->_getRefererUrl()) {
            $result['backUrl'] = $backUrl;
        } elseif ($product && !$product->getIsSalable()) {
            $result['product'] = [
                'statusText' => 'Out of stock'
            ];
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        return;
    }

    /**
     * @throws Mage_Exception
     */
    protected function _goBack()
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {
            if (!$this->_isUrlInternal($returnUrl)) {
                throw new Mage_Exception('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $this->_getSession()->getMessages();
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (
                (strtolower($this->getRequest()->getActionName()) == 'add')
                && !$this->getRequest()->getParam('in_cart')
            ) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return;
    }
}
