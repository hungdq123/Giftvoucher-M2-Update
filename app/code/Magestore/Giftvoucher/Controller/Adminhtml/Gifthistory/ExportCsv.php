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

namespace Magestore\Giftvoucher\Controller\Adminhtml\Gifthistory;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Adminhtml Gifthistory ExportCsv Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class ExportCsv extends \Magestore\Giftvoucher\Controller\Adminhtml\Giftvoucher
{

    /**
     * Export gift card history grid to CSV format
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'giftcard_history.csv';
        $exportBlock = $this->_view->getLayout()->createBlock('Magestore\Giftvoucher\Block\Adminhtml\Gifthistory\Grid');
        return $this->getFileFactory()->create(
            $fileName,
            $exportBlock->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}
