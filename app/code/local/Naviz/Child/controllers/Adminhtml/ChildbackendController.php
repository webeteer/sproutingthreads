<?php
class Naviz_Child_Adminhtml_ChildbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Child details"));
	   $this->renderLayout();
    }
}