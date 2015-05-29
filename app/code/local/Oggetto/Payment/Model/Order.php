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
     * Success status from gateway
     */
    const PAYMENT_STATUS_SUCCESS = 1;
    /**
     * Error status from gateway
     */
    const PAYMENT_ERROR_SUCCESS  = 2;

    /**
     * Validate order
     *
     * @param Mage_Sales_Model_Order $order validating order
     * @param array                  $data  data for validating order
     * @return bool
     */
    public function validate($order, $data)
    {
        /** @var Oggetto_Payment_Helper_Data $helper */
        $helper = Mage::helper('oggetto_payment');

        if ($order->getId()) {
            $grandTotal = $helper->convertPriceFromFloatToCommaFormat($order->getGrandTotal());

            if ($data['total'] == $grandTotal) {
                $hashData = [
                    'order_id' => $data['order_id'],
                    'total'    => $data['total'],
                    'status'   => $data['status']
                ];
                $signature = $helper->getHashedSignature($hashData);

                if ($signature == $data['hash']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Handle order
     *
     * @param Mage_Sales_Model_Order $order  Handling order
     * @param string                 $status Payment status
     *
     * @return void
     */
    public function handle($order, $status)
    {
        if ($status == $this::PAYMENT_STATUS_SUCCESS) {

            $payment = $order->getPayment();
            $payment->registerCaptureNotification($order->getGrandTotal());

        } else {

            $invoice = $this->getInvoiceFromOrder($order);
            $invoice->cancel()->save();

            $order->cancel()->save();

        }
    }

    /**
     * Get invoice from order
     *
     * @param Mage_Sales_Model_Order $order Order
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function getInvoiceFromOrder(Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Sales_Model_Resource_Order_Invoice_Collection $invoiceCollection */
        $invoiceCollection = $order->getInvoiceCollection();
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = $invoiceCollection->getLastItem();
        return $invoice;
    }
}
