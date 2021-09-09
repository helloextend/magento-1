<?php

class Extend_Warranty_Block_System_Config_Portallink extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<tr id="' . $element->getHtmlId() . '_container">';
        $html .= '<td class="value">';
        $html .= '<button id="id_be2c9ac0b74ee7bcf813be7e92d5f1f8" title="Go to my Extend merchant dashboard" type="button" class="scalable save" onclick="window.location.href = \'https://merchants.extend.com\'" style=""><span><span><span>Go to my Extend merchant dashboard</span></span></span></button>';
        $html .= '</td>';
        $html .= '</tr>' . "\n";
        $html .= '<tr>';
        return $html;
    }
}
