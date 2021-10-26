<?php

class Extend_Warranty_Model_Config_Source_Authmode
{
    const LIVE_MODE = 1;
    const SANDBOX_MODE = 0;

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::LIVE_MODE, 'label' => Mage::helper('warranty')->__('Live')),
            array('value' => self::SANDBOX_MODE, 'label' => Mage::helper('warranty')->__('Sandbox')),
        );
    }
}
