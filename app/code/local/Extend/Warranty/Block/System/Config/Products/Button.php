<?php

class Extend_Warranty_Block_System_Config_Products_Button extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        //TODO: update template, AJAX sync will be added in future
        $html = '<tr id="' . $element->getHtmlId() . '_container">';
        $html .= '<td class="label">' . $element->getLabelHtml() . '</td>';
        $html .= '<td class="value">';
        // $html .= '<button id="id_be2c9ac0b74ee7bcf813be7e92d5f1f8" title="Save Config" type="button" class="scalable save" onclick="alert()" style=""><span><span><span>Sync Products</span></span></span></button>';
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
