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
		 
		$order_item_info = $readConnection->fetchOne('SELECT order_item_info FROM ' . $sales_recurring_profile.' WHERE profile_id="'.$profileId.'"');
		 
		$order_item_info=unserialize($order_item_info); 
		 
		$sales_recurring_profile_order=$resource->getTableName('sales_recurring_profile_order');
		
		
		$orderIdArray = $readConnection->fetchCol('SELECT order_id FROM ' . $sales_recurring_profile_order.' WHERE profile_id="'.$profileId.'"');
		$orderId=$orderIdArray[0]; 
		$sales_flat_order_item=$resource->getTableName('sales_flat_order_item');
		$orderDetailsArray = $readConnection->fetchCol('SELECT product_options FROM ' . $sales_flat_order_item.' WHERE order_id="'.$orderId.'"'); 
		$options=unserialize($orderDetailsArray[0]);
	 
		$info_buyRequest=$options['info_buyRequest']['options'];
		$info_options=$options['options'];
		foreach($info_options as $key=>$inf)
		{
				if($inf['label']=='Name')
				{ 
					$options['info_buyRequest']['options'][$inf['option_id']]=$Name;
					$options['options'][$key]['value']=$Name;
					$options['options'][$key]['print_value']=$Name;
					$options['options'][$key]['option_value']=$Name;
				}
				if($inf['label']=='Top Size')
				{ 
					$options['info_buyRequest']['options'][$inf['option_id']]=$TopSize;
					$options['options'][$key]['value']=$TopSize;
					$options['options'][$key]['print_value']=$TopSize;
					$options['options'][$key]['option_value']=$TopSize;
				}	
				if($inf['label']=='Height')
				{ 
					$options['info_buyRequest']['options'][$inf['option_id']]=$Height;
					$options['options'][$key]['value']=$Height;
					$options['options'][$key]['print_value']=$Height;
					$options['options'][$key]['option_value']=$Height;
				}	
				if($inf['label']=='Weight')
				{ 
					$options['info_buyRequest']['options'][$inf['option_id']]=$Weight;
					$options['options'][$key]['value']=$Height;
					$options['options'][$key]['print_value']=$Height;
					$options['options'][$key]['option_value']=$Height;
				}	
				if($inf['label']=='Bottom Size')
				{ 
					$options['info_buyRequest']['options'][$inf['option_id']]=$BottomSize;
					$options['options'][$key]['value']=$BottomSize;
					$options['options'][$key]['print_value']=$BottomSize;
					$options['options'][$key]['option_value']=$BottomSize;
				}	
				if($inf['label']=='Dress Size')
				{ 
					$options['info_buyRequest']['options'][$inf['option_id']]=$DressSize;
					$options['options'][$key]['value']=$DressSize;
					$options['options'][$key]['print_value']=$DressSize;
					$options['options'][$key]['option_value']=$DressSize;
				}	
				if($inf['label']=='Picky')
				{ 
					$options['info_buyRequest']['options'][$inf['option_id']]=$Details;
					$options['options'][$key]['value']=$Details;
					$options['options'][$key]['print_value']=$Details;
					$options['options'][$key]['option_value']=$Details;
				}	
				/*if($inf['label']=='Gender')
				{ 
					$options['info_buyRequest']['options'][$inf['option_id']]=$DressSize;
					$options['options'][$key]['value']=$DressSize;
					$options['options'][$key]['print_value']=$DressSize;
					$options['options'][$key]['option_value']=$DressSize;
				}		*/
				
		} 
		 
		$order_item_info['info_buyRequest']=serialize($options['info_buyRequest']);
		$order_item_info['options']=serialize($options);
		$newRecurring=serialize($order_item_info);
		//$newRecurring=str_replace('?','',$newRecurring); 
		$newRecurring=addslashes($newRecurring); 
	 	$newData=serialize($options);
		//$newData=str_replace('?','',$newData); 
		$newData=addslashes($newData);
		$query = "UPDATE {$sales_flat_order_item} SET product_options = '{$newData}' WHERE order_id = "
            . (int)$orderId; 
		 $writeConnection->query($query);
		 $query = "UPDATE {$sales_recurring_profile} SET order_item_info = '{$newRecurring}' WHERE profile_id = "
            . (int)$profileId; 
		 $writeConnection->query($query);
		// Mage::getSingleton('core/session')->addSuccess('Child profile has been updated');
		  
		}
			catch (Exception $e)
			{
				Mage::getSingleton('core/session')->addError($e->getMessage());
			   
			}
		}
		
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
				
			}
			else 
			{
				$period_frequency = 1;
				$period_max_cycles =24;
				$schedule_description = 'Monthly 20 Subscription';
				
			}
			$query = "UPDATE {$sales_recurring_profile} SET period_frequency = '{$period_frequency}',
			 period_max_cycles = '{$period_max_cycles}',
			 schedule_description = '{$schedule_description}'
			 WHERE profile_id = "
            . (int)$profileId; 
		 	$writeConnection->query($query);
			
			$sales_recurring_profile=$resource->getTableName('sales_recurring_profile');
		 
		$order_item_info = $readConnection->fetchOne('SELECT order_item_info FROM ' . $sales_recurring_profile.' WHERE profile_id="'.$profileId.'"');
		 
		$order_item_info=unserialize($order_item_info); 
		 
		$sales_recurring_profile_order=$resource->getTableName('sales_recurring_profile_order');
		
		
		$orderIdArray = $readConnection->fetchCol('SELECT order_id FROM ' . $sales_recurring_profile_order.' WHERE profile_id="'.$profileId.'"');
		$orderId=$orderIdArray[0]; 
		$sales_flat_order_item=$resource->getTableName('sales_flat_order_item');
		$orderDetailsArray = $readConnection->fetchCol('SELECT product_options FROM ' . $sales_flat_order_item.' WHERE order_id="'.$orderId.'"'); 
		$options=unserialize($orderDetailsArray[0]);
	 
		$info_buyRequest=$options['info_buyRequest']['options'];
		$info_options=$options['options'];
		foreach($info_options as $key=>$inf)
		{
			
				if($inf['label']=='SubType')
				{ 
					 
		$profiles=Mage::getModel('sales/recurring_profile')->getCollection() 
			->addFieldToFilter('profile_id',$profileId)
			->addFieldToSelect('*')
            ->setOrder('profile_id', 'desc');
			$prof=$profiles->getData();  
			 
					 
			$info=unserialize($prof[0]['order_item_info']);
			$child=unserialize($info['info_buyRequest']); 
			$child1=unserialize($info['options']); 
			 
		$opt=$child1['options'];
		 
			
			$product=$child['product']?$child['product']:$child['product_id'];
					$child=unserialize($info['info_buyRequest']); 
					$poptions = Mage::getModel('catalog/product')->load($product)->getProductOptionsCollection();
         foreach ($poptions as $o) 
             {  
			  
				$firstchilddetails[$o->getData('title')][]=$child['options'][$o->getData('option_id')]; 
				$values = $o->getValues();
				$mydata='';
				foreach($values as $v)
                  {
                     $mydata[] = $v->getData();
                                         
				  }
				 $firstchilddetails[$o->getData('title')]=$mydata; 
               } 
			   
			   foreach($firstchilddetails['SubType'] as $cats)
						{
							 
	 					if($cats['option_type_id']==$check)
						{
							//print_r($cats);
						 	$options['info_buyRequest']['options'][$inf['option_id']]=$check;
							$options['options'][$key]['value']=$cats['default_title'];
							$options['options'][$key]['print_value']=$cats['default_title'];
							$options['options'][$key]['option_value']=$check;
						} 
						} 
				} 
				
		} 
		 
		$order_item_info['info_buyRequest']=serialize($options['info_buyRequest']);
		$order_item_info['options']=serialize($options);
		$newRecurring=serialize($order_item_info);
		//$newRecurring=str_replace('?','',$newRecurring); 
		$newRecurring=addslashes($newRecurring); 
	 	$newData=serialize($options);
		//$newData=str_replace('?','',$newData); 
		$newData=addslashes($newData);
		$query = "UPDATE {$sales_flat_order_item} SET product_options = '{$newData}' WHERE order_id = "
            . (int)$orderId; 
		 $writeConnection->query($query);
		 $query = "UPDATE {$sales_recurring_profile} SET order_item_info = '{$newRecurring}' WHERE profile_id = "
            . (int)$profileId; 
		 $writeConnection->query($query);
		//	 $this->_redirect('sales/recurring_profile/?child='.$profileId);
			
			 
		 
			
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