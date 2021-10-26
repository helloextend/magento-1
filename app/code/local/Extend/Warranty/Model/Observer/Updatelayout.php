<?php

/**
 * Class Extend_Warranty_Model_Observer_Updatelayout
 */
class Extend_Warranty_Model_Observer_Updatelayout
{

    public function execute()
    {
        if (Mage::getSingleton('core/layout')->getBlock('head')) {
            Mage::getSingleton('core/layout')->getBlock('head')->setIsEnterprise(Mage::getEdition() == Mage::EDITION_ENTERPRISE);
            Mage::getSingleton('core/layout')->getBlock('head')->setIsCommunity(Mage::getEdition() == Mage::EDITION_COMMUNITY);
        }
    }
}