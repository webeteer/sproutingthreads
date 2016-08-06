<?php

class Exercise_CustomSales_Block_Adminhtml_Sales_Order_Create_Test extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_sales_order_create_test';
        $this->_blockGroup = 'Exercise_CustomSales';
        $this->_headerText = Mage::helper('Exercise_CustomSales')->__('Update');
        parent::__construct();
        $this->_removeButton('add');

    }  

    public function getHeaderText()
    {
        return Mage::helper('Exercise_CustomSales')->__('Edit');
    }
}