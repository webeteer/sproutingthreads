<?php

class Magestore_Giftvoucher_Model_Templateoptions extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAvailableTemplate() {
        $templates = Mage::getModel('giftvoucher/gifttemplate')->getCollection()
                ->addFieldToFilter('status', '1');
        $listTemplate = array();
        foreach ($templates as $template) {
            $listTemplate[] = array('label' => $template->getTemplateName(),
                'value' => $template->getId());
        }
        return $listTemplate;
    }

    public function getAllOptions($withEmpty = true) {
        if (is_null($this->_options)) {
            $this->_options = $this->getAvailableTemplate();
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array(
                'value' => '',
                'label' => '-- Please Select --',
            ));
        }
        return $options;
    }

}