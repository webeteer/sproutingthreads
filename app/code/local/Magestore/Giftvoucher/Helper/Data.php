<?php

class Magestore_Giftvoucher_Helper_Data extends Mage_Core_Helper_Data {

    protected $_imageUrl;
    protected $_imageName;
    protected $_imageReturn;

    public function getGeneralConfig($code, $store = null) {
        if ($code == 'barcode_enable' || $code == 'barcode_type') {
            return Mage::getStoreConfig('giftvoucher/print_voucher/' . $code, $store);
        }
        return Mage::getStoreConfig('giftvoucher/general/' . $code, $store);
    }

    public function getInterfaceConfig($code, $store = null) {
        return Mage::getStoreConfig('giftvoucher/interface/' . $code, $store);
    }

    //Hai.Tran 28/11
    public function getInterfaceCheckoutConfig($code, $store = null) {
        return Mage::getStoreConfig('giftvoucher/interface_checkout/' . $code, $store);
    }

    //Hai.Tran 28/11 End
	 public function isAllowRedeem($store = null) {
        if ($this->getGeneralConfig('enablecredit', $store)) {
            return true;
        }
        if ($this->getGeneralConfig('allow_enterprise_balance', $store) && Mage::getStoreConfig('customer/enterprise_customerbalance/is_enabled', $store)
        ) {
            return true;
        }
        return false;
    }
    public function getEmailConfig($code, $store = null) {
        return Mage::getStoreConfig('giftvoucher/email/' . $code, $store);
    }

    public function calcCode($expression) {
        if ($this->isExpression($expression)) {
            return preg_replace_callback('#\[([AN]{1,2})\.([0-9]+)\]#', array($this, 'convertExpression'), $expression);
        } else {
            return $expression;
        }
    }

    public function convertExpression($param) {
        $alphabet = (strpos($param[1], 'A')) === false ? '' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphabet .= (strpos($param[1], 'N')) === false ? '' : '0123456789';
        return $this->getRandomString($param[2], $alphabet);
    }

    public function isExpression($string) {
        return preg_match('#\[([AN]{1,2})\.([0-9]+)\]#', $string);
    }

    public function getGiftAmount($amountStr) {
        $amountStr = trim(str_replace(array(' ', "\r", "\t"), '', $amountStr));
        if ($amountStr == '' || $amountStr == '-') {
            return array('type' => 'any');
        }

        $values = explode('-', $amountStr);
        if (count($values) == 2) {
            return array('type' => 'range', 'from' => $values[0], 'to' => $values[1]);
        }

        $values = explode(',', $amountStr);
        if (count($values) > 1) {
            return array('type' => 'dropdown', 'options' => $values);
        }

        $value = floatval($amountStr);
        return array('type' => 'static', 'value' => $value);
    }

    public function getGiftVoucherOptions() {
        //Hai.Tran 28/11
        $option = explode(',', Mage::helper('giftvoucher')->getInterfaceCheckoutConfig('display'));
        $result = array();
        foreach ($option as $key => $val) {
            if ($val == 'amount')
                $result['amount'] = $this->__('Gift Card value');
            if ($val == 'giftcard_template_id')
                $result['giftcard_template_id'] = $this->__('Gift Card template');
            if ($val == 'customer_name')
                $result['customer_name'] = $this->__('Sender name');
            if ($val == 'recipient_name')
                $result['recipient_name'] = $this->__('Recipient name');
            if ($val == 'recipient_email')
                $result['recipient_email'] = $this->__('Recipient email address');
            if ($val == 'recipient_ship')
                $result['recipient_ship'] = $this->__('Ship to recipient');
            if ($val == 'recipient_address')
                $result['recipient_address'] = $this->__('Recipient address');
            if ($val == 'message')
                $result['message'] = $this->__('Custom message');
            if ($val == 'day_to_send')
                $result['day_to_send'] = $this->__('Day to send');
            if ($val == 'timezone_to_send')
                $result['timezone_to_send'] = $this->__('Time zone');
            if ($val == 'giftcard_use_custom_image')
                $result['giftcard_use_custom_image'] = $this->__('Use custom image');
        }
        return $result;
//        return array(
//            'recipient_name' => $this->__('Recipient name'),
//            'recipient_email' => $this->__('Recipient email'),
//            'recipient_ship' => $this->__('Ship to recipient'),
//            'recipient_address' => $this->__('Recipient address'),
//            'message' => $this->__('Custom message'),
//            'day_to_send' => $this->__('Day To Send'),
//        );
    }

    //Hai.Tran
    public function getFullGiftVoucherOptions() {
        return array(
            'customer_name' => $this->__('Sender Name'),
            'giftcard_template_id' => $this->__('Giftcard Template'),
            'send_friend' => $this->__('Send Gift Card to friend'),
            'recipient_name' => $this->__('Recipient name'),
            'recipient_email' => $this->__('Recipient email'),
            'recipient_ship' => $this->__('Ship to recipient'),
            'recipient_address' => $this->__('Recipient address'),
            'message' => $this->__('Custom message'),
            'day_to_send' => $this->__('Day To Send'),
            'timezone_to_send' => $this->__('Time zone'),
            'email_sender' => $this->__('Email To Sender'),
            'amount' => $this->__('Amount'),
            'giftcard_template_image' => $this->__('Giftcard Image'),
            'giftcard_use_custom_image' => $this->__('Use Custom Image'),
            'notify_success' => $this->__('Notify when the recipient receives Gift Card.')
        );
    }

    public function getHiddenCode($code) {
        $prefix = $this->getGeneralConfig('showprefix');
        $prefixCode = substr($code, 0, $prefix);
        $suffixCode = substr($code, $prefix);
        if ($suffixCode) {
            $hiddenChar = $this->getGeneralConfig('hiddenchar');
            if (!$hiddenChar)
                $hiddenChar = 'X';
            else
                $hiddenChar = substr($hiddenChar, 0, 1);
            $suffixCode = preg_replace('#([A-Z,0-9]{1})#', $hiddenChar, $suffixCode);
        }
        return $prefixCode . $suffixCode;
    }

    public function isAvailableToAddCode() {
        $codes = Mage::getSingleton('giftvoucher/session')->getCodes();
        if ($max = Mage::helper('giftvoucher')->getGeneralConfig('maximum'))
            if (count($codes) >= $max)
                return false;
        return true;
    }

    /**
     * check code can used to checkout or not
     * 
     * @param mixed $code
     * @return boolean
     */
    public function canUseCode($code) {
        if (!$code) {
            return false;
        }
        if (is_string($code)) {
            $code = Mage::getModel('giftvoucher/giftvoucher')->loadByCode($code);
        }
        if (!($code instanceof Magestore_Giftvoucher_Model_Giftvoucher)) {
            return false;
        }
        if (!$code->getId()) {
            return false;
        }
        if (Mage::app()->getStore()->isAdmin()) {
            return true;
        }
        $shareCard = intval($this->getGeneralConfig('share_card'));
        if ($shareCard < 1) {
            return true;
        }
        $customersUsed = $code->getCustomerIdsUsed();
        if ($shareCard > count($customersUsed) || in_array(Mage::getSingleton('customer/session')->getCustomerId(), $customersUsed)
        ) {
            return true;
        }
        return false;
    }

    public function getAllowedCurrencies() {
        $optionArray = array();
        $baseCode = Mage::app()->getBaseCurrencyCode();
        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCode, array_values($allowedCurrencies));

        foreach ($rates as $key => $value) {
            $test = Mage::app()->getLocale()->currency($key);
            $optionArray[] = array('value' => $key, 'label' => $test->getName());
        }

        if (!count($optionArray)) {
            $test = Mage::app()->getLocale()->currency($baseCode);
            $optionArray[] = array('value' => $baseCode, 'label' => $test->getName());
        }

        return $optionArray;
    }

    public function getCheckGiftCardUrl() {
        return Mage::getUrl('giftvoucher/index/check');
    }

    /**
     * upload template image
     * @param type $type
     * @return type
     */
    public static function uploadImage($type) {
        self::createImageFolder($type);
        if (strpos($type, 'image') !== false) {
            $image_path = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'images' . DS;
            $image_path_end = 'images';
        } else {
            $image_path = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'background' . DS;
            $image_path_end = 'background';
        }
        $image = "";
        if (isset($_FILES[$type]['name']) && $_FILES[$type]['name'] != '') {
            try {
                /* Starting upload */
                $uploader = new Varien_File_Uploader($type);

                // Any extention would work
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                $uploader->setAllowRenameFiles(true);

                $uploader->setFilesDispersion(false);
                $result = $uploader->save($image_path, $_FILES[$type]['name']);

                $image = $uploader->getUploadedFileName();
                self::resizeImage(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'giftvoucher/template/' . $image_path_end . '/' . $result['file']);
                self::customResizeImage(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'giftvoucher/template/' . $image_path_end . '/', $result['file'], $image_path_end);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('giftvoucher')->__('The file uploaded has invalid format. Support jpg, jpeg, gif, png files only.'));
            }
        }
        return $image;
    }

    /**
     * create folder for template image
     * @param type $type
     */
    public static function createImageFolder($type) {
        if (strpos($type, 'image') !== false)
            $image_path = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'images' . DS;
        else {
            $image_path = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'background' . DS;
        }
        if (!is_dir($image_path)) {
            try {

                mkdir($image_path);
                chmod($image_path, 0777);
            } catch (Exception $e) {
                
            }
        }
    }

    public static function createImageFolderHaitv($parent, $type, $tmp = false) {
        if ($type !== '')
            $urlType = $type . DS;
        else
            $urlType = '';
        if ($tmp)
            $image_path = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'giftvoucher' . DS . 'images' . DS;
        else
            $image_path = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . $parent . DS . $urlType;
        if (!is_dir($image_path)) {
            try {

                mkdir($image_path);
                chmod($image_path, 0777);
            } catch (Exception $e) {
                
            }
        }
    }

    /**
     * delete image
     * @param type $image
     * @return type
     */
    public static function deleteImageFile($image) {

        if (!$image) {
            return;
        }
        $dirImg = Mage::getBaseDir() . str_replace("/", DS, strstr($image, '/media'));
        if (!file_exists($dirImg)) {
            return;
        }

        try {
            unlink($dirImg);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * convert notes
     * @param type $notes
     */
    public function convertNotes($notes) {
        $notes = str_replace(array(
            '{store_url}',
            '{store_name}',
            '{store_address}'
                ), array(
            Mage::app()->getStore($this->getStoreId())->getBaseUrl(),
            Mage::app()->getStore($this->getStoreId())->getFrontendName(),
            Mage::getStoreConfig('general/store_information/address', $this->getStoreId())
                ), $notes);
        return $notes;
    }

    public static function resizeImage($imageUrl) {
        $imageUrl = Mage::getBaseDir() . str_replace("/", DS, strstr($imageUrl, '/media'));
        if (file_exists($imageUrl)) {
            $imageObj = new Varien_Image($imageUrl);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepFrame(false);
            $imageObj->resize(600, 365);
            self::deleteImageFile($imageUrl);
            $imageObj->save($imageUrl);
        }
    }

    public static function customResizeImage($imagePath, $imageName, $imageType) {
        $imagePath = Mage::getBaseDir() . str_replace("/", DS, strstr($imagePath, '/media'));
        $imageUrl = $imagePath . $imageName;
        if (file_exists($imageUrl)) {
            self::createImageFolderHaitv($imageType, 'left');
            self::createImageFolderHaitv($imageType, 'top');
            if ($imageType == 'images') {
                $imageObj = new Varien_Image($imageUrl);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(False);
                $imageObj->keepFrame(false);
                $imageObj->resize(600, 190);
                $imageObj->save($imagePath . 'top/' . $imageName);

                $imageObj = new Varien_Image($imageUrl);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(False);
                $imageObj->keepFrame(false);
                $imageObj->resize(250, 365);
                $imageObj->save($imagePath . 'left/' . $imageName);
            } else {
                $imageObj = new Varien_Image($imageUrl);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(False);
                $imageObj->keepFrame(false);
                $imageObj->resize(600, 175);
                $imageObj->save($imagePath . 'top/' . $imageName);

                $imageObj = new Varien_Image($imageUrl);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(False);
                $imageObj->keepFrame(false);
                $imageObj->resize(350, 365);
                $imageObj->save($imagePath . 'left/' . $imageName);
            }
        }
    }

    public function createBarcode($gift_code) {
        Mage::helper('giftvoucher')->createImageFolderHaitv('barcode', '');
        $options = array('text' => $gift_code, 'barHeight' => 40, 'barThickWidth' => 2, 'drawText' => false, 'barHeight' => 42, 'withQuietZones' => false, 'barThinWidth' => 1, 'factor' => 1);
        $barcode = new Zend_Barcode_Object_Code128($options);

        $barImage = new Zend_Barcode_Renderer_Image();
        $barImage->setBarcode($barcode);
        $resource = $barImage->draw();
        $imageUrl = Mage::getBaseDir('media') . DS . 'giftvoucher' . DS . 'template' . DS . 'barcode' . DS . $gift_code . '.png';
        imagepng($resource, $imageUrl);
        imagedestroy($resource);

        $imageObj = new Varien_Image($imageUrl);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio(true);
        $imageObj->keepFrame(true);
        $imageObj->backgroundColor(array(255, 255, 255));
        $imageObj->resize($imageObj->getOriginalWidth() + 8, 40);
        $imageObj->save($imageUrl);
    }

    public function getProductThumbnail($url, $filename, $urlImage) {
        $this->_imageUrl = null;
        $this->_imageName = null;
        $this->_imageReturn = null;

        $this->_imageUrl = $url;
        $this->_imageName = $filename;
        $this->_imageReturn = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $urlImage;
        return $this;
    }

    public function resize($width, $height = null) {
        $imageReturn = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "tmp/giftvoucher/cache/" . $this->_imageName;
        $this->_imageReturn = $imageReturn;
        if (file_exists(Mage::getBaseDir() . str_replace("/", DS, strstr($imageReturn, '/media')))) {
            //return $this;
        }
        if ($height == null)
            $height = $width;
        $imageUrl = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'giftvoucher' . DS . 'cache' . DS . str_replace("/", DS, $this->_imageName);
        $imageObj = new Varien_Image($this->_imageUrl);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio(TRUE);
        $imageObj->keepFrame(TRUE);
        $imageObj->backgroundColor(array(255, 255, 255));
        $imageObj->resize($width, $height);
        try {
            $imageObj->save($imageUrl);
        } catch (Exception $e) {
            
        }
        return $this;
    }

    public function setWatermarkSize($size) {
        return $this;
    }

    public function __toString() {
        if ($this->_imageReturn)
            return $this->_imageReturn;
        return '';
    }

    public function getItemRateOnQuote($product, $store) {
        //Calculate rate to subtract taxable amount
        $priceIncludesTax = (bool) Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store);
        $taxClassId = $product->getTaxClassId();
        if ($taxClassId && $priceIncludesTax) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false, $store);
            $rate = Mage::getSingleton('tax/calculation')
                    ->getRate($request->setProductClassId($taxClassId));
            return $rate;
        }
        return 0;
    }

}
