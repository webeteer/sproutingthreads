<?php

class TBT_Milestone_Model_Rule_Condition_Referrals_Reference extends TBT_Rewards_Model_Transfer_Reference_Abstract
 {
    const REFERENCE_TYPE_ID = 604;
    const REFERENCE_KEY     = 'referrals_milestone';

    public function clearReferences(&$transfer)
    {
        if ($transfer->hasData(self::REFERENCE_KEY)) {
            $transfer->unsetData(self::REFERENCE_KEY);
        }

        return $this;
    }

    public function getReferenceOptions()
    {
        $referenceOptions = array(self::REFERENCE_TYPE_ID => Mage::helper('tbtmilestone')->__('Referrals Milestone'));
        return $referenceOptions;
    }

    public function loadReferenceInformation(&$transfer)
    {
        $this->_loadTransferId($transfer);
        return $this;
    }

    protected function _loadTransferId($transfer)
    {
        $id = $transfer->getReferenceId();
        $transfer->setReferenceType(self::REFERENCE_TYPE_ID);
        $transfer->setReferenceId($id);
        $transfer->setData(self::REFERENCE_KEY, $id);

        return $this;
    }
}
