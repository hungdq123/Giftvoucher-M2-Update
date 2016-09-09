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

/**
 * Giftvoucher Credit Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class Credit extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Credit constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null
    ) {
        $this->_customerSession = $customerSession;
        $this->_objectManager = $objectManager;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('Magestore\Giftvoucher\Model\ResourceModel\Credit');
    }

    public function getCreditAccountLogin()
    {
        $customerId = $this->_customerSession->getCustomerId();
        return $this->getCreditByCustomerId($customerId);
    }

    public function getCreditByCustomerId($customerId)
    {
        $collection = $this->getCollection()->addFieldToFilter('customer_id', $customerId);
        if ($collection->getSize()) {
            $id = $collection->getFirstItem()->getId();
            $this->load($id);
        }
        return $this;
    }
}
