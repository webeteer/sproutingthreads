<?php
/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 * 
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 * 
 * Want to customize or need help with your store?
 *  Phone: 717-431-3330
 *  Email: sales@paradoxlabs.com
 *
 * @category	ParadoxLabs
 * @package		AuthorizeNetCim
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

class ParadoxLabs_AuthorizeNetCim_Model_Observer_Legacy
{
	/**
	 * Check if the customer has been converted before returning stored cards.
	 * If they have not, run the conversion process inline.
	 */
	public function convertStoredData( $observer )
	{
		$customer	= $observer->getEvent()->getCustomer();
		$method		= $observer->getEvent()->getMethod();
		
		/**
		 * Short circuit if this isn't us.
		 */
		if( is_null( $method ) || $method != 'authnetcim' ) {
			return $this;
		}
		
		$profileId	= $customer->getAuthnetcimProfileId();
		
		if( $customer && $customer->getId() && !empty( $profileId ) && $customer->getAuthnetcimProfileVersion() < 200 ) {
			/**
			 * Update customer data from 1.x trunk to 2.x.
			 * That means:
			 * - Load all profile data from Authorize.Net
			 * - Merge in data from authnetcim/cards table
			 * - Create card records for each
			 * - Update any orders or profiles attached to those cards
			 */
			
			/**
			 * Fetch profile data from Authorize.Net
			 */
			$gateway	= Mage::helper('payment')->getMethodInstance('authnetcim')->gateway();
			$gateway->setParameter( 'customerProfileId', $profileId );
			
			$profile	= $gateway->getCustomerProfile();
			$cards		= array();
			
			$affectedCards	= 0;
			$affectedOrders	= 0;
			$affectedRps	= 0;
			
			if( isset( $profile['profile']['paymentProfiles'] ) && count( $profile['profile']['paymentProfiles'] ) > 0 ) {
				if( isset( $profile['profile']['paymentProfiles']['billTo'] ) ) {
					$cards[ $profile['profile']['paymentProfiles']['customerPaymentProfileId'] ] = $profile['profile']['paymentProfiles'];
				}
				else {
					foreach( $profile['profile']['paymentProfiles'] as $card ) {
						$cards[ $card['customerPaymentProfileId'] ] = $card;
					}
				}
			}
			
			if( count( $cards ) > 0 ) {
				/**
				 * Fetch and merge in data from authnetcim/cards (deleted/unsaved cards)
				 */
				$resource		= Mage::getSingleton('core/resource');
				$db				= $resource->getConnection('core_read');
				$cardTable		= $resource->getTableName('authnetcim/card');
				
				$sql			= $db->select()
									 ->from( $cardTable, array( 'profile_id', 'payment_id', 'added' ) )
									 ->where( 'customer_id=' . $customer->getId() );
				
				$excludedCards	= $db->fetchAll( $sql );
				if( count( $excludedCards ) > 0 ) {
					foreach( $excludedCards as $excluded ) {
						if( isset( $cards[ $excluded['payment_id'] ] ) && $excluded['profile_id'] == $profileId ) {
							$cards[ $excluded['payment_id'] ]['active']		= false;
							$cards[ $excluded['payment_id'] ]['last_use']	= strtotime( $excluded['added'] );
						}
					}
				}
				
				/**
				 * Create a card record for each
				 */
				foreach( $cards as $k => $card ) {
					if( !isset( $card['payment']['creditCard'] ) ) {
						continue;
					}
					
					$storedCard = Mage::getModel( 'authnetcim/card' );
					$storedCard->setMethod( 'authnetcim' )
							   ->setCustomer( $customer )
							   ->setProfileId( $profileId )
							   ->setPaymentId( $card['customerPaymentProfileId'] );
					
					if( isset( $card['last_use'] ) ) {
						$storedCard->setLastUse( strtotime( $card['last_use'] ) );
					}
					
					if( isset( $card['active'] ) && $card['active'] == false ) {
						$storedCard->setActive( 0 );
					}
					
					$addressData = array(
						'parent_id'			=> $customer->getId(),
						'customer_id'		=> $customer->getId(),
						'firstname'			=> $card['billTo']['firstName'],
						'lastname'			=> $card['billTo']['lastName'],
						'street'			=> $card['billTo']['address'],
						'city'				=> $card['billTo']['city'],
						'country_id'		=> $card['billTo']['country'],
						'region'			=> $card['billTo']['state'],
						'region_id'			=> Mage::getModel('directory/region')->loadByName( $card['billTo']['state'], $card['billTo']['country'] )->getId(),
						'postcode'			=> $card['billTo']['zip'],
						'telephone'			=> isset( $card['billTo']['phoneNumber'] ) ? $card['billTo']['phoneNumber'] : '',
						'fax'				=> isset( $card['billTo']['faxNumber'] ) ? $card['billTo']['faxNumber'] : '',
					);
					
					$storedCard->setData( 'address', serialize( $addressData ) );
					
					if( isset( $card['payment']['creditCard'] ) ) {
						$paymentData = array(
							'cc_type'			=> '',
							'cc_last4'			=> substr( $card['payment']['creditCard']['cardNumber'], -4 ),
							'cc_exp_year'		=> '',
							'cc_exp_month'		=> '',
						);
						
						$storedCard->setData( 'additional', serialize( $paymentData ) );
					}
					
					$storedCard->save();
					
					$cards[ $k ]['tokenbase_id'] = $storedCard->getId();
					
					$affectedCards++;
				}
				
				/**
				 * Update any attached orders
				 */
				$orders = Mage::getModel('sales/order')->getCollection()
								->addFieldToFilter( 'ext_customer_id', array( 'in' => array_keys( $cards ) ) );
				
				foreach( $orders as $order ) {
					$order->getPayment()->setTokenbaseId( $cards[ $order->getExtCustomerId() ]['tokenbase_id'] )
										->save();
					
					$affectedOrders++;
				}
				
				/**
				 * Update any attached recurring profiles
				 */
				$profiles = Mage::getModel('sales/recurring_profile')->getCollection()
									->addFieldToFilter( 'customer_id', $customer->getId() )
									->addFieldToFilter( 'state', array( 'nin' => array( 'expired', 'canceled' ) ) );
				
				foreach( $profiles as $profile ) {
					$adtl	= unserialize( $profile->getAdditionalInfo() );
					
					if( isset( $adtl['payment_id'] ) && isset( $cards[ $adtl['payment_id'] ] ) ) {
						$adtl['tokenbase_id'] = $cards[ $adtl['payment_id'] ]['tokenbase_id'];
					}
					
					$profile->setAdditionalInfo( serialize( $adtl ) )
							->save();
					
					$affectedRps++;
				}
				
				Mage::helper('tokenbase')->log( 'authnetcim', sprintf( "Updated records for customer %s (%d): %d cards, %d orders, %d profiles", $customer->getName(), $customer->getId(), $affectedCards, $affectedOrders, $affectedRps ) );
			}
			
			$customer->setAuthnetcimProfileVersion( 200 )
					 ->save();
		}
	}
	
	/**
	 * Make sure the current profile has a TokenBase record before proceeding.
	 * 
	 * eew.
	 */
	public function convertRecurringProfile( $observer )
	{
		$profile	= $observer->getEvent()->getProfile();
		
		if( $profile->getMethodCode() == 'authnetcim' ) {
			$adtl	= $profile->getAdditionalInfo();
			
			if( is_string( $adtl ) ) {
				$adtl	= unserialize( $adtl );
			}
			
			if( !isset( $adtl['tokenbase_id'] ) && !empty( $adtl['payment_id'] ) ) {
				try {
					$customer	= Mage::getModel('customer/customer');
					$customer->setEmail( $profile->getInfoValue( 'order_info', 'customer_email' ) );
					
					/**
					 * If we're a customer, try to convert their entire profile (which should cover this).
					 */
					if( $profile->getCustomerId() > 0 ) {
						$customer->load( $profile->getCustomerId() );
						
						Mage::dispatchEvent( 'tokenbase_before_load_active_cards', array( 'method' => 'authnetcim', 'customer' => $customer ) );
						
						$profile->load( $profile->getId() );
					}
					
					/**
					 * If that didn't work, create the card straight up.
					 */
					if( $profile->getInfoValue( 'additional_info', 'tokenbase_id' ) == '' ) {
						/**
						 * We don't know the customerProfileId; we need that first.
						 */
						$gateway	= Mage::helper('payment')->getMethodInstance('authnetcim')->gateway();
						$gateway->setParameter( 'merchantCustomerId', $customer->getId() );
						$gateway->setParameter( 'email', $customer->getEmail() );
						
						$profileId	= $gateway->createCustomerProfile();
						
						if( !empty( $profileId ) ) {
							$gateway->setParameter( 'customerProfileId', $profileId );
							
							$cimProfile	= $gateway->getCustomerProfile();
							$card		= null;
							
							if( isset( $cimProfile['profile']['paymentProfiles'] ) && count( $cimProfile['profile']['paymentProfiles'] ) > 0 ) {
								if( isset( $cimProfile['profile']['paymentProfiles']['billTo'] ) ) {
									if( $cimProfile['profile']['paymentProfiles']['customerPaymentProfileId'] == $adtl['payment_id'] ) {
										$card = $cimProfile['profile']['paymentProfiles'];
									}
								}
								else {
									foreach( $cimProfile['profile']['paymentProfiles'] as $row ) {
										if( $row['customerPaymentProfileId'] == $adtl['payment_id'] ) {
											$card = $row;
											break;
										}
									}
								}
								
								if( !is_null( $card ) ) {
									$storedCard = Mage::getModel( 'authnetcim/card' );
									$storedCard->setMethod( 'authnetcim' )
											   ->setCustomerId( $customer->getId() )
											   ->setCustomerEmail( $customer->getEmail() )
											   ->setProfileId( $profileId )
											   ->setPaymentId( $card['customerPaymentProfileId'] );
									
									$addressData = array(
										'parent_id'			=> $customer->getId(),
										'customer_id'		=> $customer->getId(),
										'firstname'			=> $card['billTo']['firstName'],
										'lastname'			=> $card['billTo']['lastName'],
										'street'			=> $card['billTo']['address'],
										'city'				=> $card['billTo']['city'],
										'country_id'		=> $card['billTo']['country'],
										'region'			=> $card['billTo']['state'],
										'region_id'			=> Mage::getModel('directory/region')->loadByName( $card['billTo']['state'], $card['billTo']['country'] )->getId(),
										'postcode'			=> $card['billTo']['zip'],
										'telephone'			=> isset( $card['billTo']['phoneNumber'] ) ? $card['billTo']['phoneNumber'] : '',
										'fax'				=> isset( $card['billTo']['faxNumber'] ) ? $card['billTo']['faxNumber'] : '',
									);
									
									$storedCard->setData( 'address', serialize( $addressData ) );
									
									if( isset( $card['payment']['creditCard'] ) ) {
										$paymentData = array(
											'cc_type'			=> '',
											'cc_last4'			=> substr( $card['payment']['creditCard']['cardNumber'], -4 ),
											'cc_exp_year'		=> '',
											'cc_exp_month'		=> '',
										);
										
										$storedCard->setData( 'additional', serialize( $paymentData ) );
									}
									
									$storedCard->save();
									
									
									$adtl['tokenbase_id'] = $storedCard->getId();
									
									$profile->setAdditionalInfo( serialize( $adtl ) )
											->save();
								}
								else {
									Mage::throwException( Mage::helper('tokenbase')->__( 'Could not find Authorize.Net CIM payment record.' ) );
								}
							}
							else {
								Mage::throwException( Mage::helper('tokenbase')->__( 'Could not find Authorize.Net CIM payment record.' ) );
							}
						}
						else {
							Mage::throwException( Mage::helper('tokenbase')->__( 'Could not find Authorize.Net CIM profile.' ) );
						}
					} // Still not fixed
				}
				catch( Exception $e ) {
					Mage::helper('tokenbase')->log( 'authnetcim', sprintf( 'Failed to convert profile %s: %s', $profile->getReferenceId(), $e->getMessage() ) );
				}
			} // Is legacy
		} // Is CIM
		
		return $this;
	}
}
