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

try {
	/**
	 * Use filesystem locking to avoid duplication.
	 */
	$processLock = Mage::getModel('index/process');
	$processLock->setId('authnetcim_setup');
	
	if( !$processLock->isLocked() ) {	
		$processLock->lockAndBlock();
		
		$this->startSetup();
		
		$table = $this->getTable('authnetcim/card');
		
		$this->run("CREATE TABLE IF NOT EXISTS {$table} (
			id int auto_increment primary key,
			customer_id int,
			profile_id int,
			payment_id int,
			added varchar(255)
		);");
		
		$this->endSetup();
		
		$processLock->unlock();
	}
}
catch( Exception $e ) {
	Mage::log( 'SETUP FAILURE authnetcim_setup:mysql4-upgrade-1.2.0-1.3.0: ' . (string)$e, null, 'authnetcim.log', true );
	
	if( isset( $processLock ) && $processLock instanceof Mage_Index_Model_Process ) {
		$processLock->unlock();
	}
	
	Mage::throwException( $e->getMessage() );
}
