<?php

class Magestore_Giftvoucher_Block_Adminhtml_Generategiftcard extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_generategiftcard';
        $this->_blockGroup = 'giftvoucher';
        $this->_headerText = Mage::helper('giftvoucher')->__('Gift Code Pattern Manager');
        $this->_addButtonLabel = Mage::helper('giftvoucher')->__('Add Gift Code Pattern');
        parent::__construct();
    }

}