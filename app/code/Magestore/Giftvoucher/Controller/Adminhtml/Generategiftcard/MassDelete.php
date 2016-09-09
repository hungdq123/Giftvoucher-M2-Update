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

namespace Magestore\Giftvoucher\Controller\Adminhtml\Generategiftcard;

/**
 * Adminhtml Generategiftcard MassDelete Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Massdelete extends \Magento\Backend\App\Action
{

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magestore\Giftvoucher\Model\Generategiftcard $generategiftcard
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magestore\Giftvoucher\Model\Generategiftcard $generategiftcard
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_generategiftcard = $generategiftcard;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $templateIds = $this->getRequest()->getParam('template');
        if (!is_array($templateIds)) {
            $this->messageManager->addError(__('Please select Template(s)'));
        } else {
            try {
                foreach ($templateIds as $templateId) {
                    $template = $this->_generategiftcard->load($templateId);
                    $template->delete();
                }
                $this->messageManager->addSuccess(
                    __('Total of %d record(s) were successfully deleted', count($templateIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/index');
    }
}
