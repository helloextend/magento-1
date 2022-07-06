<?php

/**
 *
 */
class Extend_Warranty_Model_SyncProcessor_Orders extends Extend_Warranty_Model_SyncProcessor
{
    const EXTEND_HISTORICAL_ORDERS_TABLE_NAME = 'warranty/historical_orders';

    /**
     * @var
     */
    protected $collection;

    /**
     * @var Extend_Warranty_Model_Api_Sync_Orders_Handler
     */
    protected $syncHandler;

    protected $historicalOrderSyncPeriod;

    protected $storeId;

    /**
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    public function getCollection()
    {

        if ($this->collection === null) {
            $batchSize = $this->getBatchSize();

            /** @var Mage_Sales_Model_Resource_Order_Collection $orderCollection */
            $orderCollection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->setPageSize($batchSize);

            $orderCollection->getSelect()->joinLeft(
                array('historical' => $orderCollection->getResource()->getTable(self::EXTEND_HISTORICAL_ORDERS_TABLE_NAME)),
                'historical.entity_id = main_table.entity_id',
                array('historical.was_sent')
            );

            $storeId = $this->getStoreId();

            $fromDate = $this->getHistoricalOrdersSyncPeriod($storeId);

            if ($fromDate) {
                $orderCollection->addFieldToFilter('created_at', array('from' => $fromDate));
            }

            if ($storeId) {
                $orderCollection->addFieldToFilter('store_id', array('eq' => $storeId));
            }

            $this->collection = $orderCollection;
        }
        return $this->collection;
    }

    public function getStartMessage()
    {
        return 'Started syncing historical orders from Magento to Extend. Please wait!';
    }

    /**
     * @return Extend_Warranty_Model_Api_Sync_Orders_Handler
     */
    public function getSyncHandler()
    {
        if ($this->syncHandler === null) {
            $this->syncHandler = Mage::getModel('warranty/api_sync_orders_handler');
        }
        return $this->syncHandler;
    }

    /**
     * @return integer
     */
    public function getBatchSize()
    {
        return Mage::helper('warranty/connector')->getHistoricalOrdersBatchSize();
    }


    protected function getHistoricalOrdersSyncPeriod($storeId = null)
    {
        if ($this->historicalOrderSyncPeriod === null) {
            $this->historicalOrderSyncPeriod = Mage::helper('warranty/connector')->getHistoricalOrdersSyncPeriod($storeId);
        }

        if ($this->historicalOrderSyncPeriod === null) {
            $date = Mage::app()
                ->getLocale()
                ->date(null, null, null, false)
                ->addYear(-2);

            $value = $date->toString(Varien_Date::DATE_INTERNAL_FORMAT);
            $scope = is_null($storeId) ? 'default' : 'stores';
            $scopeId = is_null($storeId) ? 0 : $storeId;
            Mage::helper('warranty/connector')->setHistoricalOrdersSyncPeriod($value, $scope, $scopeId);

            $this->historicalOrderSyncPeriod = Mage::helper('warranty/connector')->getHistoricalOrdersSyncPeriod($storeId);
        }
        return $this->historicalOrderSyncPeriod;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    public function resetCollection()
    {
        $this->historicalOrderSyncPeriod = null;
        $this->collection = null;
    }

    public function process()
    {
        if ($this->getStoreId()) {
            $stores = [Mage::app()->getStore($this->getStoreId())];
        } else {
            $stores = Mage::app()->getStores();
            $allStoresProcessing = true;
        }

        foreach ($stores as $store) {
            if (!Mage::helper('warranty/connector')->isExtendEnabled($store->getId())) {
                continue;
            }

            Mage::helper('warranty/connector')->setStore($store);

            $this->resetCollection();

            $this->setStoreId($store->getId());

            parent::process();

            $this->updateOrdersSyncPeriod($store->getId());
        }

        if ($allStoresProcessing) {
            $this->updateOrdersSyncPeriod();
        }
    }

    /**
     * @param $storeId
     * @return void
     */
    protected function updateOrdersSyncPeriod($storeId = null)
    {
        $date = Mage::app()->getLocale()->date(null, null, null, false);
        $value = $date->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        $scope = $storeId ? Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES : \Mage_Adminhtml_Block_System_Config_Form::SCOPE_DEFAULT;
        $scopeId = $storeId ? $storeId : 0;
        Mage::helper('warranty/connector')->setHistoricalOrdersSyncPeriod($value, $scope, $scopeId);
    }
}