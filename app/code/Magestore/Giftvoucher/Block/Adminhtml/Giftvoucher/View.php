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
namespace Magestore\Giftvoucher\Block\Adminhtml\Giftvoucher;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Adminhtml Giftvoucher View Block
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class View extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magestore\Giftvoucher\Model\Giftvoucher
     */
    protected $_giftvoucher;
    
    /**
     * @var \Magestore\Giftvoucher\Model\Gifttemplate
     */
    protected $_gifttemplate;
    
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;
    
    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_helperData;
    
    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;
    
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
     * @param \Magestore\Giftvoucher\Model\Gifttemplate $gifttemplate
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magestore\Giftvoucher\Helper\Data $helperData
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher,
        \Magestore\Giftvoucher\Model\Gifttemplate $gifttemplate,
        \Magento\Directory\Model\Currency $currency,
        \Magestore\Giftvoucher\Helper\Data $helperData,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        array $data = []
    ) {
        $this->_giftvoucher = $giftvoucher;
        $this->_gifttemplate = $gifttemplate;
        $this->_currency = $currency;
        $this->_helperData = $helperData;
        $this->_imageFactory = $imageFactory;
        $this->_localeCurrency = $localeCurrency;
        parent::__construct($context, $data);
    }
    
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    public function getGiftVoucher()
    {
        if (!$this->hasData('gift_voucher')) {
            $this->setData(
                'gift_voucher',
                $this->_giftvoucher->load($this->getRequest()->getParam('id'))
            );
        }
        return $this->getData('gift_voucher');
    }

    public function getGiftVouchers()
    {
        if (!$this->hasData('gift_vouchers')) {
            $giftvoucherIds = $this->getRequest()->getParam('ids');
            if (!is_array($giftvoucherIds)) {
                $giftvoucherIds = explode(',', $giftvoucherIds);
            }
            $giftvouchers = $this->_giftvoucher->getCollection()
                ->addFieldToFilter('giftvoucher_id', array(
                    'in' => $giftvoucherIds,
                ));
            $this->setData('gift_vouchers', $giftvouchers);
        }
        return $this->getData('gift_vouchers');
    }

    /**
     * Get Gift Card templates
     *
     * @param int $templateId
     * @return \Magestore\Giftvoucher\Model\Gifttemplate
     */
    public function getGiftcardTemplate($templateId)
    {
        $templates = $this->_gifttemplate->load($templateId);
        return $templates;
    }
    
    public function getGiftcardTemplates()
    {
        return $this->_gifttemplate->getCollection()->addFieldToFilter('status', '1');
    }

    public function getHelper()
    {
        return $this->_helperData;
    }
    
    public function getCurrency()
    {
        return $this->_currency;
    }
    
    public function getLocaleCurrency()
    {
        return $this->_localeCurrency;
    }
    
    public function getBaseDirMedia()
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }
    
    public function getImage()
    {
        return $this->_imageFactory->create();
    }
    
    public function getBarcodeInformation($giftCard)
    {
        $helper = $this->getHelper();
        $store = $this->getStore();
        $result = [];
        $barcode = $helper->getGeneralConfig('barcode_enable');
        $barcode_type = $helper->getGeneralConfig('barcode_type');
        $urlBarcode = '';
        $resizeImage = false;
        $qr = new \Magestore_Giftvoucher_QRCode($giftCard->getGiftCode());
        if ($barcode) {
            if ($barcode_type == 'code128') {
                $urlBarcode = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                    . 'giftvoucher/template/barcode/' . $giftCard->getGiftCode() . '.png';
                $imageUrl = $this->getMediaDirPath('giftvoucher/template/barcode/' . $giftCard->getGiftCode() . '.png');
                $imageObj = $this->getImage();
                $imageObj->open($imageUrl);
                $imageObj->getImage();
                if ($imageObj->getOriginalWidth() > 200) {
                    $resizeImage = true;
                }
            } else {
                $urlBarcode = $qr->getResult();
            }
        }
        $result['resize_image'] = $resizeImage;
        $result['url_barcode'] = $urlBarcode;

        return $result;
    }
}
