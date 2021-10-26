<?php

class Extend_Warranty_Block_System_Config_Products_Button extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<tr id="' . $element->getHtmlId() . '_container">';
        $html .= '<td class="label">' . $element->getLabelHtml() . '</td>';
        $html .= '<td class="value">';
        $html .= '<button title="Save Config" id="extend_sync_products" type="button" class="scalable save" onclick=\'syncProducts("' . $this->getUrl('adminhtml/Products/sync') . '")\'><span>Sync Products</span></button>';
        $html .= '<div class="sync-info">
                    <p id="sync-msg" style="display: block;"><strong>Last Sync: </strong><span id="sync-time">' . Mage::helper('warranty/connector')->getLastSyncDate() . '</span></p>
                    <a id="cancel_sync" style="display: none;">Cancel Sync</a>
                </div>';
        $html .= '</td>';
        $html .= '</tr>' . "\n";
        $html .= '<tr>';

        return $html;
    }
}
