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
 * Payment Controller for front
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage controllers
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Is triggered when someone places an order
     *
     * @return void
     */
    public function redirectAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Is triggered when your gateway sends back a response after processing the customer's payment
     *
     * @return void
     */
    public function responseAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();

            try {
                if ($data['status'] == 1 || $data['status'] == 2) {
                    /** @var Oggetto_Payment_Model_Order $orderModel */
                    $orderModel = Mage::getModel('oggetto_payment/order');

                    if ($orderModel->validate($data)) {
                        $orderModel->handle($data['status'], $data['order_id']);
                        $this->getResponse()->setHttpResponseCode(200);
                    } else {
                        $this->getResponse()->setHttpResponseCode(400);
                    }
                } else {
                    $this->getResponse()->setHttpResponseCode(400);
                }
            } catch (Exception $e) {
                Mage::logException($e);
                $this->getResponse()->setHttpResponseCode(500);
            }

        }
    }

    /**
     * Is triggered when an order is to be cancelled
     *
     * @return void
     */
    public function cancelAction()
    {
        $errorMessage = $this->getRequest()->getParam('message');
        Mage::getSingleton('core/session')->addError($errorMessage);

        /** @var Oggetto_Payment_Helper_Data $helper */
        $helper = Mage::helper('oggetto_payment');

        $order = $helper->getOrder();

        /** @var Oggetto_Payment_Model_Order $paymentOrder */
        $paymentOrder = Mage::getModel('oggetto_payment/order');
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = $paymentOrder->getInvoiceFromOrder($order);

        if ($order->getId()) {
            $invoice->cancel()->save();

            $order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();

        }

        $this->_redirect('checkout/onepage/failure');
    }
}
