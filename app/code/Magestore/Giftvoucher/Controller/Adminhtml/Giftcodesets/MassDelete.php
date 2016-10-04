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

namespace Magestore\Giftvoucher\Controller\Adminhtml\Giftcodesets;

/**
 * Adminhtml Generategiftcard MassDelete Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class MassDelete extends \Magento\Backend\App\Action
{

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magestore\Giftvoucher\Model\Generategiftcard $generategiftcard
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magestore\Giftvoucher\Model\Giftcodesets $generategiftcard
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_generategiftcard = $generategiftcard;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $templateIds = $this->getRequest()->getParam('set_name');
        if (!is_array($templateIds)) {
            $this->messageManager->addError(__('Please select Set(s)'));
        } else {
            try {
                foreach ($templateIds as $templateId) {
                    $template = $this->_generategiftcard->load($templateId);
                    $template->delete();
                }
                $this->messageManager->addSuccess(
                    __('The Gift Code Set was deleted successfully')
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/index');
    }
}
