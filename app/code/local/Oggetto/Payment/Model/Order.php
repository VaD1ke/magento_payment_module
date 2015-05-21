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
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Handle order
     *
     * @param string $status  payment status
     * @param string $orderId order ID
     *
     * @return void
     */
    public function handle($status, $orderId)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($orderId);
        

        /** @var Mage_Sales_Model_Resource_Order_Invoice_Collection $invoiceCollection */
        $invoiceCollection = $order->getInvoiceCollection();
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = $invoiceCollection->getLastItem();

        if ($status == 1) {
            if ($invoice->canCapture()) {
                $invoice->capture()->save();
            }

            $order->setState(
                    Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.'
                )
                ->setStatus('processing')
                ->sendNewOrderEmail()
                ->setEmailSent(true);

            $order->save();
        } else {
            if ($invoice->canCancel()) {
                $invoice->cancel()->save();
            }

            if ($order->canCancel()) {
                $order->cancel()->setState(
                    Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.'
                )->save();
            }

        }
    }
}
