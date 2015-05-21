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
 * Standard payment Model
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage Model
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Unique internal payment method identifier
     * @var string
     */
    protected $_code = 'oggetto_payment';

    /**
     * Is initialize needed
     * @var bool
     */
    protected $_isInitializeNeeded     = true;
    /**
     * Can use this payment method in administration panel?
     * @var bool
     */
    protected $_canUseInternal         = false;
    /**
     * Is this payment method suitable for multi-shipping checkout?
     * @var bool
     */
    protected $_canUseForMultishipping = false;
    /**
     * Can capture funds online?
     * @var bool
     */
    protected $_canCapture             = true;

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        //when you click on place order you will be redirected on this url, if you don't want this action remove this method
        /** @var Oggetto_Payment_Helper_Data $helper */
        $helper = Mage::helper('oggetto_payment');

        return $helper->getRedirectSecureUrl();
    }

    /**
     * Handle placed order
     *
     * @param Mage_Sales_Model_Order $order Placed order
     * @return void
     */
    public function handlePlacedOrder(Mage_Sales_Model_Order $order)
    {
        if ($this->checkNewOrder($order)) {

            try {
                if (!$order->canInvoice()) {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
                }

                $invoice = $this->prepareInvoice($order);

                if (!$invoice->getTotalQty()) {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                }

                $invoice->register();

                $this->_saveTransactionWithAddedInvoice($invoice);
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Prepare and register invoice
     *
     * @param Mage_Sales_Model_Order $order Order
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function prepareInvoice(Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Sales_Model_Service_Order $serviceOrder */
        $serviceOrder = Mage::getModel('sales/service_order', $order);

        $invoice = $serviceOrder->prepareInvoice();
        return $invoice;
    }

    /**
     * Check new order
     *
     * @param Mage_Sales_Model_Order $order Order
     * @return bool
     */
    public function checkNewOrder(Mage_Sales_Model_Order $order)
    {
        return $this->_isOrderNew($order) && $this->_isSelfPaymentMethod($order);
    }


    /**
     * Save transaction with added invoice
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice Invoice
     * @throws Exception
     * @throws bool
     */
    protected function _saveTransactionWithAddedInvoice($invoice)
    {
        /** @var Mage_Core_Model_Resource_Transaction $transactionSave */
        $transactionSave = Mage::getModel('core/resource_transaction');

        $transactionSave->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
    }

    /**
     * Is order new
     *
     * @param Mage_Sales_Model_Order $order Order
     * @return bool
     */
    protected function _isOrderNew(Mage_Sales_Model_Order $order)
    {
        return $order->getState() == Mage_Sales_Model_Order::STATE_NEW;
    }

    /**
     * Is order payment method is this method
     *
     * @param Mage_Sales_Model_Order $order Order
     * @return bool
     */
    protected function _isSelfPaymentMethod(Mage_Sales_Model_Order $order)
    {
        return $order->getPayment()->getMethod() == $this->_code;
    }
}
