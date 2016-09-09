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
namespace Magestore\Giftvoucher\Observer\Backend;

use Magento\Framework\DataObject;

class ConditionsActionObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Render Gift Card conditions
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $this->_registry->registry('current_product');
        $model = $this->_objectManager->get('Magestore\Giftvoucher\Model\Product');
        if (!$model->getId() && $product->getId()) {
            $model->loadByProduct($product);
        }
    }
}
