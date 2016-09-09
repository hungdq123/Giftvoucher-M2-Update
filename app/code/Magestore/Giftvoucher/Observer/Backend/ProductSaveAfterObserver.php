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

class ProductSaveAfterObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Set Gift Card conditions when product is saved
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() != 'giftvoucher' || !$product->getId()) {
            return;
        }
        $model = $this->_objectManager->get('Magestore\Giftvoucher\Model\Product');
        if ($model->getIsSavedConditions()) {
            return;
        }
        $model->setIsSavedConditions(true);
        if (!$model->getId()) {
            $model->loadByProduct($product);
        }
        
        $data = $this->_request->getPostValue();
        
        if (isset($data['rule'])) {
            $rules = $data['rule'];
            if (isset($rules['conditions'])) {
                $data['conditions'] = $rules['conditions'];
            }
            if (isset($rules['actions'])) {
                $data['actions'] = $rules['actions'];
            }
            unset($data['rule']);
        }
        $model->loadPost($data);
        $model->setProductId($product->getId());
        try {
            $model->save();
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            throw new \Magento\Framework\Exception\LocalizedException(__($exception->getMessage()));
        }
    }
}
