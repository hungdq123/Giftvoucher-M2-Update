<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Giftvoucher\Controller\Index;

use Magento\Customer\Model\Session;

/**
 * Giftvoucher Index Check Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Check extends \Magestore\Giftvoucher\Controller\Action
{
   
    public function execute()
    {
        $max = $this->getHelper()->getGeneralConfig('maximum');
        $resultPage = $this->getPageFactory();
        if ($code = $this->getRequest()->getPostValue('code')) {
            $resultPage->getConfig()->getTitle()->set($this->getHelper()->getHiddenCode($code));
            $giftVoucher = $this->getGiftvoucherModel()->loadByCode($code);
            $codes = $this->_objectManager->get('Magestore\Giftvoucher\Model\Session')->getCodesInvalid();
            if (!$giftVoucher->getId()) {
                $codes[] = $code;
                $codes = array_unique($codes);
                $this->_objectManager->get('Magestore\Giftvoucher\Model\Session')->setCodesInvalid($codes);
            }
            if (!$this->getHelper()->isAvailableToCheckCode()) {
                $this->messageManager->addError(
                    __('The maximum number of times to enter the invalid gift codes is %1!', $max)
                );
                $this->_view->getLayout()->initMessages();
                return $resultPage;
            }
            if (!$giftVoucher->getId()) {
                $errorMessage = __('Invalid gift code. ');
                if ($max) {
                    $errorMessage .=
                        __(
                            'You have %1 time(s) remaining to check your Gift Card code.',
                            $max - count($codes)
                        );
                }
                $this->messageManager->addError($errorMessage);
            }
        } else {
            $resultPage->getConfig()->getTitle()->set(__('Check Gift Card Balance'));
            if (!$this->getHelper()->isAvailableToCheckCode()) {
                $this->messageManager->addError(
                    __('The maximum number of times to enter the invalid gift codes is %1!', $max)
                );
            }
        }
        $this->_view->getLayout()->initMessages();
        return $resultPage;
    }
}
