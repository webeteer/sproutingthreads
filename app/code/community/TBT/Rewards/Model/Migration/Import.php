<?php

/**
 * WDCA - Sweet Tooth
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL:
 * https://www.sweettoothrewards.com/terms-of-service
 * The Open Software License is available at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * By adding to, editing, or in any way modifying this code, WDCA is
 * not held liable for any inconsistencies or abnormalities in the
 * behaviour of this code.
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the
 * provided Sweet Tooth License.
 * Upon discovery of modified code in the process of support, the Licensee
 * is still held accountable for any and all billable time WDCA spent
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension.
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to
 * support@sweettoothrewards.com or call 1.855.699.9322, so we can send you a copy
 * immediately.
 *
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2014 Sweet Tooth Inc. (http://www.sweettoothrewards.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Rule Rule
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     Sweet Tooth Inc. <support@sweettoothrewards.com>
 */
class TBT_Rewards_Model_Migration_Import extends TBT_Rewards_Model_Migration_Abstract
{
    const DATA_CATALOGRULE_RULE = TBT_Rewards_Model_Migration_Export::DATA_CATALOGRULE_RULE;
    const DATA_SALESRULE_RULE   = TBT_Rewards_Model_Migration_Export::DATA_SALESRULE_RULE;
    const DATA_SPECIAL_RULE     = TBT_Rewards_Model_Migration_Export::DATA_SPECIAL_RULE;
    const DATA_CURRENCY         = TBT_Rewards_Model_Migration_Export::DATA_CURRENCY;
    const DATA_CONFIG           = TBT_Rewards_Model_Migration_Export::DATA_CONFIG;
    const EXT                   = TBT_Rewards_Model_Migration_Export::EXT;

    /**
     * This tells us whether any STR catalog rules were imported so we can notify admin to apply rules.
     *
     * @var boolean
     */
    protected $_catalogRulesImported = false;

    /**
     * Import data from a file.
     *
     * @param  string $filename Temporary import file location.
     * @return self
     */
    public function importFromFile($filename)
    {
        $sinput = file_get_contents($filename);
        return $this->importFromSerializedData($sinput);
    }

    /**
     * Import data from a serialized string of data.
     *
     * @param  string $data Serialized data to import.
     * @return self
     */
    public function importFromSerializedData($data)
    {
        $input = unserialize($data);
        return $this->_importFromData($input);
    }

    /**
     * Imports from an array of data Sweet Tooth rules, configuration settings & currency data. Whatever is present.
     *
     * @param  array $data  Array of data to be imported.
     * @return self
     */
    protected function _importFromData($data)
    {
        if (isset($data[self::DATA_CATALOGRULE_RULE]) && !empty($data[self::DATA_CATALOGRULE_RULE])) {
            $this->_catalogRulesImported = true;
            $this->_importAllCatalogruleRuleData($data [self::DATA_CATALOGRULE_RULE]);
        }
        if (isset($data[self::DATA_SALESRULE_RULE]) && !empty($data[self::DATA_SALESRULE_RULE])) {
            $this->_importAllSalesruleRuleData($data [self::DATA_SALESRULE_RULE]);
        }
        if (isset($data[self::DATA_SPECIAL_RULE]) && !empty($data[self::DATA_SPECIAL_RULE])) {
            $this->_importAllSpecialRuleData($data [self::DATA_SPECIAL_RULE]);
        }
        if (isset($data[self::DATA_CURRENCY]) && !empty($data[self::DATA_CURRENCY])) {
            $this->_importCurrencyData($data [self::DATA_CURRENCY]);
        }
        if (isset($data[self::DATA_CONFIG]) && !empty($data[self::DATA_CONFIG])) {
            $this->_importConfigData($data [self::DATA_CONFIG]);
        }

        return $this;
    }

    /**
     * Import Sweet Tooth catalog rules from an array of data.
     *
     * @param  array $rules_data  Array collection of STR catalog rules.
     * @return self
     */
    protected function _importAllCatalogruleRuleData($rules_data)
    {
        return $this->_importModelData($rules_data, 'rewards/catalogrule_rule');
    }

    /**
     * Import Sweet Tooth cart rules from an array of data.
     *
     * @param  array $rules_data  Array collection of STR cart rules.
     * @return self
     */
    protected function _importAllSalesruleRuleData($rules_data)
    {
        return $this->_importModelData($rules_data, 'rewards/salesrule_rule');
    }

    /**
     * Import Sweet Tooth special rules from an array of data.
     *
     * @param  array $rules_data  Array collection of STR special rules.
     * @return self
     */
    protected function _importAllSpecialRuleData($rules_data)
    {
        return $this->_importModelData($rules_data, 'rewards/special');
    }

    /**
     * Import Sweet Tooth currency from an array of data.
     *
     * @param  array $rules_data  Array collection of STR currencies. STR doesn't support multi-currency, so it should
     *                            be only currency with ID 1.
     * @return self
     */
    protected function _importCurrencyData($curencies_data)
    {
        return $this->_importModelData($curencies_data, 'rewards/currency');
    }

    /**
     * Import specific Sweet Tooth rules from an array.
     *
     * @param  array $models_data   Array collection of Sweet Tooth rules to import.
     * @param  string $model_key    Sweet Tooth rule type to import.
     * @return self
     */
    protected function _importModelData($models_data, $model_key)
    {
        foreach ($models_data as $md) {
            $model = Mage::getModel($model_key);
            if ($model instanceof Mage_Rule_Model_Rule) {
                $model->loadPost($md)
                    ->unserializeActions()
                    ->unserializeConditions();
            } else {
                $model->setData($md);
            }

            $model->saveWithId();
        }

        return $this;
    }

    public function importRewardsConfigData()
    {
        return $this->getConfigData('rewards');
    }

    /**
     * Import Sweet Tooth configuration settings for the current admin configuration scope.
     *
     * @param  array $data  Array of Sweet Tooth configuration data.
     * @return self
     */
    protected function _importConfigData($data)
    {
        $config_table = Mage::getConfig()->getTablePrefix() . "core_config_data";
        $write        = Mage::getSingleton('core/resource')->getConnection('core_write');

        $scopeData = Mage::helper('rewards/migration')->getScopeData($this->getScopeParams());
        foreach ($data as $data_row) {
            if (isset($scopeData['scope']) && isset($scopeData['value'])) {
                // if we have website or store scope only import for that scope
                if ($data_row['scope'] == $scopeData['scope'] && $data_row['scope_id'] == $scopeData['value']) {
                    $select = $write->insert($config_table, $data_row);
                }
            } else {
                // otherwise import for default configuration scope
                $select = $write->insert($config_table, $data_row);
            }
        }

        return $this;
    }

    /**
     * Whether we had catalog rules imported or not.
     *
     * @return bool
     */
    public function getIsCatalogRuleImported()
    {
        return $this->_catalogRulesImported;
    }

    /**
     * Import Sweet Tooth customer points.
     *
     * @param  string $filename Temporary file url.
     * @return self
     */
    public function importPoints($filename)
    {

        /* Local Variables */
        $hasError = false;
        $errorMsg = "";
        $line     = 0;

        /* Store indices of titles on first line of csv file */
        $NUMBER_OF_POINTS_COLUMN_INDEX = -1;
        $CUSTOMER_ID_COLUMN_INDEX      = -1;
        $CUSTOMER_EMAIL_COLUMN_INDEX   = -1;
        $WEBSITE_ID_INDEX              = -1;

        /* Open file handle and read csv file line by line separating comma delaminated values */
        $handle = fopen($filename, "r");

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if ($line == 0) {
                // This is the first line of the csv file. It usually contains titles of columns
                // Next iteration will propagate to "else" statement and increment to line 2 immediately
                $line = 1;

                /* Read in column headers and save indices if they appear */
                $num = count($data);
                for ($index = 0; $index < $num; $index++) {
                    $columnTitle = trim(strtolower($data [$index]));
                    if ($columnTitle === "customer_id") {
                        $CUSTOMER_ID_COLUMN_INDEX = $index;
                    }
                    if ($columnTitle === "points_amount") {
                        $NUMBER_OF_POINTS_COLUMN_INDEX = $index;
                    }
                    if ($columnTitle === "customer_email") {
                        $CUSTOMER_EMAIL_COLUMN_INDEX = $index;
                    }
                    if ($columnTitle === "website_id") {
                        $WEBSITE_ID_INDEX = $index;
                    }
                }

                /* Terminate if no customer identifier column found */
                if ($CUSTOMER_EMAIL_COLUMN_INDEX == -1 && $CUSTOMER_ID_COLUMN_INDEX == -1) {
                    Mage::throwException(
                        Mage::helper('rewards')->__("Error on line") . " " . $line . ": " . Mage::helper('rewards')->__(
                            "No customer identifier in CSV file. Please check the contents of the file."
                        )
                    );
                }

                /* Terminate if no points column found */
                if ($NUMBER_OF_POINTS_COLUMN_INDEX == -1) {
                    Mage::throwException(
                        Mage::helper('rewards')->__("Error on line") . " " . $line . ": " . Mage::helper('rewards')->__(
                            "No identifier for \"points_amount\" in CSV file. Please check the contents of the file."
                        )
                    );
                }
            } else {
                try {
                    $line++;
                    // This handles the rest of the lines of the csv file

                    /* Prepare line data based on values provided */
                    $num = count($data);
                    $num_points = $data [$NUMBER_OF_POINTS_COLUMN_INDEX];
                    $custId = null;
                    $cusEmail = null;
                    $websiteId = null;

                    if ($WEBSITE_ID_INDEX != -1) {
                        $websiteId = array_key_exists($WEBSITE_ID_INDEX, $data) ? $data [$WEBSITE_ID_INDEX] : null;
                    }
                    if ($CUSTOMER_EMAIL_COLUMN_INDEX != -1) {
                        // customer email.
                        $cusEmail = array_key_exists($CUSTOMER_EMAIL_COLUMN_INDEX, $data)
                        ? $data [$CUSTOMER_EMAIL_COLUMN_INDEX]
                        : null;
                    }
                    if ($CUSTOMER_ID_COLUMN_INDEX != -1) {
                        // customer id.
                        $custId = array_key_exists($CUSTOMER_ID_COLUMN_INDEX, $data)
                        ? $data [$CUSTOMER_ID_COLUMN_INDEX]
                        : null;
                    } else {
                        // If no customer_id provided, try finding the id by their email
                        // Customer email is website dependent. Either load deafult website or look at website ID provided in file
                        if ($websiteId == null) {
                            $websiteId = Mage::app()->getDefaultStoreView()->getWebsiteId();
                        } else {
                            $websiteId = Mage::app()->getWebsite($websiteId)->getId();
                        }
                        $custId = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($cusEmail)
                            ->getId();
                        if (empty ($custId)) {
                            $hasError = true;
                            $errorMsg .= "- " . Mage::helper('rewards')->__(
                                    "Error on line"
                                ) . " " . $line . ": " . Mage::helper('rewards')->__(
                                    "Customer with email"
                                ) . " \"" . $cusEmail . "\" " . Mage::helper('rewards')->__(
                                    "was not found in website with id #"
                                ) . $websiteId . ".\n";
                            continue;
                        }
                    }
                    // Make sure customer_id provided is actually valid
                    if (Mage::getModel('customer/customer')->load($custId)->getId() == null) {
                        $hasError = true;
                        $errorMsg .= "- " . Mage::helper('rewards')->__(
                                "Error on line"
                            ) . " " . $line . ": " . Mage::helper('rewards')->__(
                                "Customer with id #"
                            ) . $custId . " " . Mage::helper('rewards')->__("was not found.") . "\n";
                        continue;
                    }

                    /* Start Import */
                    //Load in transfer model
                    $transfer = Mage::getModel('rewards/transfer');

                    //Load it up with information
                    $transfer->setId(null)
                        // in versions of sweet tooth 1.0-1.2 this should be set to "1"
                        ->setCurrencyId(1)
                        // number of points to transfer.  This number can be negative or positive, but not zero
                        ->setQuantity($num_points)
                        ->setCustomerId($custId)// the id of the customer that these points will be going out to
                        ->setReasonId(TBT_Rewards_Model_Transfer_Reason_AdminAdjustment::REASON_TYPE_ID)
                        //This is optional
                        ->setComments(Mage::helper('rewards/config')->getDefaultMassTransferComment())
                        ->setIsPointsImport(1);

                    // Checks to make sure you can actually move the transfer into the new status
                    // STATUS_APPROVED would transfer the points in the approved status to the customer
                    if ($transfer->setStatus(null, TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED)) {
                        $transfer->save(); //Save everything and execute the transfer
                    }

                    // Keep a record in system log
                    Mage::log(
                        "Successfully imported points data on line " . $line . " for following customer:" . "\n\tcustId: " . $custId . "\n\tcusEmail: " . $cusEmail . "\n\twebsiteId: " . $websiteId . "\n\tnum_points: " . $num_points . "\n"
                    );
                } catch (Exception $e) {
                    // Any other errors which happen on each line should be saved and reported at the very end
                    Mage::logException($e);
                    $hasError = true;
                    $errorMsg .= "- " . Mage::helper('rewards')->__(
                            "Error on line"
                        ) . " " . $line . ": " . $e->getMessage() . "\n";
                }
            }
        }

        fclose($handle);
        if ($hasError) {
            // If there were any errors saved, now's the time to report them
            Mage::throwException(
                Mage::helper('rewards')->__("Points were imported with the following errors:") . "\n" . $errorMsg
            );
        }

        return $this;
    }

    ////////////////
    // DEPRECATED //
    ////////////////

    /**
     * @deprecated
     * @see  self::_importFromData()
     */
    public function importFromData($data)
    {
        return $this->_importFromData($data);
    }

    /**
     * @deprecated
     * @see  self::_importAllCatalogruleRuleData()
     */
    public function importAllCatalogruleRuleData($rules_data)
    {
        return $this->_importAllCatalogruleRuleData($rules_data);
    }

    /**
     * @deprecated
     * @see  self::_importAllSalesruleRuleData()
     */
    public function importAllSalesruleRuleData($rules_data)
    {
        return $this->_importAllSalesruleRuleData($rules_data);
    }

    /**
     * @deprecated
     * @see  self::_importAllSpecialRuleData()
     */
    public function importAllSpecialRuleData($rules_data)
    {
        return $this->_importAllSpecialRuleData($rules_data);
    }

    /**
     * @deprecated
     * @see  self::_importCurrencyData()
     */
    public function importCurrencyData($curencies_data)
    {
        return $this->_importCurrencyData($curencies_data);
    }

    /**
     * @deprecated
     * @see  self::_importModelData()
     */
    public function importModelData($models_data, $model_key)
    {
        return $this->_importModelData($models_data, $model_key);
    }
}
