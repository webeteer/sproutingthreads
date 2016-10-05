<?php

class Naviz_Child_Model_Observer
{     
    public function customer_save_after($observer)
    {
		$details=Mage::app()->getRequest()->getPost('child'); 
		
		foreach($details as $key=>$child)
		{
			 
			$profileId=$key;
			try{ 
		$Name=$child['name'];
		$TopSize=$child['atop'];
		$Height=$child['height'];
		$Weight=$child['weight'];
		$BottomSize=$child['bottom'];
		$DressSize=$child['dress'];
		$Details=$child['details'];
		$resource = Mage::getSingleton('core/resource');       
		$readConnection = $resource->getConnection('core_read');       
		$writeConnection = $resource->getConnection('core_write');
		
		$sales_recurring_profile=$resource->getTableName('sales_recurring_profile');
		 
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
			if('Picky'==$o['title']) {$info_buyRequest['options'][$o['option_id']]=($Details);}
			 
			 
		 }
		 $order_item_info['info_buyRequest']=serialize($info_buyRequest);
		 $updated_order_item_info=serialize($order_item_info);
		 $updated_order_item_info=addslashes($updated_order_item_info);
		  $query = "UPDATE {$sales_recurring_profile} SET order_item_info = '{$updated_order_item_info}' WHERE profile_id = ".(int)$profileId; 
		  
		 $writeConnection->query($query);
		// Mage::getSingleton('core/session')->addSuccess('Child profile has been updated');
		 
		 
		 
		 $child_subscription=Mage::app()->getRequest()->getPost('child_subscription');
		foreach($child_subscription as $key=>$childs)
		{
			$profileId=$key; 
			$check=$childs['check'];
			$frequency=$childs['frequency'];
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
		 
			
		}
		 
		  
		}
			catch (Exception $e)
			{
				Mage::getSingleton('core/session')->addError($e->getMessage());
			   
			}
		} 
		
		 
    }
	
	
	public function coreBlockAbstractPrepareLayoutAfter(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getFrontController()->getAction()->getFullActionName() === 'adminhtml_dashboard_index')
        {
            $block = $observer->getBlock();
            if ($block->getNameInLayout() === 'dashboard')
            {
                $block->getChild('lastOrders')->setUseAsDashboardHook(true);
            }
        }
    }

    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getFrontController()->getAction()->getFullActionName() === 'adminhtml_dashboard_index')
        {
            if ($observer->getBlock()->getUseAsDashboardHook())
            {
                 $html = $observer->getTransport()->getHtml();
                $myBlock = $observer->getBlock()->getLayout()
                    ->createBlock('core/template')
                    ->setTemplate('child/dashboard/orders.phtml');
                $html .= $myBlock->toHtml();
                $observer->getTransport()->setHtml($html);
            }
        }
    }
	
}