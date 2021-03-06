<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
 
require Mage::getBaseDir('lib').'/authorizenet/autoload.php';
	use net\authorize\api\contract\v1 as AnetAPI;
	use net\authorize\api\controller as AnetController; 
class Amasty_Rma_GuestController extends Mage_Core_Controller_Front_Action
{
    public function loginAction(){
        
        if ($this->_getSession()->isLoggedIn())
            $this->_redirect('*/*/history');
        
        $this->loadLayout();
        $this->_initLayoutMessages('amrma/session');
        $this->renderLayout();
    }
    
    public function loginPostAction()
    {
        $session = $this->_getSession();
        $login = array();
        
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getParam('login');
        } else {
            $login['username'] = $this->getRequest()->getParam('username');
            $login['order'] = $this->getRequest()->getParam('order');
        }
        
        if (!empty($login['username']) && !empty($login['order'])) {
            $this->_login($login);
        } else {
            $session->addError($this->__('Login and password are required.'));
        }
        
        
        if ($session->isLoggedIn()){
            $this->_redirect('*/*/history');
        } else {
            $backUrl = $this->_getRefererUrl();
            $this->_redirectUrl($backUrl);
        }
    }
    
    protected function _login($login){
        $session = $this->_getSession();
        
        try {
            $session->login($login['username'], $login['order']);
        } catch (Mage_Core_Exception $e) {
            $message = $e->getMessage();
            $session->addError($message);
            $session->setUsername($login['username']);
        }
    }
    
    public function logoutAction()
    {
        $this->_getSession()->logout();
        $this->_redirect('*/*/login');
    }
    
    public function historyAction()
    {
        $hlr = Mage::helper("amrma");
        
        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        
        if ($hlr->getRequestsCount($this->_getSession()->getId()) > 0){
            $this->loadLayout();
            $this->_initLayoutMessages('amrma/session');
            $this->getLayout()->getBlock('head')->setTitle($this->__('RMA Order'));
            $this->renderLayout();
        } else {
            if (!$hlr->canCreateRma($this->_getSession()->getId())){
                
                Mage::getSingleton('core/session')->addError($hlr->getFailReason($this->_getSession()->getId()));
                
                $this->loadLayout();
                $this->_initLayoutMessages('amrma/session');
                $this->getLayout()->getBlock('head')->setTitle($this->__('RMA Order'));
                $this->renderLayout();

            } else {
                $this->_redirect('*/*/new', array('order_id' => $this->_getSession()->getId()));
            }
            
        }
    }
    
    public function viewAction()
    {
        $id    = (int)$this->getRequest()->getParam('id');
        
        if (!$this->_loadValidRequest($id)) {
            $this->_redirect('*/*/history');
            return;
        }
        
        $order = Mage::getModel('sales/order')->load(
            Mage::registry('amrma_request')->getOrderId()
        );
        
        Mage::register('amrma_order', $order);
        
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My RMA'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        
        $this->renderLayout();
    }
    
    public function editAction()
    {
        $this->_forward('form');
    }
    
    public function newAction()
    {
        $orderId    = (int)$this->getRequest()->getParam('order_id');

        $order = Mage::getModel('sales/order')->load($orderId);
        
        $hlr = Mage::helper("amrma");
		
		$params=Mage::app()->getRequest()->getParams();
		 
		$payment = $order->getPayment();
		
	 
		
		//$authorize = Mage::getModel('authnetcim/gateway');
      //  $resutlt=$authorize->capture($payment,10);
		//print_r($result);
        if ($this->_canViewOrder($order) && $hlr->canCreateRma($orderId)){
            
            $post = $this->getRequest()->getPost();
            
            if (($post)) {
				
				

                $pending = Amasty_Rma_Model_Status::getPendingStatus();
                
                $request = Mage::getModel('amrma/request');
                
                $request->setData(array(
                    'order_id' => $order->getId(),
                    'increment_id' => $order->getIncrementId(),
                    'store_id' => $order->getStoreId(),
                    'customer_id' => $order->getCustomerId(),
                    'email' => $order->getCustomerEmail(),
                    'customer_firstname' => $order->getCustomerFirstname(),
                    'customer_lastname' => $order->getCustomerLastname(),
                    'code' => uniqid(),
                    'status_id' => $pending->getId(),
                    'created' => Mage::getSingleton('core/date')->gmtDate(),
                    'updated' => Mage::getSingleton('core/date')->gmtDate(),
                    'items' => Mage::app()->getRequest()->getParam('items', array()),
                    'comment' => Mage::app()->getRequest()->getParam('comment', ''),
                    'field_1' => Mage::app()->getRequest()->getParam('field_1'),
                    'field_2' => Mage::app()->getRequest()->getParam('field_2'),
                    'field_3' => Mage::app()->getRequest()->getParam('field_3'),
                    'field_4' => Mage::app()->getRequest()->getParam('field_4'),
                    'field_5' => Mage::app()->getRequest()->getParam('field_5')
                )); 
                
                $request->save();
                $comment = $request->submitComment(FALSE, $_FILES['file']);
                $request->saveRmaItems();
                
                $request->sendNotificaitionRmaCreated($comment);
                
                $this->_forward('history');

            } else {
                $this->_forward('form');
            }
        } else {
			
			$this->_forward('form');
            //$error = $hlr->getFailReason($orderId);
            
            //Mage::getSingleton('core/session')->addError($error);
            
           // $this->_redirect('*/*/history');
        }
    }
	
	
	public function summaryAction()
	{
		
		$params=Mage::app()->getRequest()->getParams();
		 
		$orderId    = (int)$params['order_id']; 
		$orderdetails=Mage::getModel('sales/order')->load($orderId);
		$orderdetails=$orderdetails->getData();  
		 $cards=Mage::helper('tokenbase')->getActiveCustomerCardsByMethod('authnetcim');
		echo ' 
                <div class="column twelve cstep3 hidesteps"  id="step3">
				<form action="'.Mage::getUrl('amrmafront/customer/create/').'" id="submitcheckout">
				<input type="hidden" name="order_id" value="'.$orderId.'">
				';
				
                echo	'<div class="success-inner">
                		<div class="column nine"><h1>order summary</h1></div><div class="column three green_feed"><a href="#"  onClick="nextStep()">Show Feedback &gt;&gt;</a></div>'; 
						 
						 $returning=false;
						 $keeping=false;
						 $total=0;
						 foreach($params['items'] as $item_id=>$item)
						 {
							 
							 if($item['order_item_id']!='no')
							 {
								 
								 
								 $total+=$item['price'];
								 $keeping=true;
								 $keepingHTML.='
								 
	<input type="hidden" name="items['.$item_id.'][order_item_id]" value="'.$item_id.'">
	<input type="hidden" name="items['.$item_id.'][reason][size]" value="'.$item['reason']['size'].'">
	<input type="hidden" name="items['.$item_id.'][reason][price]" value="'.$item['reason']['price'].'">
	<input type="hidden" name="items['.$item_id.'][reason][quality]" value="'.$item['reason']['quality'].'">
	<input type="hidden" name="items['.$item_id.'][reason][comments]" value="'.$item['reason']['comments'].'">
	<input type="hidden" name="items['.$item_id.'][reason][style]" value="'.$item['reason']['style'].'">
	<input type="hidden" name="items['.$item_id.'][reason][fit]" value="'.$item['reason']['fit'].'">
	<input type="hidden" name="items['.$item_id.'][resolution]" value="None"> 
	<input type="hidden" name="items['.$item_id.'][condition]" value="Keep It"> 
	<input type="hidden" name="items['.$item_id.'][qty_requested]" value="0"> 
								 
                                 <div class="column nine order_des"><span>'.$item['sku'].'</span>
                                 <br>'.$item['name'].'</div>
                                  <div class="column three list_price">'.Mage::helper('core')->currency($item['price'], true, false).'</div>
                                  <br>';
							 }
							 else
							 {
								 $returning=true;
							 }
						 }
						 
						 if($keeping){
						 echo '<div class="column twelve order_head"><h4>keeping</h4></div>';
						 echo $keepingHTML;
						 }
						 if($returning){
						echo '  
                        <div class="column twelve order_head"><h4>returning</h4></div>';
                         
						 foreach($params['items'] as $item_id=>$item)
						 {
							 if($item['order_item_id']=='no')
							 {
								 echo '
								 <input type="hidden" name="items['.$item_id.'][order_item_id]" value="'.$item_id.'">
	<input type="hidden" name="items['.$item_id.'][reason][size]" value="'.$item['reason']['size'].'">
	<input type="hidden" name="items['.$item_id.'][reason][price]" value="'.$item['reason']['price'].'">
	<input type="hidden" name="items['.$item_id.'][reason][quality]" value="'.$item['reason']['quality'].'">
	<input type="hidden" name="items['.$item_id.'][reason][comments]" value="'.$item['reason']['comments'].'">
	<input type="hidden" name="items['.$item_id.'][reason][style]" value="'.$item['reason']['style'].'">
	<input type="hidden" name="items['.$item_id.'][reason][fit]" value="'.$item['reason']['fit'].'">
	<input type="hidden" name="items['.$item_id.'][resolution]" value="Refund"> 
	<input type="hidden" name="items['.$item_id.'][condition]" value="Return It">
	<input type="hidden" name="items['.$item_id.'][qty_requested]" value="1">  
                                  <div class="column nine order_des"><span>'.$item['sku'].'</span>
                                  <br>'.$item['name'].'</div>
                                  <div class="column three list_price"><del>'.Mage::helper('core')->currency($item['price'], true, false).'</del></div>
                                  <br>
                                ';
							 }
						 }
						 }
						 
				
						 
						echo '
                        <hr>
                        
                        <div class="column twelve order_price">
                          <ul>
                           <li>Subtotal</li><li>'.Mage::helper('core')->currency($total).'<input type="hidden" name="linetotal" value="'.$total.'"></li> 
						   '; 
						   $allgift_voucher_discount=$orderdetails['gift_voucher_discount']+$orderdetails['giftcard_credit_amount'];
						   if($allgift_voucher_discount>0)
						   {
							   $giftcard=$allgift_voucher_discount;
							   $total=$total-$giftcard; 
							   $totalrefund=$giftcard;
							   echo '<li>Gift Card</li>
							   <li>-'.Mage::helper('core')->currency($giftcard).'
							   <input type="hidden" name="giftcard" value="'.$giftcard.'"> 
							   ' ;
							   if($orderdetails['gift_codes']){
							   echo '<br><span>('.$orderdetails['gift_codes'].')</span>';
							   }
							   echo '</li>';
						   }
						   
						   if(abs($orderdetails['discount_amount'])>0)
						   {
							   $dis=abs($orderdetails['discount_amount']);
							   $total=$total-$dis; 
							   //$totalrefund=$totalrefund+$dis;
							   echo '<li>Discount Amount</li>
							   <li>-'.Mage::helper('core')->currency($dis).'
							   <input type="hidden" name="dis" value="'.$dis.'">  
							   
							   </li>';
						   }
						   if(!$returning)
						   {
							   
							   $alldiscount=$total*10/100;
							   $total=$total-$alldiscount; 
							   echo '<li>(10%)</li>
							   <li>-'.Mage::helper('core')->currency($alldiscount).'
							   <input type="hidden" name="returningg" value="'.$alldiscount.'">  
							   <br><span>(Applied when<br>keeping all items)</span>
							   </li>';
						   }
						   if($total>60)
						   {
							     $total=$total-20; 
							     echo '<li>Styling Fee</li><li>-$20.00<br><span>(Applied when<br>
keeping &gt;$60)</span>
 <input type="hidden" name="totalg" value="20"> 
</li> '; 
							   
							  
						   }
						  $refund=$total;
						   if($total<=0)
						   {
							   $refund=$total;
							   $total=0;
						   }
                           echo ' 
                           
                         <!--  <li>Discount</li><li>-$ ----------</li>
                           <li>ReThread/giftcard</li><li>-$ ----------</li>
                           <li>Rewards</li><li>-$ ----------</li>-->
                           <li class="last">Total</li><li class="last">
						    <input type="hidden" name="alltotalcalc" value="'.$refund.'">
							 <input type="hidden" name="subtotal" value="'.$total.'">   '.Mage::helper('core')->currency($total).'</li>
                          </ul>
						   
                        </div>
						';
						if($total>0){
                        echo '<div class="column twelve card_form">
						  
						  ';
					
					?>
	<?php $requireClass	= ( $this->haveStoredCards() ? $this->getMethodCode() . '_require' : 'required-entry' ); ?>
	<?php $newClass		= $this->getMethodCode() . '_new'; ?>
	<ul class="form-list" id="payment_form_<?php echo $this->getMethodCode(); ?>" >
	 
	<?php if( $this->haveStoredCards() ): ?>
		<li>
			<label for="<?php echo $this->getMethodCode(); ?>_payment_id"><?php echo $this->__('Pay with credit card on file'); ?></label>
			<div class="input-box">
				<select name="payment[card_id]" id="<?php echo $this->getMethodCode(); ?>_card_id" class="<?php echo $this->getMethodCode(); ?>_require required-entry">
					<option value=""><?php echo $this->__('-- Select One --'); ?></option>
					<?php foreach( $this->getStoredCards() as $card ): ?>
						<?php $card = $card->getTypeInstance(); ?>
						<option value="<?php echo $card->getHash(); ?>" <?php if(count( $this->getStoredCards() ) == 1 ): ?>selected="selected"<?php endif; ?>><?php echo $card->getLabel(); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</li> 
		<li>
			<div class="input-box">
				<?php echo $this->__('Or,'); ?> <a href="#" id="<?php echo $this->getMethodCode(); ?>_add_new"><?php echo $this->__('use a different card.'); ?></a>
			</div>
		</li>
	<?php endif; ?>
	<li <?php if( $this->haveStoredCards() ): ?>style="display:none"<?php endif; ?> class="<?php echo $newClass; ?>">
    <label for="<?php echo $this->getMethodCode(); ?>_payment_id"><?php echo $this->__('Pay with credit card'); ?></label>
	  <label for="<?php echo $this->getMethodCode(); ?>_cc_type" class="required"><em>*</em><?php echo $this->__('Credit Card Type') ?></label>
		<div class="input-box">
			<select id="<?php echo $this->getMethodCode(); ?>_cc_type" name="payment[cc_type]" class="<?php echo $requireClass; ?>">
				<option value=""><?php echo $this->__('-- Select One --')?></option>
				<?php foreach( Mage::helper('tokenbase')->getCcAvailableTypes($this->getMethodCode()) as $k => $v ): ?>
					<option value="<?php echo $k; ?>" ><?php echo $v; ?></option>
				<?php endforeach ?>
			</select>
		</div>
	</li>
	<li <?php if( $this->haveStoredCards() ): ?>style="display:none"<?php endif; ?> class="<?php echo $newClass; ?>">
		<label for="<?php echo $this->getMethodCode(); ?>_cc_number" class="required"><em>*</em><?php echo $this->__('Credit Card Number') ?></label>
		<div class="input-box">
			<input type="text" id="<?php echo $this->getMethodCode(); ?>_cc_number" name="payment[cc_number]" title="<?php echo $this->__('Credit Card Number') ?>" class="input-text <?php echo $requireClass; ?> validate-cc-number" autocomplete="off" value="" />
		</div>
	</li>
	<li <?php if( $this->haveStoredCards() ): ?>style="display:none"<?php endif; ?> class="<?php echo $newClass; ?>" id="<?php echo $this->getMethodCode(); ?>_cc_type_exp_div">
		<label for="<?php echo $this->getMethodCode(); ?>_expiration" class="required"><em>*</em><?php echo $this->__('Expiration Date') ?></label>
		<div class="input-box">
			<div class="v-fix">
				<select id="<?php echo $this->getMethodCode(); ?>_expiration" name="payment[cc_exp_month]" class="month <?php echo $requireClass; ?>">
					<?php foreach( Mage::helper('tokenbase')->getCcMonths() as $k => $v ): ?>
						<option value="<?php echo $k?$k:''; ?>"  ><?php echo $v; ?></option>
					<?php endforeach ?>
				</select>
			</div>
			<div class="v-fix">
				<select id="<?php echo $this->getMethodCode(); ?>_expiration_yr" name="payment[cc_exp_year]" class="year <?php echo $requireClass; ?>">
					<?php foreach( Mage::helper('tokenbase')->getCcYears() as $k => $v ): ?>
						<option value="<?php echo $k?$k:''; ?>"  ><?php echo $v; ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
	</li> 
	 
		<li <?php if( $this->haveStoredCards() ): ?>style="display:none"<?php endif; ?> class="<?php echo $newClass; ?>" id="<?php echo $this->getMethodCode(); ?>_cc_type_cvv_div">
			<label for="<?php echo $this->getMethodCode(); ?>_cc_cid" class="required"><em>*</em><?php echo $this->__('Card Verification Number') ?></label>
			<div class="input-box">
				<div class="v-fix">
					<input type="text" title="<?php echo $this->__('Card Verification Number') ?>" class="input-text cvv <?php echo $requireClass; ?> <?php if( !$this->haveStoredCards() ): ?>validate-cc-cvn<?php endif; ?>" id="<?php echo $this->getMethodCode(); ?>_cc_cid" name="payment[cc_cid]" autocomplete="off" value="" maxlength="4" />
				</div> 
			</div>
		</li>
	 
	 
		<li <?php if( $this->haveStoredCards() ): ?>style="display:none"<?php endif; ?> class="<?php echo $newClass; ?>">
			<?php echo $this->__('<strong>Note:</strong> For your convenience, this data will be stored securely by our payment processor.'); ?>
		</li>
 
</ul>
<?php if( $this->haveStoredCards() ): ?>
	<script type="text/javascript">
	//<![CDATA[
		$('<?php echo $this->getMethodCode(); ?>_add_new').observe( 'click', function(e) {
			e.preventDefault();
			
			$$('.<?php echo $newClass; ?>').each(function(el) {
				el.show();
			});
			
			$$('.<?php echo $this->getMethodCode(); ?>_require').each(function(el) {
				el.toggleClassName('required-entry');
				if( el.hasClassName('cvv') ) {
					el.toggleClassName('validate-cc-cvn');
				}
			});
			
			$('<?php echo $this->getMethodCode(); ?>_card_id').setValue(0);
			
			return false;
		});
		
		$('<?php echo $this->getMethodCode(); ?>_card_id').observe( 'change', function(e) {
			$$('.<?php echo $newClass; ?>').each(function(el) {
				el.hide();
			});
			
			$$('.<?php echo $newClass; ?> .<?php echo $this->getMethodCode(); ?>_require').each(function(el) {
				el.removeClassName('required-entry');
				if( el.hasClassName('cvv') ) {
					el.removeClassName('validate-cc-cvn');
				}
			});
			
			$$('#<?php echo $this->getMethodCode(); ?>_saved_cc_cid, #<?php echo $this->getMethodCode(); ?>_card_id').each(function(el) {
				el.addClassName('required-entry');
				if( el.hasClassName('cvv') ) {
					el.addClassName('validate-cc-cvn');
				}
			});
			
			return false;
		});
	//]]>
	</script>
<?php endif; ?>
                 <?php
					
					
				echo '
						  
                        </div>';
						}
                                	
                        echo' <div class="row selfcheckout">
                       		 <div class="column twelve">
                            	<input type="button" value="submit" onClick="submitCheckout()"  name="checkout_submit"><br>
								<p>Clicking submit will automatically credit or debit the card we have on file. <br>Gift Card Credits will be automatically adjusted & placed into your balance<br> history in myAccount</p>
								<div id="loader1" class="fl hidesteps"><img src="'.Mage::getDesign()->getSkinUrl('images/ajax-loader.gif').'" width="30"></div>
								<div style="color:red;"><span id="msg4res"></span></div>
                            </div>
                        </div>       
                    </div>
					
					</form>	  
                   
						 
                </div>';
		
				 
			exit;
	}
	public function getCcAvailableTypes( $method )
	{
		$config	= Mage::getConfig()->getNode('global/payment/cc/types')->asArray();
		$avail	= explode( ',', Mage::helper('payment')->getMethodInstance( $method )->getConfigData('cctypes') );
		
		$types	= array();
		foreach( $config as $data ) {
			if( in_array( $data['code'], $avail ) !== false ) {
				$types[ $data['code'] ] = $data['name'];
			}
		}
		
		return $types;
	}
	public function getMethodCode()
	{
		return 'authnetcim';
	}
	public function getStoredCards()
	{
		if( is_null( $this->_cards ) ) {
			/**
			 * If logged in, fetch the method cards for the current customer.
			 * If not, short circuit / return empty array.
			 */
			$customer = Mage::helper('tokenbase')->getCurrentCustomer();
			
			if( Mage::app()->getStore()->isAdmin() || $customer && $customer->getId() > 0 ) {
				$this->_cards = Mage::helper('tokenbase')->getActiveCustomerCardsByMethod( $this->getMethodCode() );
			}
			else {
				$this->_cards = array();
			}
		}
		
		return $this->_cards;
	}
	
	/**
	 * Check whether we have any cards stored.
	 */
	public function haveStoredCards()
	{
		$cards = $this->getStoredCards();
		return false;
		return ( count( $cards ) > 0 ? true : false );
	}
	public function getCardsByCustomer( $customerId )
	{
		$cards		= Mage::getModel('tokenbase/card')->getCollection()
						->addFieldToFilter( 'customer_id', (int)$customerId )
						->addFieldToFilter( 'active', 1 );
		
		$results	= array();
		foreach( $cards as $card ) {
			$results[] = $this->_prepareCard( $card );
		}
		
		return $results;
	}
	protected function _prepareCard( ParadoxLabs_TokenBase_Model_Card $card )
	{
		$card		= $card->getTypeInstance();
		$address	= $card->getAddress();
		
		/**
		 * Basic payment record data
		 */
		$result		= array();
		foreach( $this->_cardMap as $key ) {
			$result[ $key ]		= $card->getData( $key );
		}
		
		/**
		 * Address data
		 */
		$result['address']		= array();
		foreach( $this->_addrMap as $key ) {
			$result['address'][ $key ] = $address[ $key ];
		}
		
		/**
		 * Additional (common) information
		 */
		$result['label']		= $card->getLabel();
		$result['cc_type']		= $card->getAdditional('cc_type');
		$result['cc_last4']		= $card->getAdditional('cc_last4');
		
		return $result;
	}
	
     public function createAction()
    {
		 
		$orderId    = (int)$this->getRequest()->getParam('order_id'); 
		$params=Mage::app()->getRequest()->getParams();
		
		$order = Mage::getModel("sales/order")->load($orderId);
		$totalg=Mage::app()->getRequest()->getParam('totalg');
		$returningg=Mage::app()->getRequest()->getParam('returningg');
		$giftcard=Mage::app()->getRequest()->getParam('giftcard');
		$dis=Mage::app()->getRequest()->getParam('dis');
		$alltotal=Mage::app()->getRequest()->getParam('subtotal');
		$totaldiscount=$totalg+$returningg;
		 // Common setup for API credentials
      $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
      $merchantAuthentication->setName(Mage::getStoreConfig('payment/authnetcim/login'));
      $merchantAuthentication->setTransactionKey(Mage::getStoreConfig('payment/authnetcim/trans_key'));
      $refId = $lastOrderId = $order->getIncrementId(); 
	 
      // Create the payment data for a credit card
      $creditCard = new AnetAPI\CreditCardType();
      $creditCard->setCardNumber($params['payment']['cc_number']);
      $creditCard->setExpirationDate(str_pad($params['payment']['cc_exp_month'],2,0,STR_PAD_LEFT).$params['payment']['cc_exp_year']);
      $creditCard->setCardCode($params['payment']['cc_cid']);
      $paymentOne = new AnetAPI\PaymentType();
      $paymentOne->setCreditCard($creditCard); 
        
		if($totalg!='')
		{
		 $order->addStatusHistoryComment(
				'Discount Applied when keeping &gt;$60 - $' . Mage::app()->getRequest()->getParam('totalg') . ' ', false
			);
		}
		if($returningg!='')
		{
		 $order->addStatusHistoryComment(
				'Discount Applied when keeping all items - $' . Mage::app()->getRequest()->getParam('returningg') . ' ', false
			);
		}
		//apply to order discount
		$order->setBaseDiscountAmount($totaldiscount);
		$order->setDiscountAmount($totaldiscount); 
		//apply to order grand total
		$order->setBaseGrandTotal($order->getBaseGrandTotal() - $totaldiscount);
		$order->setGrandTotal($order->getGrandTotal() - $totaldiscount); 
		//and finally save your order
		$order->save();
		$totaldiscount=$totalg+$returningg+$giftcard+$dis;
		$items=Mage::app()->getRequest()->getParam('items', array());
		foreach($items as $key=>$oit)
		{
			if($oit['condition']=='Return It')
			{
				$returns[]=$oit['order_item_id'];
			}
			else
			{
				$keeping[]=$oit['order_item_id'];
			}
		} 
		
  try {
		 
		$orderItem = $order->getItemsCollection();
		foreach($orderItem as $itms)
		{
			 
			if(in_array($itms->getId(),$keeping))
			{
			 
				$qtys[$itms->getId()]=1;
			}
			if(in_array($itms->getId(),$returns))
			{
			 
				$qrtys[$itms->getId()]=1;
			}
			
		}  
		 
		 foreach ($order->getInvoiceCollection() as $invoice) {
                        $invoiceIncId[] = $invoice->getIncrementId();
                 }
		if(count($invoiceIncId)==0){
			
		$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::NOT_CAPTURE);
		$invoice->register();
		$transactionSave = Mage::getModel('core/resource_transaction')
		->addObject($invoice)
		->addObject($invoice->getOrder()); 
		$transactionSave->save();
		$order = Mage::getModel("sales/order")->load($orderId);
		$service = Mage::getModel('sales/service_order', $order);
		$data = array(
			'qtys' => $qrtys
		); 
		}
		 
		foreach ($order->getInvoiceCollection() as $invoice) {
                        $invoiceIncId[] = $invoice->getIncrementId();
                 }
		
				 
		$totaltoinvoice=$invoice->getSubtotal()-$totaldiscount;		 
			if($totaltoinvoice<=0)
				 {
					$totaltoinvoice=0; 
				 }
		  	 
		$incrementId = $invoiceIncId[0];
		
		if($alltotal>0){		
	//echo Mage::helper('core')->decrypt('bc7cf09f4e440bf00f10219439684c2dfdc4e6e9');
		 //create a transaction
	  $selforder = new AnetAPI\OrderType();
      $selforder->setDescription("Selfcheckout for order#".$refId);
      $transactionRequestType = new AnetAPI\TransactionRequestType();
      $transactionRequestType->setTransactionType( "authCaptureTransaction"); 
      $transactionRequestType->setAmount($alltotal);
      $transactionRequestType->setOrder($selforder);
      $transactionRequestType->setPayment($paymentOne); 

      $request = new AnetAPI\CreateTransactionRequest();
      $request->setMerchantAuthentication($merchantAuthentication);
      $request->setRefId( $refId);
      $request->setTransactionRequest( $transactionRequestType);
      $controller = new AnetController\CreateTransactionController($request);
      $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION);
      $flag=false;
	  if ($response != null)
      {
        $tresponse = $response->getTransactionResponse(); 
        if (($tresponse != null) && ($tresponse->getResponseCode()== 1) )   
        {
			
			$order->addStatusHistoryComment('Captured amount of $'.$alltotal.'. Transaction ID: "'.$tresponse->getTransId().'".'); 
			$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE);
			$order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
			$order->save(); 
			$flag=true;
         
        }
        else
        {
			$flag=false; 
			
            echo  "This transaction has been declined\n";
			exit;
        }
        
      }
      else
      {
		   $flag=false; 
           echo  "Charge Credit card Null response returned";
			exit;
       
      }
		}
		else
		{
			$order->addStatusHistoryComment('Balance was $'.$alltotal.'.'); 
			$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE);
			$order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
			$order->save(); 
			$flag=true;
		}
       if($flag)
	   {
		
		$invoiveF = Mage::getModel('sales/order_invoice')->loadByIncrementId($incrementId); 
		$invoiveF->setGrandTotal($alltotal);
        $invoiveF->setBaseGrandTotal($alltotal);
		$invoiveF->save();
		$totaldiscount=$totalg+$returningg+$giftcard+$dis; 
		$__order = Mage::getModel("sales/order")->load($orderId);
		
		
		$giftcard=Mage::app()->getRequest()->getParam('giftcard');
		$linetotal=Mage::app()->getRequest()->getParam('linetotal');
		
		$alltotalcalc=Mage::app()->getRequest()->getParam('alltotalcalc');
		if($alltotalcalc<0)
		{ 
			$refundAmount = 0; 
			$alltotalca=$linetotal-$giftcard;
			$refundAmount=abs($alltotalcalc);
			$customer = Mage::getModel('customer/customer')->load($__order->getCustomerId());
			$credit = Mage::getModel('giftvoucher/credit')->load($customer->getId(), 'customer_id');
                if (!$credit->getId()) {
                    $credit->setCustomerId($customer->getId())
                            ->setCurrency($__order->getBaseCurrencyCode())
                            ->setBalance(0);
                }
               
				$creditBalance = $refundAmount;
				 try {
                        $credit->setBalance($credit->getBalance() + $creditBalance)
                                ->save(); 
						$currency_balance = round($credit->getBalance(), 4); 
                        $credithistory = Mage::getModel('giftvoucher/credithistory')->setData($credit->getData());
                        $credithistory->addData(array(
                            'action' => 'Refund',
                            'currency_balance' => $currency_balance,
                            'order_id' => $__order->getId(),
                            'order_number' => $__order->getIncrementId(),
                            'balance_change' => $creditBalance,
                            'created_date' => date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())),
                            'currency' => $__order->getOrderCurrencyCode(),
                            'base_amount' => $creditBalance,
                            'amount' => $creditBalance
                        ))->setId(null)->save();
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
		}
		
		
		$__order->setGrandTotal($alltotal); 
		$__order->setBaseGrandTotal($alltotal); 
		//and finally save your order
		$__order->save();
		
		
		
		/*$invoiveG = Mage::getModel('sales/order_invoice')->loadByIncrementId($incrementId);
		$invoiveG->capture()->save(); 
		$transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoiveG)
            ->addObject($invoiveG->getOrder());
		$transactionSave->save();*/
		 
		
		$order = Mage::getModel('sales/order')->load($orderId); 
        $hlr = Mage::helper("amrma"); 
		
		$post = $this->getRequest()->getPost(); 
        if ($this->_canViewOrder($order) && $hlr->canCreateRma($orderId)){ 
            $post = $this->getRequest()->getPost(); 
            if (($post)) { 
                $pending = Amasty_Rma_Model_Status::getPendingStatus();
                
                $request = Mage::getModel('amrma/request');
                
                $request->setData(array(
                    'order_id' => $order->getId(),
                    'increment_id' => $order->getIncrementId(),
                    'store_id' => $order->getStoreId(),
                    'customer_id' => $order->getCustomerId(),
                    'email' => $order->getCustomerEmail(),
                    'customer_firstname' => $order->getCustomerFirstname(),
                    'customer_lastname' => $order->getCustomerLastname(),
                    'code' => uniqid(),
                    'status_id' => $pending->getId(),
                    'created' => Mage::getSingleton('core/date')->gmtDate(),
                    'updated' => Mage::getSingleton('core/date')->gmtDate(),
                    'items' => Mage::app()->getRequest()->getParam('items', array()),
                    'comment' => Mage::app()->getRequest()->getParam('comment', ''),
                    'field_1' => Mage::app()->getRequest()->getParam('field_1'),
                    'field_2' => Mage::app()->getRequest()->getParam('field_2'),
                    'field_3' => Mage::app()->getRequest()->getParam('field_3'),
                    'field_4' => Mage::app()->getRequest()->getParam('field_4'),
                    'field_5' => Mage::app()->getRequest()->getParam('field_5')
                )); 
                
                $request->save();
                $comment = $request->submitComment(FALSE, $_FILES['file']);
                $request->saveRmaItems();
                
                $request->sendNotificaitionRmaCreated($comment);
               
				 
                //$this->_forward('history');

            } else {
				//echo 'nopost';
                //$this->_forward('form');
            }
			
					echo 'ok';
        } 
		
		else {
			echo 'Your request cannot be processed at this time,Please contact customer support'; 
        }
	   }
	   
		
		}
		catch (Mage_Core_Exception $e) {
	 
		}
		
		
       
    }
	
    public function deleteAction()
    {
        $id    = (int)$this->getRequest()->getParam('id');
        
        if ($this->_loadValidRequest($id)) {
            $request = Mage::registry('amrma_request');
            $request->delete();
        } 
        
        $this->_redirect('*/*/history');
    }
    
    public function formAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('amrma/customer/history');
        }
        $this->renderLayout();
    }
    
    protected function _getSession()
    {
        return Mage::getSingleton('amrma/session');
    }
    
    public function downloadAction()
    {
        $fileName = $this->getRequest()->getParam('file');
        
        $download = Amasty_Rma_Model_File::getUploadPath($fileName);

        if (is_writable($download)){
            $this->_prepareDownloadResponse($fileName, file_get_contents($download));
        } else {
            Mage::throwException('Unable read file');
        }

    }
    
    public function addCommentAction()
    {
        $id    = (int)$this->getRequest()->getParam('id');
        
        if (!$this->_loadValidRequest($id)) {
            $this->_redirect('*/*/history');
            return;
        }
        
        $model = Mage::registry('amrma_request');
        
        $data = $this->getRequest()->getPost();
        
        if ($data) {
	
            $model->setComment($data['comment']);
            
            try {
                
                $comment = $model->submitComment(FALSE, $_FILES['file']);
                
                $model->sendNotificaition2admin($comment);
                
                $model->setUpdated(Mage::getSingleton('core/date')->gmtDate());
                
                $model->save();
                
                $msg = Mage::helper('amrma')->__('Comment placed');
                
                Mage::getSingleton('core/session')->addSuccess($msg);

            } 
            catch (Exception $e) {
                
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }	
            
            $this->_redirectReferer();
            return;
        }
    }
    
    protected function _canViewRequest($request)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        
        if ($request->getId() && $request->getCustomerId() && ($request->getCustomerId() == $customerId)) {
            return true;
        }
        
        $amrma = $this->_getSession();
        
        if ($amrma){ //guest validation
            $salesOrder = Mage::getModel('sales/order')->load($amrma->getId());
            return $request->getEmail() == $salesOrder->getCustomerEmail();
        }
        return false;
    }
    
    protected function _canViewOrder($order)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        
        if ($order->getId() && $order->getCustomerId() && ($order->getCustomerId() == $customerId)) {
            return true;
        }
        
        $session = $this->_getSession();
        
        if ($session){
            $sessionOrder = Mage::getModel('sales/order')->load($session->getId());
            
            return $order->getCustomerEmail() == $sessionOrder->getCustomerEmail();
        }
        return false;
    }

    protected function _loadValidRequest($entityId = null)
    {
        $request = Mage::getModel('amrma/request')->load($entityId);
        
        if ($this->_canViewRequest($request)) {
            Mage::register('amrma_request', $request);
            return true;
        } else {
            $amrma = $this->_getSession();
        
            if ($amrma){
                $this->_redirect('*/*/history');
            } else {
                $this->_redirect('*/*/login');
            }
        }
        return false;
    }
    
    
    public function exportAction(){
        $id    = (int)$this->getRequest()->getParam('id');
        $code = $this->getRequest()->getParam('code');
        
        if ($code){
           $request = Mage::getModel('amrma/request')->load($code, "code");
           Mage::register('amrma_request', $request); 
        } else {
            if (!$this->_loadValidRequest($id)) {
                $this->_redirect('*/*/history');
                return;
            }

            $_request = Mage::registry('amrma_request');

            if (!Mage::helper('amrma')->getIsAllowPrintLabel() ||
                    !$_request->allowPrintLabel()){
                throw Mage::exception('Mage_Core', Mage::helper('amrma')->__('Access denied.'));
            }
        }      
        $this->loadLayout();    
        $this->renderLayout();
    }
    
    
    public function confirmAction(){
        $id    = (int)$this->getRequest()->getParam('id');
        
        if (!$this->_loadValidRequest($id)) {
            $this->_redirect('*/*/history');
            return;
        }
        
        $_request = Mage::registry('amrma_request');
        
        if (!$_request->allowPrintLabel()){
            $this->_redirect('*/*/history');
            return;
        } else {
            $_request->setIsShipped(TRUE);
            $_request->save();
            
            $msg = Mage::helper('amrma')->__('Shipping confirmed');
                
            Mage::getSingleton('core/session')->addSuccess($msg);
                
            $this->_redirect('*/*/view', array("id" => $id));
        }
    }
    
    public function commentLookupAction(){
        $key = $this->getRequest()->getParam('key');
        $hlr = Mage::helper('amrma');
        $comment = Mage::getModel('amrma/comment')->load($key, 'unique_key');
        $session = $this->_getSession();
        
        if ($comment){
            
            try {
                $session->loginByComment($comment);

            } catch (Mage_Core_Exception $e) {
                $message = $e->getMessage();
                
                $session->addError($message);
            }
        } else {
            $session->addError($hlr->__("Wrong key"));
        }
        
        if ($session->isLoggedIn()){
            $url = Mage::getUrl('*/*/view', array(
                'id' => $comment->getRequestId(),
            ));
            $url .= '#comment_'.$comment->getId();
            
            $this->_redirectUrl($url);
        } else {
            $this->_redirect('amrma/guest/login');
        }   
    }
}