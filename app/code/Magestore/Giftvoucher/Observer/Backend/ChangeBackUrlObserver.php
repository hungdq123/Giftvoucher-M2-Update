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

class ChangeBackUrlObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Reset the back link for Gift Card
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer['block'];
        $session = $this->_objectManager->get('Magestore\Giftvoucher\Model\Session');
        $storeId = $this->_storeManager->getStore()->getStoreId();

        if ($this->_helperData->getGeneralConfig('active', $storeId)
            && $block->getNameInLayout() == 'product_edit'
            && $session->getGiftProductBackUrl()) {
            if (!$block->getRequest()->getParam('popup')) {
                if ($block->getToolbar()) {
                    $childBlock = $block->getToolbar()->getChildBlock('back_button');
                    $childBlock->setOnClick(
                        'setLocation(\'' . $block->getUrl(
                            'giftvoucheradmin/giftproduct/index',
                            ['store' => $block->getRequest()->getParam('store', 0)]
                        ) . '\')'
                    );
                }
                $session->unsetData('gift_product_back_url');
            }
        }
    }
}
