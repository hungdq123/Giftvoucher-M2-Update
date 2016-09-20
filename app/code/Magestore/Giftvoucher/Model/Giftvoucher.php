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

use Magestore\Giftvoucher\Model\Status;

/**
 * Giftvoucher Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Giftvoucher extends \Magento\Rule\Model\AbstractModel
{

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\CombineFactory
     */
    protected $_conditionsInstance;
    
    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    protected $_actionsInstance;
    
    /**
     * @var \Magestore\Giftvoucher\Helper\Data
     */
    protected $_helperData;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    
    /**
     * @var \Magento\Email\Model\Template
     */
    protected $_emailTemplate;
    
    /**
     * @var array
     */
    protected $_calculators = [];

    /**
     * Giftvoucher constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\SalesRule\Model\Rule\Condition\CombineFactory $conditionsInstance
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $actionsInstance
     * @param \Magestore\Giftvoucher\Helper\Data $helperData
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Email\Model\Template $emailTemplate
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $conditionsInstance,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $actionsInstance,
        \Magestore\Giftvoucher\Helper\Data $helperData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Email\Model\Template $emailTemplate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_conditionsInstance = $conditionsInstance;
        $this->_actionsInstance = $actionsInstance;
        $this->_helperData = $helperData;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_urlBuilder = $urlBuilder;
        $this->_emailTemplate = $emailTemplate;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }
    
    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Giftvoucher\Model\ResourceModel\Giftvoucher');
    }
    
    public function getCollection()
    {
        return parent::getCollection()->getAvailable();
    }
    
    /**
     * Load Gift Card by gift code
     *
     * @param string $code
     * @return \Magestore\Giftvoucher\Model\Giftvoucher
     */
    public function loadByCode($code)
    {
        return $this->load($code, 'gift_code');
    }
    
    public function load($id, $field = null)
    {
        parent::load($id, $field);
        
        $timeSite = date(
            "Y-m-d H:i:s",
            $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime')->timestamp(time())
        );
        if ($this->getIsDeleted()) {
            return $this;
        }

        if ($this->getStatus() == Status::STATUS_ACTIVE
            && $this->getExpiredAt() && $this->getExpiredAt() < $timeSite) {
            $this->setStatus(Status::STATUS_EXPIRED);
        }
        return $this;
    }
    
    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\CombineFactory
     */
    public function getConditionsInstance()
    {
        return $this->_conditionsInstance->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    public function getActionsInstance()
    {
        return $this->_actionsInstance->create();
    }
    
    /**
     * Initialize rule model data from array
     *
     * @param array $data
     * @return $this
     */
    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions([])->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['actions'])) {
            $this->getActions()->setActions([])->loadArray($arr['actions'][1], 'actions');
        }

        return $this;
    }
    
    public function roundPrice($price, $type = 'regular', $negative = false)
    {
        $calculatorFactory = $this->_objectManager->create('\Magento\Framework\Math\CalculatorFactory');
        if ($price) {
            if (!isset($this->_calculators[$type])) {
                $this->_calculators[$type] = $calculatorFactory->create(['scope' => $this->_storeManager->getStore()]);
            }
            $price = $this->_calculators[$type]->deltaRound($price, $negative);
        }
        return $price;
    }
    
    /**
     * Get the base balance of gift code
     *
     * @param string $storeId
     * @return float
     */
    public function getBaseBalance($storeId = null)
    {
        if (!$this->hasData('base_balance')) {
            $baseBalance = 0;
            if ($rate = $this->_storeManager->getStore($storeId)
                            ->getBaseCurrency()->getRate($this->getData('currency'))
            ) {
                $baseBalance = $this->getBalance() / $rate;
            }
            $this->setData('base_balance', $baseBalance);
        }
        return $this->getData('base_balance');
    }
    
    public function beforeSave()
    {
        parent::beforeSave();
        
        $timeSite = date(
            "Y-m-d H:i:s",
            $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime')->timestamp(time())
        );
        if (!$this->getId()) {
            $this->setAction(\Magestore\Giftvoucher\Model\Actions::ACTIONS_CREATE);
        }
        
        if ($this->getStoreId()==null) {
            $this->setStoreId(0);
        }
        
        if (!$this->getStatus()) {
            $this->setStatus(\Magestore\Giftvoucher\Model\Status::STATUS_PENDING);
        }

        if ($this->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_USED
            && $this->roundPrice($this->getBalance()) > 0) {
            $this->setStatus(\Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE);
        }

        if ($this->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
            && $this->roundPrice($this->getBalance()) == 0) {
            $this->setStatus(\Magestore\Giftvoucher\Model\Status::STATUS_USED);
        }
        
        if (($this->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE
            || $this->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_USED
            || $this->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_PENDING)
            && $this->getExpiredAt() && $this->getExpiredAt() < $timeSite) {
            $this->setStatus(\Magestore\Giftvoucher\Model\Status::STATUS_EXPIRED);
        }

        if ($this->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_EXPIRED
            && $this->getExpiredAt() && $this->getExpiredAt() > date('Y-m-d')) {
            $this->setExpiredAt(date('Y-m-d'));
        }
        
        if ($this->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_EXPIRED
            && !$this->getExpiredAt()) {
            $this->setExpiredAt(date('Y-m-d'));
        }
        
        if ($this->getExpiredAt() && $this->getExpiredAt() < date('Y-m-d')) {
            $this->setStatus(\Magestore\Giftvoucher\Model\Status::STATUS_EXPIRED);
        }

        if (!$this->getGiftCode()) {
            $this->setGiftCode(
                $this->_objectManager->create('Magestore\Giftvoucher\Helper\Data')->getGeneralConfig('pattern')
            );
        }
           //var_dump($this->getData());
//            var_dump($this->_objectManager->create('Magestore\Giftvoucher\Helper\Data')->getGeneralConfig('pattern'));
           //die();

            if ($this->_codeIsExpression()) {
                if($this->getGiftCode()==$this->_objectManager->create('Magestore\Giftvoucher\Helper\Data')->getGeneralConfig('pattern') || $this->getPattern()||$this->getFormKey()) {

                    $this->setGiftCode($this->_getGiftCode());
                }
            } else {
                if ($this->getAction() == \Magestore\Giftvoucher\Model\Actions::ACTIONS_CREATE && !$this->getGiftCode()) {
                    if ($this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')
                        ->loadByCode($this->getGiftCode())->getId()
                    ) {
                        throw new \Exception(__('Gift code is existed!'));
                    }
                }
            }

        
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        if (!$registryObject->registry('giftvoucher_conditions')) {
            $registryObject->register('giftvoucher_conditions', true);
        } else {
            if (!$this->getGenerateGiftcode()) {
                $data = $this->getData();
                if (isset($data['conditions_serialized'])) {
                    unset($data['conditions_serialized']);
                }
                if (isset($data['actions_serialized'])) {
                    unset($data['actions_serialized']);
                }
                $this->setData($data);
            }
        }
        
        $this->_helperData->createBarcode($this->getGiftCode());

        return $this;
    }
    
    public function afterSave()
    {
        parent::afterSave();
        
        if ($this->getIncludeHistory() && $this->getAction()) {
            $history = $this->_objectManager->create('Magestore\Giftvoucher\Model\History')
                ->setData($this->getData())
                ->setData(
                    'created_at',
                    (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
                );
            if ($this->getAction() == \Magestore\Giftvoucher\Model\Actions::ACTIONS_UPDATE
                || $this->getAction() == \Magestore\Giftvoucher\Model\Actions::ACTIONS_MASS_UPDATE
            ) {
                $history->setData('customer_id', null)
                    ->setData('customer_email', null)
                    ->setData('amount', $this->getBalance());
            }

            try {
                $history->save();
            } catch (\Exception $e) {
            }
        }
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        if (!$registryObject->registry('draw_gift_card' . $this->getGiftCode()) && !$this->getMassEmail()) {
            $this->_objectManager->create('Magestore\Giftvoucher\Helper\Drawgiftcard')->draw($this);
            $registryObject->register('draw_gift_card' . $this->getGiftCode(), 1);
        }

        return $this;
    }
    
    protected function _codeIsExpression()
    {
        return $this->_objectManager->create('Magestore\Giftvoucher\Helper\Data')->isExpression($this->getGiftCode());
    }
    
    protected function _getGiftCode()
    {
        $helper = $this->_objectManager->create('Magestore\Giftvoucher\Helper\Data');
        $code = $helper->calcCode($this->getGiftCode());
        $times = 10;
        while ($this->_objectManager->create('Magestore\Giftvoucher\Model\Giftvoucher')->loadByCode($code)->getId()
            && $times) {
            $code = $helper->calcCode($this->getGiftCode());
            $times--;
            if ($times == 0) {
                throw new \Exception(__('Exceeded maximum retries to find available random gift card code!'));
            }
        }
        return $code;
    }
    
    public function addToSession($session = null)
    {
        if (is_null($session)) {
            $session = $this->_helperData->getCheckoutSession();
        }
        if ($codes = $session->getGiftCodes()) {
            $codesArray = explode(',', $codes);
            $codesArray[] = $this->getGiftCode();
            $codes = implode(',', array_unique($codesArray));
        } else {
            $codes = $this->getGiftCode();
        }
        $session->setGiftCodes($codes);
        return $this;
    }
    
    public function sendEmail()
    {
        $store = $this->_storeManager->getStore($this->getStoreId());
        $storeId = $store->getStoreId();
        $mailSent = 0;
        if ($this->getCustomerEmail()) {
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $this->_helperData->getEmailConfig('self', $storeId)
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'store' => $store,
                        'giftvoucher' => $this,
                        'balance' => $this->getBalanceFormated(),
                        'status' => $this->getStatusLabel(),
                        'noactive' => ($this->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE)
                            ? 0 : 1,
                        'expiredat' => $this->getExpiredAt() ?
                            $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')
                                ->date('M d, Y', $this->getExpiredAt()) : '',
                        'message' => $this->getFormatedMessage(),
                        'note' => $this->getEmailNotes(),
                        'description' => $this->getDescription(),
                        'logo' => $this->getPrintLogo(),
                        'url' => $this->getPrintTemplate(),
                        'secure_key' => base64_encode($this->getGiftCode() . '$' . $this->getId()),
                    ]
                )->setFrom(
                    $this->_helperData->getEmailConfig('sender', $storeId)
                )->addTo(
                    $this->getCustomerEmail(),
                    $this->getCustomerName()
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {
                $this->_logger->critical($ex);
            }
            $mailSent++;
        }
        if ($this->getRecipientEmail()) {
            $mailSent += $this->sendEmailToRecipient();
        }
        if ($this->getRecipientEmail() || $this->getCustomerEmail()) {
            try {
                if ($this->getData('recipient_address')) {
                    $this->setIsSent(2);
                } else {
                    $this->setIsSent(true);
                }
                if (!$this->getNotResave()) {
                    $this->save();
                }
            } catch (\Exception $ex) {
                $this->_logger->critical($ex);
            }
        }

        $this->setEmailSent($mailSent);
        return $this;
    }
    
    /**
     * Send email to Gift Voucher Receipient
     *
     * @return int The number of email sent
     */
    public function sendEmailToRecipient()
    {
        $allowStatus = explode(',', $this->_helperData->getEmailConfig('only_complete', $this->getStoreId()));
        if (!is_array($allowStatus)) {
            $allowStatus = array();
        }
        if ($this->getRecipientEmail() && !$this->getData('dont_send_email_to_recipient')
            && in_array($this->getStatus(), $allowStatus)
        ) {
            try {
                $store = $this->_storeManager->getStore($this->getStoreId());
                $storeId = $store->getStoreId();

                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $this->_helperData->getEmailConfig('template', $storeId)
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'store' => $store,
                        'giftvoucher' => $this,
                        'balance' => $this->getBalanceFormated(),
                        'status' => $this->getStatusLabel(),
                        'noactive' => ($this->getStatus() == \Magestore\Giftvoucher\Model\Status::STATUS_ACTIVE)
                        ? 0 : 1,
                        'expiredat' => $this->getExpiredAt() ?
                            $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')
                                ->date('M d, Y', $this->getExpiredAt()) : '',
                        'message' => $this->getFormatedMessage(),
                        'note' => $this->getEmailNotes(),
                        'logo' => $this->getPrintLogo(),
                        'url' => $this->getPrintTemplate(),
                        'addurl' => $this->_urlBuilder->getUrl('giftvoucher/index/addlist', array(
                            'giftvouchercode' => $this->getGiftCode()
                        )),
                        'secure_key' => base64_encode($this->getGiftCode() . '$' . $this->getId())
                    ]
                )->setFrom(
                    $this->_helperData->getEmailConfig('sender', $storeId)
                )->addTo(
                    $this->getRecipientEmail(),
                    $this->getRecipientName()
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {
                $this->_logger->critical($ex);
            }
            
            try {
                if (!$this->getData('recipient_address')) {
                    $this->setIsSent(true);
                } else {
                    $this->setIsSent(2);
                }
                if (!$this->getNotResave()) {
                    $this->save();
                }
            } catch (\Exception $ex) {
                $this->_logger->critical($ex);
            }
            return 1;
        }
        return 0;
    }
    
    /**
     * Send the success notification email
     *
     * @return \Magestore\Giftvoucher\Model\Giftvoucher
     */
    public function sendEmailSuccess()
    {
        if ($this->getCustomerEmail()) {
            $store = $this->_storeManager->getStore($this->getStoreId());
            $storeId = $store->getStoreId();
            
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $this->_helperData->getEmailConfig('template_success', $storeId)
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'name' => $this->getCustomerName(),
                    ]
                )->setFrom(
                    $this->_helperData->getEmailConfig('sender', $storeId)
                )->addTo(
                    $this->getCustomerEmail(),
                    $this->getCustomerName()
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {
                $this->_logger->critical($ex);
            }
        }
        return $this;
    }
    
    /**
     * Send the refund notification email
     *
     * @return \Magestore\Giftvoucher\Model\Giftvoucher
     */
    public function sendEmailRefundToRecipient()
    {
        if ($this->getRecipientEmail() && !$this->getData('dont_send_email_to_recipient')) {
            $store = $this->_storeManager->getStore($this->getStoreId());
            $storeId = $store->getStoreId();
            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier(
                    $this->_helperData->getEmailConfig('template_refund', $storeId)
                )->setTemplateOptions(
                    ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                )->setTemplateVars(
                    [
                        'store' => $store,
                        'sendername' => $this->getCustomerName(),
                        'receivename' => $this->getRecipientName(),
                        'code' => $this->getGiftCode(),
                        'balance' => $this->getBalanceFormated(),
                        'status' => $this->getStatusLabel(),
                        'message' => $this->getFormatedMessage(),
                        'description' => $this->getDescription(),
                        'addurl' => $this->_urlBuilder->getUrl('giftvoucher/index/addlist', array(
                            'giftvouchercode' => $this->getGiftCode()
                        )),
                    ]
                )->setFrom(
                    $this->_helperData->getEmailConfig('sender', $storeId)
                )->addTo(
                    $this->getRecipientEmail(),
                    $this->getRecipientName()
                )->getTransport();
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $ex) {
                $this->_logger->critical($ex);
            }
        }
        return $this;
    }
    
    /**
     * Get the print template image
     *
     * @return string
     */
    public function getPrintTemplate()
    {
        $images = $this->_objectManager->create('Magestore\Giftvoucher\Helper\Drawgiftcard')
            ->getImagesInFolder($this->getGiftCode());

        if (isset($images[0]) && file_exists($images[0])) {
            $search = $this->_helperData->getBaseDirMedia()
                ->getAbsolutePath('giftvoucher/draw/' . $this->getGiftCode() . '/');
            $replace = $this->_helperData->getBaseDirMedia()
                ->getAbsolutePath('giftvoucher/draw/' . $this->getGiftCode() . '/');
            $result = str_replace($search, $replace, $images[0]);

            return $result;
        }
        return '';
    }
    
    public function getStatusLabel()
    {
        $statusArray = \Magestore\Giftvoucher\Model\Status::getOptionArray();
        return $statusArray[$this->getStatus()];
    }
    
    public function getFormatedMessage()
    {
        return str_replace("\n", "<br/>", $this->getMessage());
    }
    
    /**
     * Get the email notes
     *
     * @return string
     */
    public function getEmailNotes()
    {
        if (!$this->hasData('email_notes')) {
            $notes = $this->_scopeConfig->getValue(
                'giftvoucher/email/note',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            );
            $notes = str_replace(array(
                '{store_url}',
                '{store_name}',
                '{store_address}'

                ), array(
                $this->_storeManager->getStore($this->getStoreId())->getBaseUrl(),
                $this->_storeManager->getStore($this->getStoreId())->getFrontendName(),
                $this->_scopeConfig->getValue(
                    'general/store_information/address',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->getStoreId()
                )

                ), $notes);
            $this->setData('email_notes', $notes);
        }
        return $this->getData('email_notes');
    }
    
    /**
     * Get the print logo
     *
     * @return string|boolean
     */
    public function getPrintLogo()
    {
        $image = $this->_scopeConfig->getValue('giftvoucher/print_voucher/logo', 'store', $this->getStoreId());
        if ($image) {
            $image = $this->_storeManager->getStore($this->getStoreId())->getBaseUrl('media')
                . 'giftvoucher/pdf/logo/' . $image;
            return $image;
        }
        return false;
    }
    
    /**
     * Returns the formatted balance
     *
     * @return string
     */
    public function getBalanceFormated()
    {
        $currency = $this->_objectManager->create('Magento\Directory\Model\Currency')->load($this->getCurrency());
        return $currency->format($this->getBalance());
    }
    
    /**
     * Get the print notes
     *
     * @return string
     */
    public function getPrintNotes()
    {
        if (!$this->hasData('print_notes')) {
            $notes = $this->_scopeConfig->getValue('giftvoucher/print_voucher/note', 'store', $this->getStoreId());
            $notes = str_replace(
                array(
                    '{store_url}',
                    '{store_name}',
                    '{store_address}'
                ),
                array(
                    '<span class="print-notes">' . $this->_storeManager->getStore($this->getStoreId())->getBaseUrl()
                    . '</span>',
                    '<span class="print-notes">' . $this->_storeManager->getStore($this->getStoreId())->getFrontendName() .
                        '</span>',
                    '<span class="print-notes">' . $this->_scopeConfig->getValue(
                        'general/store_information/address',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $this->getStoreId()
                    ) . '</span>'
                ),
                $notes
            );
            $this->setData('print_notes', $notes);
        }
        return $this->getData('print_notes');
    }
    
    /**
     * Get the list customer that used this code
     *
     * @return array
     */
    public function getCustomerIdsUsed()
    {
        $collection = $this->_objectManager->create('Magestore\Giftvoucher\Model\ResourceModel\History\Collection')
            ->addFieldToFilter('main_table.giftvoucher_id', $this->getId())
            ->addFieldToFilter('main_table.action', \Magestore\Giftvoucher\Model\Actions::ACTIONS_SPEND_ORDER);

        $collection->joinSalesOrder();
        $customerIds = array();
        foreach ($collection as $item) {
            $customerIds[] = $item->getData('order_customer_id');
        }
        return $customerIds;
    }
}
