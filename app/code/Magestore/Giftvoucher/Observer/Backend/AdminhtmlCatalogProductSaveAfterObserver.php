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

class AdminhtmlCatalogProductSaveAfterObserver extends \Magestore\Giftvoucher\Observer\GiftcardObserver
{
    /**
     * Redirect when admin edit gift product
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magestore\Giftvoucher\Observer\Backend\AdminhtmlCatalogProductSaveAfterObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        $back = $action->getRequest()->getParam('back', false);
        $session = $this->_objectManager->get('Magestore\Giftvoucher\Model\Session');
        $giftproductsession = $session->getGiftProductEdit();
        
        if ($back || !$giftproductsession) {
            return;
        }
        
        $type = $action->getRequest()->getParam('type');
        
        if (!$type) {
            $id = $action->getRequest()->getParam('id');
            $type = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id)->getTypeId();
        }
        if (!$type) {
            return $this;
        }
        if($type == 'giftvoucher'){
            $session->unsetData('gift_product_edit');
            $action->getActionFlag()->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            // $url = $this->_url->getUrl('giftvoucheradmin/giftproduct/index');
            $this->messageManager->addSuccess(__('You saved the product.'));
            $action->getResponse()->setRedirect(
                $action->getUrl('giftvoucheradmin/giftproduct/index')
            )->sendResponse();
            exit;
        }
        return $this;
    }
}
