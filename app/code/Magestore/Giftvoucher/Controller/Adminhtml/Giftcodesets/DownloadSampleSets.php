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

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Adminhtml Giftvoucher DownloadSample Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class DownloadSampleSets extends \Magestore\Giftvoucher\Controller\Adminhtml\Giftvoucher
{
    public function execute()
    {
        $this->_view->loadLayout();
        $filename = $this->_objectManager->get('Magestore\Giftvoucher\Helper\Data')
            ->getBaseDirMedia()
            ->getAbsolutePath('giftvoucher/import_giftcodesets_sample.csv');
        return $this->getFileFactory()->create(
            'import_giftcodesets_sample.csv',
            file_get_contents($filename),
            DirectoryList::VAR_DIR
        );
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_Giftvoucher::giftvoucher');
    }
}
