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
 * Giftvoucher History Model
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @author      Magestore Developer
 */
class History extends \Magento\Framework\Model\AbstractModel
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magestore\Giftvoucher\Model\ResourceModel\History');
    }
    
    /**
     * Filter Gift Card history
     *
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftVoucher
     * @param \Magento\Sales\Model\Order $order
     * @param int $action
     * @return \Magestore\Giftvoucher\Model\History
     */
    public function getCollectionByOrderAction($giftVoucher, $order, $action)
    {
        return $this->getCollection()
            ->addFieldToFilter('giftvoucher_id', $giftVoucher->getId())
            ->addFieldToFilter('action', $action)
            ->addFieldToFilter('order_increment_id', $order->getIncrementId());
    }
    
    /**
     * Get the total amount of Gift Card spent in order
     *
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftVoucher
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getTotalSpent($giftVoucher, $order)
    {
        $total = 0;
        $histories = $this->getCollectionByOrderAction(
            $giftVoucher,
            $order,
            \Magestore\Giftvoucher\Model\Actions::ACTIONS_SPEND_ORDER
        );
        foreach ($histories as $history) {
            $total += $history->getAmount();
        }
        return $total;
    }
    
    /**
     * Get the total amount of Gift Card refunded in order
     *
     * @param \Magestore\Giftvoucher\Model\Giftvoucher $giftVoucher
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getTotalRefund($giftVoucher, $order)
    {
        $total = 0;
        $histories = $this->getCollectionByOrderAction(
            $giftVoucher,
            $order,
            \Magestore\Giftvoucher\Model\Actions::ACTIONS_REFUND
        );
        foreach ($histories as $history) {
            $total += $history->getAmount();
        }
        return $total;
    }
}
