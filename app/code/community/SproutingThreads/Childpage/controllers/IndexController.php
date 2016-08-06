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
		 echo '<pre>';
			print_r($opt);
			
			
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
				 $firstchilddetails[$o->getData('title')][]=$mydata; 
               } 
			   
			   foreach($firstchilddetails['SubType'][1] as $cats)
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
		catch (Exception $e)
		{
			Mage::getSingleton('core/session')->addSuccess($e->getMessage());
		    $this->_redirect('sales/recurring_profile/?child='.$profileId);
		}
	}
} 