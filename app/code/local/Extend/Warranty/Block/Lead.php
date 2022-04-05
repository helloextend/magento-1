<?php

class Extend_Warranty_Block_Lead extends Mage_Core_Block_Template
{
    public function getLeadTokenFromUrl()
    {
        $leadToken = $this->getRequest()->getQuery('leadToken');
        return $leadToken;
    }
}