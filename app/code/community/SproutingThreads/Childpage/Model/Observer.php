<?php
class SproutingThreads_Childpage_Model_Observer
{
	public function __construct()
	{
	}
	public function injectTabs($observer)
	{
	 $block = $observer->getEvent()->getBlock();
	 // add tab in customer edit page
	 if ($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tabs)
         {
			if ($this->_getRequest()->getActionName() == 'edit' || $this->_getRequest()->getParam('type')) 
				{
					$block->addTab('domains', array('label'=> Mage::helper('customer')->__('Manage Licensed Domains'),'url'=> $block->getUrl('*/*/custom', array('_current' => true)),'class'=> 'ajax'));
				}
		}
	}
	protected function _getRequest()
    {
		return Mage::app()->getRequest();
    }
}