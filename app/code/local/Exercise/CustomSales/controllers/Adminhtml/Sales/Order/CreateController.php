<?php

    class Exercise_CustomSales_Adminhtml_Sales_Order_CreateController extends Mage_Adminhtml_Controller_Action
    {
        public function indexAction()
        {
			//print_r($this);
            $this->loadLayout();
            $this->renderLayout();
            return $this;

           // If I echo something here I do see what I echo
        }
    }