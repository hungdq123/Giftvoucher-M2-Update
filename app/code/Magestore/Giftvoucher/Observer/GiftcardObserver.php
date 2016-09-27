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
namespace Magestore\Giftvoucher\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ObserverInterface;

class GiftcardObserver implements ObserverInterface
{

    /**
     * @var Indexer\Category\Flat\State
     */
    protected $categoryFlatConfig;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;
    
    /**
     * Model Url instance
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;
    
    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    private $_catalogLayer = null;

    /**
     * Catalog layer resolver
     *
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_catalogProduct;

    /**
     * Catalog category1
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $_categoryResource;

    /**
     * Factory for product resource
     *
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $_productResourceFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    
    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_helperData;
    
    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;
    
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $_sessionManager;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param Resource\Category $categoryResource
     * @param Resource\Product $catalogProduct
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Backend\Model\UrlFactory $backendUrlFactory
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceFactory
     * @param \Magestore\Giftvoucher\Helper\Data
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Backend\Model\UrlFactory $backendUrlFactory,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceFactory,
        \Magestore\Giftvoucher\Helper\Data $helperData,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magestore\Giftvoucher\Model\Giftvoucher $giftvoucher
    ) {
        $this->_url = $backendUrlFactory->create();
        $this->_request = $request;
        $this->_objectManager = $objectManager;
        $this->_registry = $registry;
        $this->_categoryResource = $categoryResource;
        $this->_catalogProduct = $catalogProduct;
        $this->_storeManager = $storeManager;
        $this->_actionFlag = $actionFlag;
        $this->layerResolver = $layerResolver;
        $this->_catalogCategory = $catalogCategory;
        $this->_catalogData = $catalogData;
        $this->categoryFlatConfig = $categoryFlatState;
        $this->_productResourceFactory = $productResourceFactory;
        $this->_helperData = $helperData;
        $this->_appState = $appState;
        $this->_priceCurrency = $priceCurrency;
        $this->messageManager = $messageManager;
        $this->_sessionManager = $sessionManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_urlBuilder = $urlBuilder;
        $this->_imageFactory = $imageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }
    
    /**
     * Add Gift Card data to order
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function _addGiftVoucherForOrder($order)
    {
        $adminSession = $this->_objectManager->get('Magento\Backend\Model\Session\Quote');
        if ($this->_appState->getAreaCode() == 'admin') {
            $store = $adminSession->getStore();
        } else {
            $store = $this->_storeManager->getStore();
        }

        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() != 'giftvoucher') {
                continue;
            }

            $options = $item->getProductOptions();

            $buyRequest = $options['info_buyRequest'];

            $quoteItemOptions = $this->_objectManager->create('Magento\Quote\Model\Quote\Item\Option')->getCollection()
                ->addFieldToFilter('item_id', array('eq' => $item->getQuoteItemId()));
            if (isset($buyRequest['amount']) && $quoteItemOptions) {
                foreach ($quoteItemOptions as $quoteItemOption) {
                    if ($quoteItemOption->getCode() == 'amount') {
                        $buyRequest['amount'] = $this->_priceCurrency->round($quoteItemOption->getValue());
                        $options['info_buyRequest'] = $buyRequest;
                        $item->setProductOptions($options);
                    }
                }
            }
//           var_dump($item->getProduct()->getGiftCardType());
//            var_dump($item->getProduct()->getGiftCodeSets());

            //var_dump($giftCodeSets);
//            if(!$giftCodeSets){
//
//                $giftVouchers = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')->getCollection()->addFieldToFilter('template_id',$item->getProduct()->getGiftCodeSets())
//                    ->addFieldToFilter('used',2)->getFirstItem()->addItemFilter($item->getQuoteItemId());
//            }else{
//                $giftVouchers = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')->getCollection()
//                    ->addItemFilter($item->getQuoteItemId());
//            }

//            var_dump($giftCodeSets->getGiftCode());
            $giftVouchers = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')->getCollection()
                ->addItemFilter($item->getQuoteItemId());
            //var_dump($giftVouchers->getSize());
            //var_dump($giftCodeSets);
           // die('xx');
            $time = time();
            for ($i = 0; $i < $item->getQtyOrdered() - $giftVouchers->getSize(); $i++) {
                $giftCodeSets =$this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')->getCollection()->addFieldToFilter('set_id',$item->getProduct()->getGiftCodeSets())
                    ->addFieldToFilter('used',2)->getFirstItem()->getGiftCode();

                if($giftCodeSets){
                    $giftVoucher = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')->getCollection()->addFieldToFilter('set_id',$item->getProduct()->getGiftCodeSets())
                        ->addFieldToFilter('used',2)->getFirstItem();
                    //var_dump( $giftVoucher->getData());die();
                }else{
                    $giftVoucher = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher');
                    //$this->_registry->register('code_sets','aaa');
                }
//                var_dump($giftVoucher->getData());
//                die();

                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                if (isset($buyRequest['amount'])) {
                    $amount = $buyRequest['amount'];
                } else {
                    $amount = $item->getPrice();
                }

                $giftVoucher->setBalance($amount)->setAmount($amount);
                $giftVoucher->setOrderAmount($item->getBasePrice());

                $giftProduct = $this->_objectManager->create('Magestore\Giftvoucher\Model\Product')
                    ->loadByProduct($product);
                $giftVoucher->setDescription($giftProduct->getGiftcardDescription());
                if ($giftProduct->getId()) {
                    $conditionsArr = unserialize($giftProduct->getConditionsSerialized());
                    $actionsArr = unserialize($giftProduct->getActionsSerialized());
                    if (!empty($conditionsArr) && is_array($conditionsArr)) {
                        $giftVoucher->getConditions()->loadArray($conditionsArr);
                    }
                    if (!empty($actionsArr) && is_array($actionsArr)) {
                        $giftVoucher->getActions()->loadArray($actionsArr);
                    }
                }
                //Hai.Tran
                if (isset($buyRequest['customer_name'])) {
                    $giftVoucher->setCustomerName($buyRequest['customer_name']);
                }
                if (isset($buyRequest['giftcard_template_id']) && $buyRequest['giftcard_template_id']) {
                    $giftVoucher->setGiftcardTemplateId($buyRequest['giftcard_template_id']);
                }
                if (isset($buyRequest['recipient_name'])) {
                    $giftVoucher->setRecipientName($buyRequest['recipient_name']);
                }
                if (isset($buyRequest['recipient_email'])) {
                    $giftVoucher->setRecipientEmail($buyRequest['recipient_email']);
                }
                if (isset($buyRequest['message'])) {
                    $giftVoucher->setMessage($buyRequest['message']);
                }
                if (isset($buyRequest['notify_success'])) {
                    $giftVoucher->setNotifySuccess($buyRequest['notify_success']);
                }
                if (isset($buyRequest['day_to_send']) && $buyRequest['day_to_send']) {
                    $giftVoucher->setDayToSend(date('Y-m-d', strtotime($buyRequest['day_to_send'])));
                }

                if (isset($buyRequest['timezone_to_send']) && $buyRequest['timezone_to_send']) {
                    $giftVoucher->setTimezoneToSend($buyRequest['timezone_to_send']);
                    // $customerZone = new \DateTimeZone($giftVoucher->getTimezoneToSend());
                    $customerZone = $this->_objectManager->create('DateTimeZone', ['timezone' => $giftVoucher->getTimezoneToSend()]);
                    // $date = new \DateTime($giftVoucher->getDayToSend(), $customerZone);
                    $date = $this->_objectManager->create(
                        'DateTime',
                        [
                            'time' => $giftVoucher->getDayToSend(),
                            'DateTimeZone' => $customerZone
                        ]
                    );
                    $serverTimezone = $this->_storeManager->getStore()->getConfig('general/locale/timezone');
                    $date->setTimezone(
                        $this->_objectManager->create('DateTimeZone', ['timezone' => $serverTimezone])
                    );
                    $giftVoucher->setDayStore($date->format('Y-m-d'));
                }

                if (isset($buyRequest['giftcard_template_image']) && $buyRequest['giftcard_template_image']) {
                    if (isset($buyRequest['giftcard_use_custom_image']) && $buyRequest['giftcard_use_custom_image']) {
                        $dir = $this->_helperData->getBaseDirMedia()->getAbsolutePath('tmp/giftvoucher/images/'
                                . $buyRequest['giftcard_template_image']);
                        if (file_exists($dir)) {
                            $imageObj = $this->_imageFactory->create();
                            $imageObj->open($dir);
                            $imagePath = $this->_helperData->getStoreManager()->getStore()
                                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                                . 'giftvoucher/template/images/';
                            $customerIploadImage = strval($time) . $buyRequest['giftcard_template_image'];
                            $dirCustomerUpload = $this->_helperData->getBaseDirMedia()
                                ->getAbsolutePath(strstr($imagePath, '/giftvoucher'). $customerIploadImage);

                            if (!file_exists($dirCustomerUpload)) {
                                $result = $imageObj->save($dirCustomerUpload);
                                $this->_helperData->customResizeImage($customerIploadImage, 'images');
                            }
                            $giftVoucher->setGiftcardCustomImage(true);
                            $giftVoucher->setGiftcardTemplateImage($customerIploadImage);
                        } else {
                            $giftVoucher->setGiftcardTemplateImage('default.png');
                        }
                    } else {
                        $giftVoucher->setGiftcardTemplateImage($buyRequest['giftcard_template_image']);
                    }
                }

                if (isset($buyRequest['recipient_ship'])
                    && $buyRequest['recipient_ship'] != null
                    && $address = $order->getShippingAddress()) {
                    $giftVoucher->setRecipientAddress($address->getFormated());
                }

                $giftVoucher->setCurrency($store->getCurrentCurrencyCode());

                if ($order->getStatus() == \Magento\Sales\Model\Order::STATE_COMPLETE) {
                    $giftVoucher->setStatus(\Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE);
                } else {
                    $giftVoucher->setStatus(\Magestore\Giftvoucher\Model\Status::STATUS_PENDING);
                }

                if ($timeLife = $this->_helperData->getGeneralConfig('expire', $order->getStoreId())) {
                    $orderTime = date(
                        "Y-m-d",
                        $this->_helperData->getObjectManager()->get('Magento\Framework\Stdlib\DateTime\DateTime')
                                ->timestamp(time())
                    );
                    $expire = date('Y-m-d', strtotime($orderTime . '+' . $timeLife . ' days'));
                    $giftVoucher->setExpiredAt($expire);
                }

                $giftVoucher->setCustomerId($order->getCustomerId())
                        ->setCustomerEmail($order->getCustomerEmail())
                        ->setStoreId($order->getStoreId());

                if (!$giftVoucher->getCustomerName()) {
                    $giftVoucher->setCustomerName(
                        $order->getData('customer_firstname') . ' ' . $order->getData('customer_lastname')
                    );
                }

                    $giftVoucher->setAction(\Magestore\Giftvoucher\Model\Actions::ACTIONS_CREATE)
                        ->setComments(__('Created for order %1', $order->getIncrementId()))
                        ->setOrderIncrementId($order->getIncrementId())
                        ->setQuoteItemId($item->getQuoteItemId())
                        ->setExtraContent(
                            __('Created by customer %1 %2', $order->getData('customer_firstname'), $order->getData('customer_lastname'))
                        )
                        ->setIncludeHistory(true);

                try {
                    if ($giftVoucher->getDayToSend() && strtotime($giftVoucher->getDayToSend()) > time()
                    ) {
                        $giftVoucher->setData('dont_send_email_to_recipient', 1);
                    }
                    if (!empty($buyRequest['recipient_ship'])) {
                        $giftVoucher->setData('is_sent', 2);
                        if (!$this->_helperData->getEmailConfig('send_with_ship', $order->getStoreId())) {
                            $giftVoucher->setData('dont_send_email_to_recipient', 1);
                        }
                    }
                    //$this->_registry->register('code_sets', 'createcode');
                    if($giftCodeSets){
                        //var_dump($giftCodeSets);
                        //$this->_registry->register('code_sets', $giftCodeSets);
                        $giftVoucher->setGiftCode($giftCodeSets);
//                        $giftVoucher->setOrderIncrementId($order->getIncrementId());
//                        $giftVoucher->setAction(\Magestore\Giftvoucher\Model\Actions::ACTIONS_UPDATE);
                        $giftVoucher->setUsed(1);

                        //var_dump($giftVoucher->getGiftCode());
                    }
                    //var_dump($giftVoucher->getData());die('xxxx');
                    $giftVoucher->save();

                    if ($order->getCustomerId()) {
                        $timeSite = date(
                            "Y-m-d",
                            $this->_helperData->getObjectManager()->get('Magento\Framework\Stdlib\DateTime\DateTime')
                                ->timestamp(time())
                        );
                        $this->_objectManager->create('Magestore\Giftvoucher\Model\Customervoucher')
                                ->setCustomerId($order->getCustomerId())
                                ->setVoucherId($giftVoucher->getId())
                                ->setAddedDate($timeSite)
                                ->save();
                    }
                } catch (\Exception $e) {
                }
            }
        }
        return;
    }

    /**
     * Get Gift Card information when loading order
     *
     * @param $order
     */
    protected function _loadOrderData($order)
    {
        $giftVouchers = $this->_objectManager->create('Magestore\Giftvoucher\Model\History')
                            ->getCollection()
                            ->joinGiftVoucher()
                            ->addFieldToFilter('main_table.order_increment_id', $order->getIncrementId());
        $codesArray = array();
        $baseDiscount = 0;
        $discount = 0;
        foreach ($giftVouchers as $giftVoucher) {
            $codesArray[] = $giftVoucher->getGiftCode();
            $baseDiscount += $giftVoucher->getAmount();
            $discount += $giftVoucher->getOrderAmount();
        }
        if ($baseDiscount) {
            $baseCurrency = $this->_priceCurrency->getCurrency(null, $order->getBaseCurrencyCode());
            $currentCurrency = $this->_priceCurrency->getCurrency(null, $order->getOrderCurrencyCode());
            $baseDiscount = $baseDiscount * $baseDiscount / $baseCurrency->convert($baseDiscount, $currentCurrency);

            $order->setGiftCodes(implode(',', $codesArray));
            $order->setBaseGiftVoucherDiscount($baseDiscount);
            $order->setGiftVoucherDiscount($discount);

        }
        $creditHistory = $this->_objectManager->create('Magestore\Giftvoucher\Model\ResourceModel\Credithistory\Collection')
                ->addFieldToFilter('action', 'Spend')
                ->addFieldToFilter('order_id', $order->getId())
                ->getFirstItem();
        if ($creditHistory && $creditHistory->getId()) {
            $order->setGiftcardCreditAmount($creditHistory->getBalanceChange());
            $order->setBaseUseGiftCreditAmount($creditHistory->getBaseAmount());
            $order->setUseGiftCreditAmount($creditHistory->getAmount());
        }
        return;
    }

    /**
     * Process Gift Card data when refund offline
     *
     * @param EventObserver $observer
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    protected function _refundOffline($order, $baseGrandTotal)
    {
        $adminSession = $this->_objectManager->get('Magento\Backend\Model\Session\Quote');
        if ($this->_appState->getAreaCode() == 'admin') {
            $store = $adminSession->getStore();
        } else {
            $store = $this->_storeManager->getStore();
        }

        if ($codes = $order->getGiftCodes()) {
            $codesArray = explode(',', $codes);
            foreach ($codesArray as $code) {
                if ($this->_priceCurrency->round($baseGrandTotal) == 0) {
                    return;
                }

                $giftVoucher = $this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')
                                    ->loadByCode($code);
                $history = $this->_objectManager->create('Magestore\Giftvoucher\Model\History');

                $availableDiscount = $history->getTotalSpent($giftVoucher, $order)
                    - $history->getTotalRefund($giftVoucher, $order);
                if ($this->_priceCurrency->round($availableDiscount) == 0) {
                    continue;
                }

                if ($availableDiscount < $baseGrandTotal) {
                    $baseGrandTotal = $baseGrandTotal - $availableDiscount;
                } else {
                    $availableDiscount = $baseGrandTotal;
                    $baseGrandTotal = 0;
                }
                $baseCurrencyCode = $order->getBaseCurrencyCode();
                $baseCurrency = $this->_objectManager->create('Magento\Directory\Model\Currency')
                                        ->load($baseCurrencyCode);
                $currentCurrency = $this->_objectManager->create('Magento\Directory\Model\Currency')
                                        ->load($giftVoucher->getData('currency'));

                $discountRefund = $this->_objectManager->create('Magento\Directory\Helper\Data')
                    ->currencyConvert($availableDiscount, $baseCurrencyCode, $giftVoucher->getData('currency'));
                $discountCurrentRefund = $this->_objectManager->create('Magento\Directory\Helper\Data')
                    ->currencyConvert($availableDiscount, $baseCurrencyCode, $order->getOrderCurrencyCode());

                $balance = $giftVoucher->getBalance() + $discountRefund;
                $baseBalance = $balance * $balance / $baseCurrency->convert($balance, $currentCurrency);
                $currentBalance = $this->_objectManager->create('Magento\Directory\Helper\Data')
                    ->currencyConvert($baseBalance, $baseCurrencyCode, $order->getOrderCurrencyCode());

                if ($giftVoucher->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_USED) {
                    $giftVoucher->setStatus(\Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE);
                }
                $giftVoucher->setData('balance', $balance)->save();

                $history->setData(array(
                    'order_increment_id' => $order->getIncrementId(),
                    'giftvoucher_id' => $giftVoucher->getId(),
                    'created_at' => date("Y-m-d H:i:s"),
                    'action' => \Magestore\Giftvoucher\Model\Actions::ACTIONS_REFUND,
                    'amount' => $discountCurrentRefund,
                    'balance' => $currentBalance,
                    'currency' => $order->getOrderCurrencyCode(),
                    'status' => $giftVoucher->getStatus(),
                    'comments' => __('Refund from order %1', $order->getIncrementId()),
                    'customer_id' => $order->getData('customer_id'),
                    'customer_email' => $order->getData('customer_email'),
                ))->save();
            }
        }
        if ($order->getBaseUseGiftCreditAmount() && $order->getCustomerId()
            && $this->_helperData->getGeneralConfig('enablecredit', $order->getStoreId())) {
            $credit = $this->_objectManager->create('Magestore\Giftvoucher\Model\Credit')
                            ->load($order->getCustomerId(), 'customer_id');
            if ($credit->getId()) {
                // check order is refunded to credit balance
                $histories = $this->_objectManager
                        ->create('Magestore\Giftvoucher\Model\ResourceModel\Credithistory\Collection')
                        ->addFieldToFilter('customer_id', $order->getCustomerId())
                        ->addFieldToFilter('action', 'Refund')
                        ->addFieldToFilter('order_id', $order->getId())
                        ->getFirstItem();
                if ($histories && $histories->getId()) {
                    return;
                }
                try {
                    $credit->setBalance($credit->getBalance() + $order->getBaseUseGiftCreditAmount());
                    $credit->save();
                    if ($store->getCurrentCurrencyCode() != $order->getBaseCurrencyCode()) {
                        $baseCurrency = $this->_objectManager->create('Magento\Directory\Model\Currency')
                                                ->load($order->getBaseCurrencyCode());
                        $currentCurrency = $this->_objectManager->create('Magento\Directory\Model\Currency')
                                                ->load($order->getOrderCurrencyCode());
                        $currencyBalance = $baseCurrency->convert(round($credit->getBalance(), 4), $currentCurrency);
                    } else {
                        $currencyBalance = round($credit->getBalance(), 4);
                    }
                    $credithistory = $this->_objectManager->create('Magestore\Giftvoucher\Model\Credithistory')
                                            ->setData($credit->getData());
                    $credithistory->addData(array(
                        'action' => 'Refund',
                        'currency_balance' => $currencyBalance,
                        'order_id' => $order->getId(),
                        'order_number' => $order->getIncrementId(),
                        'balance_change' => $order->getUseGiftCreditAmount(),
                        'created_date' => date("Y-m-d H:i:s"),
                        'currency' => $store->getCurrentCurrencyCode(),
                        'base_amount' => $order->getBaseUseGiftCreditAmount(),
                        'amount' => $order->getUseGiftCreditAmount()
                    ))->setId(null)->save();
                } catch (\Exception $e) {
                }
            }
        }
        return;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
    }
}
