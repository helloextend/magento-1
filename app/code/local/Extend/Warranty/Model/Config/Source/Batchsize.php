<?php

class Extend_Warranty_Model_Config_Source_Batchsize
{
    const VALUE_25 = 25;
    const VALUE_50 = 50;
    const VALUE_100 = 100;

    public function toOptionArray()
    {
        return array(
            array('value' => self::VALUE_25, 'label' => Mage::helper('warranty')->__('25')),
            array('value' => self::VALUE_50, 'label' => Mage::helper('warranty')->__('50')),
            array('value' => self::VALUE_100, 'label' => Mage::helper('warranty')->__('100')),
        );
    }
}
