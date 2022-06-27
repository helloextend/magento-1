<?php

/**
 * Class Extend_Warranty_Model_ProductSyncProcessor
 */
class Extend_Warranty_Model_SyncProcessor_Products  extends Extend_Warranty_Model_SyncProcessor
{
    /**
     * @return \Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $batchSize = $this->getBatchSize();

            $productCollection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('type_id', array('neq' => 'warranty'))
                ->setPageSize($batchSize);;
            $this->collection = $productCollection;
        }
        return $this->collection;
    }

    public function getSyncHandler()
    {
        return Mage::getModel('warranty/api_sync_products_handler');
    }
}