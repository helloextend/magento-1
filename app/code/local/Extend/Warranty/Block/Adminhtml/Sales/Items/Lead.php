<?php

class Extend_Warranty_Block_Adminhtml_Sales_Items_Lead extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    /**
     * @return mixed|null
     */

    public function getItem()
    {
        return $this->getParentBlock()->getItem();
    }

    public function getOrder()
    {
        return $this->getItem()->getOrder();
    }

    public function getLeadToken($_item)
    {
        if ($_item->getProductType() === Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            foreach ($_item->getChildrenItems() as $child) {
                if ($child->getLeadToken()) {
                    $leadTokenJson = $child->getLeadToken();
                }
            }
        } elseif ($_item->getLeadToken()) {
            $leadTokenJson = $_item->getLeadToken();
        }
        $leadTokens = json_decode($leadTokenJson, true);
        $leadToken = is_array($leadTokens) ? reset($leadTokens) : $leadTokens;
        return $leadToken;
    }

    public function _toHtml()
    {
        if (!$this->getLeadToken($this->getItem())
            || $this->getItem()->getProductType() == Extend_Warranty_Model_Product_Type::TYPE_CODE) {
            return '';
        }
        return parent::_toHtml(); // TODO: Change the autogenerated stub
    }
}