<?php

class Magestore_Giftvoucher_Block_Adminhtml_Gifthistory extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_gifthistory';
        $this->_blockGroup = 'giftvoucher';
        $this->_headerText = Mage::helper('giftvoucher')->__('Gift Card History');
        parent::__construct();
        $this->_removeButton('add');
    }

}