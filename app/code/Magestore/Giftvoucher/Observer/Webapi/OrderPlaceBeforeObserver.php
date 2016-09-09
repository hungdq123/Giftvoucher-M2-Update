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
namespace Magestore\Giftvoucher\Observer\Webapi;

use Magento\Framework\DataObject;

class OrderPlaceBeforeObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Check gift codes before place order
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        if ($codes = $session->getGiftCodes()) {
            $codesArray = explode(',', $codes);
            $baseSessionAmountUsed = explode(',', $session->getBaseAmountUsed());
            $baseAmountUsed = array_combine($codesArray, $baseSessionAmountUsed);

            foreach ($baseAmountUsed as $code => $amount) {
                $model = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')
                    ->loadByCode(strval($code));
                if (!$model || $model->getStatus() != \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
                    || round($model->getBaseBalance(), 10) < round($amount, 10)) {
                    Mage::app()->getResponse()
                            ->setHeader('HTTP/1.1', '403 Session Expired')
                            ->setHeader('Login-Required', 'true')
                            ->sendResponse();
                    exit;
                }
            }
        }
    }
}
