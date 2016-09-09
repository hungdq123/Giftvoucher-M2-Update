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
namespace Magestore\Giftvoucher\Controller\Adminhtml\Checkout;

use Magento\Customer\Model\Session;

/**
 * Adminhtml Checkout Removegift Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Removegift extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magestore\Giftvoucher\Helper\Data $giftvoucherHelper,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher,
        \Magento\Framework\Json\Helper\Data $helperJson
    ) {
        parent::__construct($context);
        $this->_sessionQuote = $sessionQuote;
        $this->_checkoutSession = $checkoutSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->_giftvoucherHelper = $giftvoucherHelper;
        $this->_giftvoucher = $giftvoucher;
        $this->_helperJson = $helperJson;
    }
    
    public function execute()
    {
        $session = $this->_checkoutSession;
        $code = trim($this->getRequest()->getParam('code'));
        $codes = $session->getGiftCodes();

        $success = false;
        if ($code && $codes) {
            $codesArray = explode(',', $codes);
            foreach ($codesArray as $key => $value) {
                if ($value == $code) {
                    unset($codesArray[$key]);
                    $success = true;
                    $giftMaxUseAmount = unserialize($session->getGiftMaxUseAmount());
                    if (is_array($giftMaxUseAmount) && array_key_exists($code, $giftMaxUseAmount)) {
                        unset($giftMaxUseAmount[$code]);
                        $session->setGiftMaxUseAmount(serialize($giftMaxUseAmount));
                    }
                    break;
                }
            }
        }

        if ($success) {
            $codes = implode(',', $codesArray);
            $session->setGiftCodes($codes);
            $this->messageManager->addSuccess(
                __('Gift card "%1" has been removed successfully.', $code)
            );
        } else {
            $this->messageManager->addError(
                __('Gift card "%1" not found!', $code)
            );
        }
        $this->getResponse()->setBody($this->_helperJson->jsonEncode(array()));
    }
}
