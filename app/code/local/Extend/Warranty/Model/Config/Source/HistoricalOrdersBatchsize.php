<?php

class Extend_Warranty_Model_Config_Source_HistoricalOrdersBatchsize
{
    const VALUE_1 = 1;
    const VALUE_5 = 5;
    const VALUE_10 = 10;

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::VALUE_1, 'label' => Mage::helper('warranty')->__('1')),
            array('value' => self::VALUE_5, 'label' => Mage::helper('warranty')->__('5')),
            array('value' => self::VALUE_10, 'label' => Mage::helper('warranty')->__('10')),
        );
    }
}
