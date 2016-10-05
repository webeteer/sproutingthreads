<?php
class SproutingThreads_Childpage_IndexController extends Mage_Core_Controller_Front_Action {
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();
 
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    } 
	
    public function indexAction()
    {
        $this->loadLayout();
		$navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('childprofile');
        }
		$this->renderLayout();
    } 
	
	public function saveAction()
	{
		$profileId=$this->getRequest()->getParam('profile');
	 	try{
		
		$Name=$this->getRequest()->getParam('name');
		$TopSize=$this->getRequest()->getParam('atop');
		$Height=$this->getRequest()->getParam('height');
		$Weight=$this->getRequest()->getParam('weight');
		$BottomSize=$this->getRequest()->getParam('bottom');
		$DressSize=$this->getRequest()->getParam('dress');
		$Details=$this->getRequest()->getParam('details');
		$resource = Mage::getSingleton('core/resource');       
		$readConnection = $resource->getConnection('core_read');       
		$writeConnection = $resource->getConnection('core_write');
		
		$sales_recurring_profile=$resource->getTableName('sales_recurring_profile');
		
		/*$order_item_info = $readConnection->fetchOne('SELECT order_item_info FROM ' . $sales_recurring_profile.' WHERE profile_id="245"');
		$query = "UPDATE {$sales_recurring_profile} SET order_item_info = '{$order_item_info}' WHERE profile_id = ".(int)$profileId; 
		$writeConnection->query($query); */
	
		 
		$order_item_info_db = $readConnection->fetchOne('SELECT order_item_info FROM ' . $sales_recurring_profile.' WHERE profile_id="'.$profileId.'"');
		
		$order_item_info=unserialize($order_item_info_db);  
		$info_buyRequest=unserialize($order_item_info['info_buyRequest']);  
		$childoptions=$info_buyRequest['options']; 
		$product=$info_buyRequest['product_id']?$info_buyRequest['product_id']:$info_buyRequest['product'];
		$options = Mage::getModel('catalog/product')->load($product)->getProductOptionsCollection();
		foreach ($options as $o) 
		 { 
		 	if('Name'==$o['title']) {$info_buyRequest['options'][$o['option_id']]=$Name;}
			if('Top Size'==$o['title']) {$info_buyRequest['options'][$o['option_id']]=$TopSize;}
			if('Height'==$o['title']) {$info_buyRequest['options'][$o['option_id']]=$Height;}
			if('Weight'==$o['title']) {$info_buyRequest['options'][$o['option_id']]=$Weight;}
			if('Bottom Size'==$o['title']) {$info_buyRequest['options'][$o['option_id']]=$BottomSize;}
			if('Dress Size'==$o['title']) {$info_buyRequest['options'][$o['option_id']]=$DressSize;}
			if('Picky'==$o['title']) {$info_buyRequest['options'][$o['option_id']]=$Details;}
			 
			 
		 }
		 $order_item_info['info_buyRequest']=serialize($info_buyRequest);
		 $updated_order_item_info=serialize($order_item_info);
	 	 $updated_order_item_info=addslashes($updated_order_item_info);
		  $query = "UPDATE {$sales_recurring_profile} SET order_item_info = '{$updated_order_item_info}' WHERE profile_id = ".(int)$profileId; 
		 $writeConnection->query($query); 
		 Mage::getSingleton('core/session')->addSuccess('Your child profile has been updated');
		 $this->_redirect('childprofile/?child='.$profileId);
		}
		catch (Exception $e)
		{
			Mage::getSingleton('core/session')->addSuccess($e->getMessage());
		  $this->_redirect('childprofile/?child='.$profileId);
		}
	}
	
	public function subscriptionAction()
	{
		$profileId=$this->getRequest()->getParam('profile');
		try
		{
			$check=$this->getRequest()->getParam('check');
			$frequency=$this->getRequest()->getParam('frequency');
			$resource = Mage::getSingleton('core/resource');       
			$readConnection = $resource->getConnection('core_read');       
			$writeConnection = $resource->getConnection('core_write'); 
		    $sales_recurring_profile=$resource->getTableName('sales_recurring_profile');
			
			if($frequency=='seasonal')
			{
				$period_frequency = 3;
				$period_max_cycles =8;
				$schedule_description = 'Season Subscription';
				$product_name='Seasonal 20 Subscription';
				
			}
			else 
			{
				$period_frequency = 1;
				$period_max_cycles =24;
				$schedule_description = 'Monthly 20 Subscription';
				$product_name='Monthly 20 Subscription';
				
			}
			$query = "UPDATE {$sales_recurring_profile} SET period_frequency = '{$period_frequency}',
			 period_max_cycles = '{$period_max_cycles}',
			 schedule_description = '{$schedule_description}'
			 WHERE profile_id = "
            . (int)$profileId; 
		 	$writeConnection->query($query);
			
			$order_item_info_db = $readConnection->fetchOne('SELECT order_item_info FROM ' . $sales_recurring_profile.' WHERE profile_id="'.$profileId.'"'); 
			
		$order_item_info=unserialize($order_item_info_db);  
		$order_item_info['name']=$product_name;
		
		$info_buyRequest=unserialize($order_item_info['info_buyRequest']);  
		$childoptions=$info_buyRequest['options']; 
		$product=$info_buyRequest['product_id']?$info_buyRequest['product_id']:$info_buyRequest['product'];
		$options = Mage::getModel('catalog/product')->load($product)->getProductOptionsCollection();
		 
		foreach ($options as $o) 
		 { 
		 
		 	if('SubType'==$o['title']) {$info_buyRequest['options'][$o['option_id']]=$check;}
			   
		 }
		 $order_item_info['info_buyRequest']=serialize($info_buyRequest);
		 $updated_order_item_info=serialize($order_item_info);
		  $updated_order_item_info=addslashes($updated_order_item_info);
		 $query = "UPDATE {$sales_recurring_profile} SET order_item_info = '{$updated_order_item_info}' WHERE profile_id = ".(int)$profileId; 
		 
		 $writeConnection->query($query);
		$this->_redirect('sales/recurring_profile/?child='.$profileId);
			
		}
		catch (Exception $e)
		{
			Mage::getSingleton('core/session')->addSuccess($e->getMessage());
		    $this->_redirect('sales/recurring_profile/?child='.$profileId);
		}
	}
} 