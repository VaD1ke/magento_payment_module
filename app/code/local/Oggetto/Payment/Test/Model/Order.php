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
 * Order model test class
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage Test
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_Test_Model_Order extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Model questions
     *
     * @var Oggetto_Payment_Model_Order
     */
    protected $_modelOrder = null;

    /**
     * Set up initial variables
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_modelOrder = Mage::getModel('oggetto_payment/order');
    }

    /**
     * Return true from order validation
     *
     * @return void
     */
    public function testReturnsTrueFromOrderValidation()
    {
        $data = [
            'order_id' => 777,
            'total'    => '123.45',
            'status'   => 1
        ];

        $hash = 'qwert';

        $grandTotal = 123.45;

        $modelOrderMock = $this->getModelMock('sales/order', [
            'loadByIncrementId', 'getId',
            'getGrandTotal'
        ]);

        $modelOrderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($data['order_id'])
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('getId')
            ->willReturn($data['order_id']);

        $modelOrderMock->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($grandTotal);

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);


        $helperDataMock = $this->getHelperMock('oggetto_payment', [
            'convertPriceFromFloatToCommaFormat','getHashedSignature'
        ]);

        $helperDataMock->expects($this->once())
            ->method('convertPriceFromFloatToCommaFormat')
            ->with($grandTotal)
            ->willReturn($data['total']);

        $helperDataMock->expects($this->once())
            ->method('getHashedSignature')
            ->with($data)
            ->willReturn($hash);

        $data['hash'] = $hash;

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);

        $this->assertEquals(true, $this->_modelOrder->validate($data));
    }

    /**
     * Return false from order validation when order with Id is not exist
     *
     * @return void
     */
    public function testReturnsFalseFromOrderValidationWhenOrderWithIdNotExist()
    {
        $data = [
            'order_id' => 777,
            'total'    => '123.45'
        ];

        $modelOrderMock = $this->getModelMock('sales/order', ['loadByIncrementId', 'getId']);

        $modelOrderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($data['order_id'])
            ->willReturn(new Mage_Sales_Model_Order);

        $modelOrderMock->expects($this->once())
            ->method('getId');

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);

        $this->assertEquals(false, $this->_modelOrder->validate($data));
    }

    /**
     * Return false from order validation when order total is not equal to total from request
     *
     * @return void
     */
    public function testReturnsFalseFromOrderValidationWhenOrderTotalIsNotValid()
    {
        $data = [
            'order_id' => 777,
            'total'    => '123.45'
        ];

        $grandTotal = 123.46;

        $modelOrderMock = $this->getModelMock('sales/order', [
            'loadByIncrementId', 'getId',
            'getGrandTotal'
        ]);

        $modelOrderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($data['order_id'])
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('getId')
            ->willReturn($data['order_id']);

        $modelOrderMock->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($grandTotal);

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);


        $helperDataMock = $this->getHelperMock('oggetto_payment', [
            'convertPriceFromFloatToCommaFormat'
        ]);

        $helperDataMock->expects($this->once())
            ->method('convertPriceFromFloatToCommaFormat')
            ->with($grandTotal);

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);


        $this->assertEquals(false, $this->_modelOrder->validate($data));
    }

    /**
     * Return false from order validation when order signature is not equal to signature from request
     *
     * @return void
     */
    public function testReturnsFalseFromOrderValidationWhenOrderSignatureIsNotValid()
    {
        $data = [
            'order_id' => 777,
            'total'    => '123.45',
            'status'   => 1,
            'hash'     => 'signature1'
        ];

        $grandTotal = 123.45;
        $signature = 'signature2';

        $modelOrderMock = $this->getModelMock('sales/order', [
            'loadByIncrementId', 'getId',
            'getGrandTotal'
        ]);

        $modelOrderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($data['order_id'])
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('getId')
            ->willReturn($data['order_id']);

        $modelOrderMock->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($grandTotal);

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);


        $helperDataMock = $this->getHelperMock('oggetto_payment', [
            'convertPriceFromFloatToCommaFormat', 'getHashedSignature'
        ]);

        $helperDataMock->expects($this->once())
            ->method('convertPriceFromFloatToCommaFormat')
            ->with($grandTotal)
            ->willReturn($data['total']);
        $helperDataMock->expects($this->once())
            ->method('getHashedSignature')
            ->willReturn($signature);

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);


        $this->assertEquals(false, $this->_modelOrder->validate($data));
    }

    /**
     * Set order state and set email with can capture invoice and status equals one
     *
     * @return void
     */
    public function testSetsOrderStateAndSetEmailWithCanCaptureInvoiceAndStatusEqualsOne()
    {
        $status = '1';
        $orderId = '777';

        $modelInvoiceMock = $this->getModelMock('sales/order_invoice', [
            'canCapture', 'capture', 'save'
        ]);

        $modelInvoiceMock->expects($this->once())
            ->method('canCapture')
            ->willReturn(true);

        $modelInvoiceMock->expects($this->once())
            ->method('capture')
            ->willReturnSelf();

        $modelInvoiceMock->expects($this->once())
            ->method('save');

        $this->replaceByMock('model', 'sales/order_invoice', $modelInvoiceMock);

        $modelOrderMock = $this->_getOrderModelMockWithSettingParamsAndSaving($orderId);

        $modelPaymentOrderMock = $this->
                _getOggettoPaymentOrderModelMockForGettingInvoiceFromOrder($modelOrderMock, $modelInvoiceMock);


        $modelPaymentOrderMock->handle($status, $orderId);
    }

    /**
     * Set order state and set email without can capture invoice and status equals one
     *
     * @return void
     */
    public function testSetsOrderStateAndSetEmailWithoutCanCaptureInvoiceAndStatusEqualsOne()
    {
        $status = '1';
        $orderId = '777';

        $modelInvoiceMock = $this->getModelMock('sales/order_invoice', [
            'canCapture', 'capture', 'save'
        ]);

        $modelInvoiceMock->expects($this->once())
            ->method('canCapture')
            ->willReturn(false);

        $modelInvoiceMock->expects($this->never())
            ->method('capture');

        $modelInvoiceMock->expects($this->never())
            ->method('save');

        $this->replaceByMock('model', 'sales/order_invoice', $modelInvoiceMock);


        $modelOrderMock = $this->_getOrderModelMockWithSettingParamsAndSaving($orderId);

        $modelPaymentOrderMock = $this->
                _getOggettoPaymentOrderModelMockForGettingInvoiceFromOrder($modelOrderMock, $modelInvoiceMock);


        $modelPaymentOrderMock->handle($status, $orderId);
    }

    /**
     * Set order state and cancel it and its invoice with can capture invoice and status equals two
     *
     * @return void
     */
    public function testSetsOrderStateAndCancelItAndItsInvoiceWithCanCaptureInvoiceAndStatusEqualsTwo()
    {
        $status = '2';
        $orderId = '777';

        $modelInvoiceMock = $this->_getOrderInvoiceModelMockWithCanCancelAndCancelAndSaveMethodsExpectations();

        $modelOrderMock = $this->_getOrderModelMockWithSettingStateCancelAndSaveIt($orderId);

        $modelPaymentOrderMock = $this->
                _getOggettoPaymentOrderModelMockForGettingInvoiceFromOrder($modelOrderMock, $modelInvoiceMock);


        $modelPaymentOrderMock->handle($status, $orderId);
    }

    /**
     * Set order state and cancel it and its invoice without can capture invoice and status equals two
     *
     * @return void
     */
    public function testSetsOrderStateAndCancelItWithoutCanCancelInvoiceAndStatusEqualsTwo()
    {
        $status = '2';
        $orderId = '777';

        $modelInvoiceMock = $this->_getOrderInvoiceModelMockWithNotCanCancelAndNeverCancelAndSaveMethodsExpectations();

        $modelOrderMock = $this->_getOrderModelMockWithSettingStateCancelAndSaveIt($orderId);

        $modelPaymentOrderMock = $this->
                _getOggettoPaymentOrderModelMockForGettingInvoiceFromOrder($modelOrderMock, $modelInvoiceMock);


        $modelPaymentOrderMock->handle($status, $orderId);
    }

    /**
     * Set order state and cancel it and its invoice with can capture invoice and status equals two
     *
     * @return void
     */
    public function testChecksNotCancelItWithoutCanCancelStatusAndWithCanCaptureInvoiceAndStatusEqualsTwo()
    {
        $status = '2';
        $orderId = '777';

        $modelInvoiceMock = $this->_getOrderInvoiceModelMockWithCanCancelAndCancelAndSaveMethodsExpectations();

        $modelOrderMock = $this->_getOrderModelMockWithoutCanCancelStatusAndNotSavingIt($orderId);

        $modelPaymentOrderMock = $this->
                _getOggettoPaymentOrderModelMockForGettingInvoiceFromOrder($modelOrderMock, $modelInvoiceMock);


        $modelPaymentOrderMock->handle($status, $orderId);
    }

    /**
     * Set order state and cancel it and its invoice with can capture invoice and status equals two
     *
     * @return void
     */
    public function testChecksNotCancelItWithoutCanCancelStatusAndWithoutCanCaptureInvoiceAndStatusEqualsTwo()
    {
        $status = '2';
        $orderId = '777';

        $modelInvoiceMock = $this->_getOrderInvoiceModelMockWithNotCanCancelAndNeverCancelAndSaveMethodsExpectations();

        $modelOrderMock = $this->_getOrderModelMockWithoutCanCancelStatusAndNotSavingIt($orderId);

        $modelPaymentOrderMock = $this->
                _getOggettoPaymentOrderModelMockForGettingInvoiceFromOrder($modelOrderMock, $modelInvoiceMock);

        $modelPaymentOrderMock->handle($status, $orderId);
    }

    /**
     * Return invoice from order invoice collection
     *
     * @return void
     */
    public function testReturnsInvoiceFromOrderInvoiceCollection()
    {
        $invoice = new Mage_Sales_Model_Order_Invoice;

        $resModelInvoiceCollectionMock = $this->getResourceModelMock(
            'sales/order_invoice_collection', ['getLastItem']);

        $resModelInvoiceCollectionMock->expects($this->once())
            ->method('getLastItem')
            ->willReturn($invoice);

        $this->replaceByMock('resource_model', 'sales/order_invoice_collection', $resModelInvoiceCollectionMock);


        $modelOrderMock = $this->getModelMock('sales/order', ['getInvoiceCollection']);

        $modelOrderMock->expects($this->once())
            ->method('getInvoiceCollection')
            ->willReturn($resModelInvoiceCollectionMock);

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);

        $this->assertEquals($invoice, $this->_modelOrder->getInvoiceFromOrder($modelOrderMock));
    }


    /**
     * Get sales/order_invoice Model Mock with not canCancel and never cancel and save methods expectations
     *
     * @return EcomDev_PHPUnit_Mock_Proxy
     */
    protected function _getOrderInvoiceModelMockWithNotCanCancelAndNeverCancelAndSaveMethodsExpectations()
    {
        $modelInvoiceMock = $this->getModelMock('sales/order_invoice', [
            'canCancel', 'cancel', 'save'
        ]);

        $modelInvoiceMock->expects($this->once())
            ->method('canCancel')
            ->willReturn(false);

        $modelInvoiceMock->expects($this->never())
            ->method('cancel');

        $modelInvoiceMock->expects($this->never())
            ->method('save');

        $this->replaceByMock('model', 'sales/order_invoice', $modelInvoiceMock);

        return $modelInvoiceMock;
    }

    /**
     * Get sales/order_invoice Model Mock with canCancel and cancel and save methods expectations
     *
     * @return EcomDev_PHPUnit_Mock_Proxy
     */
    protected function _getOrderInvoiceModelMockWithCanCancelAndCancelAndSaveMethodsExpectations()
    {
        $modelInvoiceMock = $this->getModelMock('sales/order_invoice', [
            'canCancel', 'cancel', 'save'
        ]);

        $modelInvoiceMock->expects($this->once())
            ->method('canCancel')
            ->willReturn(true);

        $modelInvoiceMock->expects($this->once())
            ->method('cancel')
            ->willReturnSelf();

        $modelInvoiceMock->expects($this->once())
            ->method('save');

        $this->replaceByMock('model', 'sales/order_invoice', $modelInvoiceMock);

        return $modelInvoiceMock;
    }

    /**
     * Return mocked Oggetto Payment Model for getting invoice from order
     *
     * @param EcomDev_PHPUnit_Mock_Proxy $order   Order mock
     * @param EcomDev_PHPUnit_Mock_Proxy $invoice Invoice mock
     *
     * @return EcomDev_PHPUnit_Mock_Proxy
     */
    protected function _getOggettoPaymentOrderModelMockForGettingInvoiceFromOrder($order, $invoice)
    {
        $modelPaymentOrderMock = $this->getModelMock('oggetto_payment/order', ['getInvoiceFromOrder']);

        $modelPaymentOrderMock->expects($this->once())
            ->method('getInvoiceFromOrder')
            ->with($order)
            ->willReturn($invoice);

        $this->replaceByMock('model', 'oggetto_payment/order', $modelPaymentOrderMock);

        return $modelPaymentOrderMock;
    }

    /**
     * Mock and replace sales/order model with setting parameters and saving itself
     *
     * @param string $orderId Order ID
     *
     * @return EcomDev_PHPUnit_Mock_Proxy
     */
    protected function _getOrderModelMockWithSettingParamsAndSaving($orderId)
    {
        $modelOrderMock = $this->getModelMock('sales/order', [
            'loadByIncrementId', 'setState', 'setStatus',
            'sendNewOrderEmail', 'setEmailSent', 'save'
        ]);

        $modelOrderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($orderId)
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('setState')
            ->with(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.')
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('setStatus')
            ->with('processing')
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('sendNewOrderEmail')
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('setEmailSent')
            ->with(true);

        $modelOrderMock->expects($this->once())
            ->method('save');

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);

        return $modelOrderMock;
    }

    /**
     * Mock and replace sales/order model with setting parameters and cancel it
     *
     * @param string $orderId Order ID
     *
     * @return EcomDev_PHPUnit_Mock_Proxy
     */
    protected function _getOrderModelMockWithSettingStateCancelAndSaveIt($orderId)
    {
        $modelOrderMock = $this->getModelMock('sales/order', [
            'loadByIncrementId', 'canCancel',
            'cancel', 'setState', 'save'
        ]);

        $modelOrderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($orderId)
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('canCancel')
            ->willReturn(true);

        $modelOrderMock->expects($this->once())
            ->method('setState')
            ->with(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.')
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('cancel')
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('save');


        $this->replaceByMock('model', 'sales/order', $modelOrderMock);
        return $modelOrderMock;
    }

    /**
     * Mock and replace sales/order model with setting parameters and cancel it
     *
     * @param string $orderId Order ID
     *
     * @return EcomDev_PHPUnit_Mock_Proxy
     */
    protected function _getOrderModelMockWithoutCanCancelStatusAndNotSavingIt($orderId)
    {
        $modelOrderMock = $this->getModelMock('sales/order', [
            'loadByIncrementId', 'canCancel',
            'cancel', 'setState', 'save'
        ]);

        $modelOrderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($orderId)
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('canCancel')
            ->willReturn(false);

        $modelOrderMock->expects($this->never())
            ->method('cancel');

        $modelOrderMock->expects($this->never())
            ->method('save');

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);

        return $modelOrderMock;
    }
}
