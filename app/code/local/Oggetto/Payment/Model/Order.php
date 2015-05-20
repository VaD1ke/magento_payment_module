<?php
/**
 * Oggetto Payment extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Oggetto Payment module to newer versions in the future.
 * If you wish to customize the Oggetto Payment module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @copyright  Copyright (C) 2015 Oggetto Web (http://oggettoweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment order model
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage Model
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_Model_Order extends Mage_Core_Model_Abstract
{
    /**
     * Order
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Validate order
     *
     * @param array $data data for validating order
     * @return bool
     */
    public function validate($data)
    {
        /** @var Oggetto_Payment_Helper_Data $helper */
        $helper = Mage::helper('oggetto_payment');

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($data['order_id']);

        if ($order->getId()) {
            $helper->setOrder($order);
            $grandTotal = $helper->convertPriceFromFloatToCommaFormat($order->getGrandTotal());

            if ($data['total'] == $grandTotal) {
                $signature = $helper->getHashedSignature($helper->getFormFields());

                if ($signature == $data['hash']) {
                    $this->_order = $order;
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Handle order
     *
     * @param string $status payment status
     * @return void
     */
    public function handle($status)
    {

        $invoice = $this->_order->getInvoiceCollection()->getLastItem();

        if ($status == 1) {

        } else {

            $this->_order->cancel()->setState(
                Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.'
            )->save();
        }
    }
}
