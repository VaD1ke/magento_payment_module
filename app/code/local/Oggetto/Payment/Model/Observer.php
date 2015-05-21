<?php

class Oggetto_Payment_Model_Observer
{
    /**
     * Save order after commit
     *
     * @param Varien_Event_Observer $observer observer
     * @return Mage_Authorizenet_Model_Directpost_Observer
     */
    public function export(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW &&
            $order->getPayment()->getMethod() == Mage::getModel('oggetto_payment/standard')->getCode()) {

            try {
                if (!$order->canInvoice()) {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
                }

                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                if (!$invoice->getTotalQty()) {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                }

                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                $invoice->register();

                /** @var Mage_Core_Model_Resource_Transaction $transactionSave */
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());

                $transactionSave->save();
            }
            catch (Mage_Core_Exception $e) {
                Mage::logException($e);
            }
        }
    }
}
