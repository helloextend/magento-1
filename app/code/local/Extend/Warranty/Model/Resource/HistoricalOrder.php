<?php

class Extend_Warranty_Model_Resource_HistoricalOrder extends Mage_Core_Model_Resource_Db_Abstract
{

    protected $_isPkAutoIncrement  = false;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('warranty/historical_orders', 'entity_id');
    }
}