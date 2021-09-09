<?php

class Extend_Warranty_Block_System_Config_Intro extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $html .= '<div class="extend__intro">
                    <img id="extend-logo" src="' . Mage::getDesign()->getSkinBaseUrl(array('_area' => 'adminhtml')) . '/images/extend/logo_slogan.svg" alt="' . $this->__('Logo Image') . '">
                    <div class="extend__info">
                        <p id="extend-intro">' . $this->__('Extend generates new revenue for your store, increases overall purchase conversions, and provides customers with streamlined product protection and peace of mind.') . '</p>
                        <p><a href="https://extend.com/merchants">' . $this->__('Learn more') . '</a></p>
                    </div>
                    <img id="extend-mobile" src="' . Mage::getDesign()->getSkinBaseUrl(array('_area' => 'adminhtml')) . '/images/extend/mobile-offer.svg" alt="' . $this->__('Mobile Image') . '">
                    </div>
                    <a href="https://merchants.extend.com" class="action action-extend-external">' . $this->__('Set up my Extend account') . '</a>
                    or <a href="https://merchants.extend.com" class="extend-account-link">' . $this->__('I already have an Extend account, I\'m ready to edit my settings') . '</a>
                    ' . $this->__('For more information or help, contact') . ' <a href="mailto:support@extend.com">support@extend.com</a>.
                    </div>';
        $html .= $this->_getFooterHtml($element);

        return $html;
    }
}
