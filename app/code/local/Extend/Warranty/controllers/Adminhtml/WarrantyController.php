<?php

class Extend_Warranty_Adminhtml_WarrantyController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return void
     */
    public function addAction()
    {
        try {
            $warranty = Mage::helper('warranty')->getWarrantyProduct();
            $warrantyData = new Varien_Object($this->getRequest()->getParam('warranty'));
            $quote = $this->_getSession()->getQuote();
            if (!$warranty) {
                $data = ["status" => "fail"];
            } else {
                $warrantyHelper = Mage::helper('warranty');
                $price = $warrantyHelper->removeFormatPrice($warrantyData->getPrice());
                $warranty = Mage::getModel('catalog/product')->load($warranty->getId());
                $quote->addProduct($warranty, $warrantyData);
                $item = $quote->getItemByProduct($warranty);
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
                $quote->collectTotals()->save();
                $data = ["status" => "success"];
            }
        } catch (\Exception $e) {
            $data = ["status" => "fail"];
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
    }

    public function leadAction()
    {
        try {
            $warranty = Mage::helper('warranty')->getWarrantyProduct();
            $warrantyData = new Varien_Object($this->getRequest()->getParam('warranty'));
            $orderId = $this->getRequest()->getParam('order');


            if (!$warranty) {
                $data = ["status" => "fail"];
            } else {
                $warrantyHelper = Mage::helper('warranty');

                $warranty = Mage::getModel('catalog/product')->load($warranty->getId());
                $orderInit = Mage::getModel('sales/order')->load($orderId);

                $price = $warrantyHelper->removeFormatPrice($warrantyData->getPrice());
                $customer = $this->getCustomer($orderInit->getCustomerEmail(), $orderInit->getStore());
                $store = $orderInit->getStore();
                $this->getQuoteSession()->setStoreId($store->getId());

                if (!$customer) {
                    $customer = Mage::getModel('customer/customer');
                    $customer->setFirstname($orderInit->getCustomerFirstname())
                        ->setLastname($orderInit->getCustomerLastname())
                        ->setEmail($orderInit->getCustomerEmail());
                    $customer->save();
                } else {
                    $this->getQuoteSession()->setCustomerId($customer->getId());
                }

                /** @var Mage_Sales_Model_Quote $quote */
                $quote = $this->getQuoteSession()->getQuote();

                $billingAddress = [
                    'firstname' => $orderInit->getCustomerFirstname(),
                    'lastname' => $orderInit->getCustomerLastname(),
                    'street' => $orderInit->getBillingAddress()->getStreet(),
                    'city' => $orderInit->getBillingAddress()->getCity(),
                    'country_id' => $orderInit->getBillingAddress()->getCountryId(),
                    'region_id' => $orderInit->getBillingAddress()->getRegionId(),
                    'postcode' => $orderInit->getBillingAddress()->getPostcode(),
                    'telephone' => $orderInit->getBillingAddress()->getTelephone()
                ];
                $quote->setStore($orderInit->getStore());
                $quote->assignCustomer($customer);
                $quote->getBillingAddress()->addData($billingAddress);
                $quote->addProductAdvanced(
                    $warranty,
                    $warrantyData,
                    Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_FULL
                );

                $item = $quote->getItemByProduct($warranty);
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
                $quote->getPayment()->importData(['method' => $orderInit->getPayment()->getMethod()]);
                $quote->collectTotals();
                $quote->setIsActive(0);
                $quote->save();

                $this->_getOrderCreateModel()
                    ->setQuote($quote)
                    ->saveQuote();
                $this->getQuoteSession()->setQuoteId($quote->getId());
                $data = ["status" => "success", "redirect" => $this->getUrl('adminhtml/sales_order_create/index/')];
            }
        } catch (Exception $e) {
            $data = ["status" => "fail"];
        }

//        $data = ["status"=>"success", "redirect" => $this->_url->getUrl('sales/order/view/', ['order_id' => $order->getId()]) ];
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
    }

    /**
     * Check is allowed access to action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @return Mage_Adminhtml_Model_Session|Mage_Core_Model_Abstract|null
     */
//    protected function _getSession()
//    {
//        return Mage::getSingleton('adminhtml/session_quote');
//    }

    /**
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     */
    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('adminhtml/sales_order_create');
    }

    /**
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    protected function getQuoteSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    /**
     * @return false|mixed
     */
    protected function getCustomer($customerEmail, $store)
    {
        return Mage::getModel('customer/customer')
            ->setWebsiteId($store->getWebsite())
            ->loadByEmail($customerEmail);
    }
}
