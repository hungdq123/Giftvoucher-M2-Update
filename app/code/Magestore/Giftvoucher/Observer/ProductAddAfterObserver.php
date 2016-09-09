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
namespace Magestore\Giftvoucher\Observer;

use Magento\Framework\DataObject;

class ProductAddAfterObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Set the Gift Card custom images to the customer session after Gift Card products is added to cart
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $product = $event->getProduct();
        if ($product->getTypeId() == 'giftvoucher') {
            $this->_objectManager->get('Magento\Customer\Model\Session')->setGiftcardCustomUploadImage('');
        }
    }
}
