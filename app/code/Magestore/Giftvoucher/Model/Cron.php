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
namespace Magestore\Giftvoucher\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Giftvoucher Cron Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Cron
{
    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_helperGiftvoucher;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var Giftvoucher
     */
    protected $_giftvoucherModel;

    /**
     * @var \Magestore\Giftvoucher\Model\ResourceModel\Giftvoucher\Collection
     */
    protected $_giftvoucherCollection;

    /**
     * Cron constructor.
     * @param \Magestore\Giftvoucher\Helper\Data $helper
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Giftvoucher $giftvoucherModel
     * @param ResourceModel\Giftvoucher\Collection $giftvoucherCollection
     */
    public function __construct(
        \Magestore\Giftvoucher\Helper\Data $helper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucherModel,
        \Magestore\Giftvoucher\Model\ResourceModel\Giftvoucher\Collection $giftvoucherCollection
    ) {
        $this->_helperGiftvoucher = $helper;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_giftvoucherModel = $giftvoucherModel;
        $this->_giftvoucherCollection = $giftvoucherCollection;
    }
    
    public function autoSendMail()
    {
        if ($this->_helperGiftvoucher->getEmailConfig('autosend')) {
            $giftVouchers = $this->_giftvoucherModel->getCollection()
                    ->addFieldToFilter('status', array('neq' => \Magestore\Giftvoucher\Model\Status::STATUS_DELETED))
                    ->addExpireAfterDaysFilter($this->_helperGiftvoucher->getEmailConfig('daybefore'));
            foreach ($giftVouchers as $giftVoucher) {
                $giftVoucher->sendEmail();
            }
        }
    }
    
    public function sendScheduleEmail()
    {
        $collection = $this->_giftvoucherCollection;
        $timeSite = date("Y-m-d H:i:s", $this->_date->timestamp());
        $collection->addFieldToFilter('is_sent', array('neq' => 1))
                ->addFieldToFilter('day_store', array('notnull' => true))
                ->addFieldToFilter('day_store', array('to' => $timeSite));
        if (count($collection)) {
            foreach ($collection as $giftCard) {
                $giftCard->save();
                if ($giftCard->sendEmailToRecipient()) {
                    if ($giftCard->getNotifySuccess()) {
                        $giftCard->sendEmailSuccess();
                    }
                }
            }
        }
    }
}
