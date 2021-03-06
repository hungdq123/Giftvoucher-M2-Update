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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Adminhtml Generategiftcard ExportCsv Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class ExportCsv extends \Magento\Backend\App\Action
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_fileFactory = $fileFactory;
        $this->_coreDate = $coreDate;
    }

    public function execute()
    {
        $fileName = 'generatetemplate_' .$this->_coreDate->date() . '.csv';
        $exportBlock = $this->resultPageFactory->create()
            ->getLayout()
            ->createBlock('Magestore\Giftvoucher\Block\Adminhtml\Generategiftcard\Grid');
        return $this->_fileFactory->create($fileName, $exportBlock->getCsvFile($fileName), DirectoryList::VAR_DIR);
    }
}
