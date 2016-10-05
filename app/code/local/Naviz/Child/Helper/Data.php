<?php
class Naviz_Child_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getChildProfiles($customer_id,$state=false)
	{
		
		try
		{
			$childprofile="";
			$profiles=Mage::getModel('sales/recurring_profile')->getCollection()
            ->addFieldToFilter('customer_id',  $customer_id);
			if($state){ $profiles->addFieldToFilter('state','active');}
			$profiles->addFieldToSelect('*');
            $profiles->setOrder('profile_id', 'desc');
			$profiles=$profiles->getData();  
			foreach($profiles as $profile)
			{
				  
				$childprofile[] = $this->getChildDetails($product,$profile); 
			}
			return $childprofile;
			
		}
		catch(Exception $e)
		{
			Mage::getSingleton('core/session')->addSuccess($e->getMessage());
		}
		
		
	}
	
	public function getChildProfile($customer_id,$profileId,$state=false)
	{
		
		try
		{
			$profiles=Mage::getModel('sales/recurring_profile')->getCollection()
            ->addFieldToFilter('customer_id',  $customer_id);
			$profiles->addFieldToFilter('profile_id',$profileId);
			if($state){ $profiles->addFieldToFilter('state','active');}
			$profiles->addFieldToSelect('*');
            $profiles->setOrder('profile_id', 'desc');
			
			$profiles=$profiles->getData();  
			foreach($profiles as $profile)
			{
				  
				$childprofile = $this->getChildDetails($product,$profile); 
			}
			return $childprofile;
			
		}
		catch(Exception $e)
		{
			Mage::getSingleton('core/session')->addSuccess($e->getMessage());
		}
		
		
	}
	
	public function getChildDetails($product,$profile)
	{
		
	 	
		$order_item_info=unserialize($profile['order_item_info']);  
		$info_buyRequest=unserialize($order_item_info['info_buyRequest']);  
		$childoptions=$info_buyRequest['options'];
		$product=$order_item_info['product_id']?$order_item_info['product_id']:$info_buyRequest['product'];
		$options = Mage::getModel('catalog/product')->load($product)->getProductOptionsCollection();
		foreach ($options as $o) 
		 { 
		 	if('Birthday'==$o['title'])
			{
				$strBDate=$childoptions[$o['option_id']]['date_internal'];  
				$strDateToCheckAge = new Zend_Date();;
				$dateDOB = new Zend_Date( $strBDate, 'YYYY-MM-dd' );
				$dateAgeAsOf = new Zend_Date( $strDateToCheckAge, 'YYYY-MM-dd' ); 
				$dateAgeAsOf->sub( $dateDOB, Zend_Date::ISO_8601 );
				$strAge = $dateAgeAsOf->get( Zend_Date::YEAR ); 
				$childoptions[$o['option_id']]['age']=$strAge;
			}
			if('Likes'==$o['title'])
			{
				$likes=json_decode(trim($childoptions[$o['option_id']]));
				if($likes->upload!='')
				{
					$profileimg='/var/uploads/'.$likes->upload;
				}
				else
				{
					$profileimg="/var/uploads/no_image_available.jpeg";
				}
				$childprofile['Profileimg'][]=$profileimg;
			}
			
			$childprofile[$o['title']][]=$childoptions[$o['option_id']];
			$values = $o->getValues();
			$mydata='';
			foreach($values as $v){ $mydata[] = $v->getData(); }
			$childprofile[$o['title']][]=$mydata;
			
			if('Gender'==$o['title'])
			{
				$gndrs=$childprofile['Gender'][1];
				foreach($gndrs as $gndr)
				{
					if($childprofile['Gender'][0]==$gndr['option_type_id'])
					{
						$childprofile['Gender'][]=$gndr['default_title'];
					} 
				}
				
				$childprofile['Profileimg'][]=$profileimg;
			}
			
		 }
		 $childprofile['profile_id']=$profile['profile_id'];
		 $childprofile['childoptions']=$childoptions;
		 $childprofile['product']=$product;
		  $childprofile['info_buyRequest']=$info_buyRequest;
		 $childprofile['period_frequency']=$profile['period_frequency'];
		 $childprofile['state']=$profile['state'];
		 return $childprofile;
	}
}
	 