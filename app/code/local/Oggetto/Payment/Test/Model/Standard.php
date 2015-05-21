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
 * Order standard method test class
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage Test
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_Test_Model_Standard extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Oggetto Payment Standard method
     *
     * @var Oggetto_Payment_Model_Standard
     */
    protected $_oggettoPayment;

    /**
     * Set up initial variables
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_oggettoPayment = Mage::getModel('oggetto_payment/standard');
    }

    /**
     * Return order place Redirect URL from Oggetto Payment Helper Data
     *
     * @return void
     */
    public function testReturnsOrderPlaceRedirectUrlFromOggettoPaymentHelperData()
    {
        /** @var Oggetto_Payment_Model_Standard $standard */
        $standard = Mage::getModel('oggetto_payment/standard');

        $testUrl = 'test';

        $helperDataMock = $this->getHelperMock('oggetto_payment', ['getRedirectSecureUrl']);

        $helperDataMock->expects($this->once())
            ->method('getRedirectSecureUrl')
            ->willReturn($testUrl);

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);

        $this->assertEquals($testUrl, $standard->getOrderPlaceRedirectUrl());
    }

    /**
     * Sets transaction objects and register invoice with canInvoice Order status and not null order quantity
     *
     * @return void
     */
    public function testSetsTransactionObjectsAndRegisterInvoiceWithCanInvoiceOrderStatusAndNotNullQty()
    {
        $modelOrderMock = $this->getModelMock('sales/order', ['getState', 'getPayment', 'canInvoice']);

        $modelOrderMock->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);


        $modelInvoiceMock = $this->getModelMock('sales/order_invoice', ['getTotalQty', 'register']);

        $modelInvoiceMock->expects($this->once())
            ->method('getTotalQty')
            ->willReturn(777);

        $modelInvoiceMock->expects($this->once())
            ->method('register');

        $this->replaceByMock('model', 'sales/order_invoice', $modelInvoiceMock);


        $modelStandardMock = $this->getModelMock('oggetto_payment/standard', [
            '_saveTransactionWithAddedInvoice', 'prepareInvoice', 'checkNewOrder'
        ]);

        $modelStandardMock->expects($this->once())
            ->method('checkNewOrder')
            ->with($modelOrderMock)
            ->willReturn(true);

        $modelStandardMock->expects($this->once())
            ->method('_saveTransactionWithAddedInvoice')
            ->with($modelInvoiceMock);

        $modelStandardMock->expects($this->once())
            ->method('prepareInvoice')
            ->willReturn($modelInvoiceMock);

        $this->replaceByMock('model', 'oggetto_payment/standard', $modelStandardMock);

        $modelStandardMock->handlePlacedOrder($modelOrderMock);
    }

    /**
     * Check invoice is not preparing when handling placed order without canInvoice status
     *
     * @return void
     */
    public function testChecksInvoiceIsNotPreparingWhenHandlingPlacedOrderWithoutCanInvoiceStatus()
    {
        $modelOrderMock = $this->getModelMock('sales/order', ['getState', 'getPayment', 'canInvoice']);

        $modelOrderMock->expects($this->once())
            ->method('canInvoice')
            ->willReturn(false);

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);


        $modelStandardMock = $this->getModelMock('oggetto_payment/standard', [
            '_saveTransactionWithAddedInvoice', 'prepareInvoice', 'checkNewOrder'
        ]);

        $modelStandardMock->expects($this->once())
            ->method('checkNewOrder')
            ->with($modelOrderMock)
            ->willReturn(true);

        $modelStandardMock->expects($this->never())
            ->method('_saveTransactionWithAddedInvoice');

        $modelStandardMock->expects($this->never())
            ->method('prepareInvoice');

        $this->replaceByMock('model', 'oggetto_payment/standard', $modelStandardMock);

        $modelStandardMock->handlePlacedOrder($modelOrderMock);
    }

    /**
     * Sets transaction objects and register invoice with canInvoice Order status and not null order quantity
     *
     * @return void
     */
    public function testChecksInvoiceIsNotRegisterWhenItsTotalQuantityIsNull()
    {

        $modelOrderMock = $this->getModelMock('sales/order', ['getState', 'getPayment', 'canInvoice']);

        $modelOrderMock->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);


        $modelInvoiceMock = $this->getModelMock('sales/order_invoice', ['getTotalQty', 'register']);

        $modelInvoiceMock->expects($this->once())
            ->method('getTotalQty')
            ->willReturn(0);

        $modelInvoiceMock->expects($this->never())
            ->method('register');

        $this->replaceByMock('model', 'sales/order_invoice', $modelInvoiceMock);


        $modelStandardMock = $this->getModelMock('oggetto_payment/standard', [
            '_saveTransactionWithAddedInvoice', 'prepareInvoice', 'checkNewOrder'
        ]);

        $modelStandardMock->expects($this->once())
            ->method('checkNewOrder')
            ->with($modelOrderMock)
            ->willReturn(true);

        $modelStandardMock->expects($this->never())
            ->method('_saveTransactionWithAddedInvoice');

        $modelStandardMock->expects($this->once())
            ->method('prepareInvoice')
            ->willReturn($modelInvoiceMock);

        $this->replaceByMock('model', 'oggetto_payment/standard', $modelStandardMock);

        $modelStandardMock->handlePlacedOrder($modelOrderMock);
    }

    /**
     * Returns prepared invoice from sales/service_order model
     *
     * @return void
     */
    public function testReturnsPreparedInvoiceFromServiceOrder()
    {
        $order   = new Mage_Sales_Model_Order;
        $invoice = new Mage_Sales_Model_Order_Invoice;

        $modelServiceOrderMock = $this->getModelMockBuilder('sales/service_order')
            ->setMethods(['prepareInvoice'])
            ->setConstructorArgs([$order])
            ->getMock();

        $modelServiceOrderMock->expects($this->once())
            ->method('prepareInvoice')
            ->willReturn($invoice);

        $this->replaceByMock('model', 'sales/service_order', $modelServiceOrderMock);

        $this->assertEquals($invoice, $this->_oggettoPayment->prepareInvoice($order));
    }

    /**
     * Return true if order is new and payment method is Oggetto Payment Standard Method
     *
     * @return void
     */
    public function testReturnTrueIfOrderIsNewAndPaymentMethodIsOggettoStandard()
    {
        $method = 'oggetto_payment';

        $modelOrderPaymentMock = $this->getModelMock('sales/order_payment', ['getMethod']);

        $modelOrderPaymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);

        $this->replaceByMock('model', 'sales/order_payment', $modelOrderPaymentMock);


        $modelOrderMock = $this->getModelMock('sales/order', ['getState', 'getPayment', 'canInvoice']);

        $modelOrderMock->expects($this->once())
            ->method('getState')
            ->willReturn(Mage_Sales_Model_Order::STATE_NEW);

        $modelOrderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($modelOrderPaymentMock);

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);


        $this->assertTrue($this->_oggettoPayment->checkNewOrder($modelOrderMock));
    }

    /**
     * Return true if order is not new and payment method is Oggetto Payment Standard Method not checking
     *
     * @return void
     */
    public function testReturnFalseIfOrderIsNotNewAndPaymentMethodIsOggettoStandardNotChecking()
    {

        $modelOrderPaymentMock = $this->getModelMock('sales/order_payment', ['getMethod']);

        $modelOrderPaymentMock->expects($this->never())
            ->method('getMethod');

        $this->replaceByMock('model', 'sales/order_payment', $modelOrderPaymentMock);


        $modelOrderMock = $this->getModelMock('sales/order', ['getState', 'getPayment', 'canInvoice']);

        $modelOrderMock->expects($this->once())
            ->method('getState')
            ->willReturn(Mage_Sales_Model_Order::STATE_PROCESSING);

        $modelOrderMock->expects($this->never())
            ->method('getPayment');

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);


        $this->assertFalse($this->_oggettoPayment->checkNewOrder($modelOrderMock));
    }
}
