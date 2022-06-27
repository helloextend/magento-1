<?php

/**
 * Class Extend_Warranty_Model_ProductSyncProcessor
 */
class Extend_Warranty_Model_SyncProcessor_Orders extends Extend_Warranty_Model_SyncProcessor
{
    const EXTEND_HISTORICAL_ORDERS_TABLE_NAME = 'extend_historical_orders';

    protected $collection;

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $batchSize = $this->getBatchSize();

            $orderCollection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->setPageSize($batchSize);;

            $orderCollection->getSelect()->joinLeft(
                array('historical' => $this->resourceConnection->getTableName(self::EXTEND_HISTORICAL_ORDERS_TABLE_NAME)),
                'historical.entity_id = main_table.entity_id',
                array('historical.was_sent')
            );

            $orderCollection->addFieldToFilter('historical.was_sent', ['null' => true]);

            $this->collection = $orderCollection;
        }
        return $this->collection;
    }

    public function getSyncHandler()
    {
        return Mage::getModel('warranty/api_sync_products_handler');
    }

}