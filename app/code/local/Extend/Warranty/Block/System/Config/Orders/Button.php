<?php

class Extend_Warranty_Block_System_Config_Orders_Button extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return element Html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {

        $currentStoreId = null;
        $urlParams = array();
        
        if (strlen($code = Mage::app()->getRequest()->getParam('store'))) // store level
        {
            $urlParams = array('store' => $code);
            $currentStoreId = Mage::getModel('core/store')->load($code)->getId();
        }

        $html = '<tr id="' . $element->getHtmlId() . '_container">';
        $html .= '<td class="label">' . $element->getLabelHtml() . '</td>';
        $html .= '<td class="value">';
        $html .= '<button title="Save Config" id="extend_sync_products" type="button" class="scalable save" onclick=\'syncOrders("' . $this->getUrl('adminhtml/sync_orders/sync', $urlParams) . '")\'><span>Sync Orders</span></button>';
        $html .= '<div class="sync-info">
                    <p id="sync-msg" style="display: block;"><strong>Last Sync: </strong><span id="sync-time">' . Mage::helper('warranty/connector')->getHistoricalOrdersSyncPeriod($currentStoreId) . '</span></p>
                    <a id="cancel_sync" style="display: none;">Cancel Sync</a>
                </div>';
        $html .= '</td>';
        $html .= '</tr>' . "\n";
        $html .= '<tr>';

        return $html;
    }

    protected function getCurrenctStore()
    {
        if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getStore())) // store level
        {
            $store_id = Mage::getModel('core/store')->load($code)->getId();
        } elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) // website level
        {
            $website_id = Mage::getModel('core/website')->load($code)->getId();
            $store_id = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
        } else // default level
        {
            $store_id = 0;
        }
        return $store_id;
    }
}
