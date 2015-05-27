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
     * Set order state and set email with and status equals one
     *
     * @return void
     */
    public function testSetsOrderStateAndSetEmailWithStatusEqualsOne()
    {
        $status     = '1';
        $orderId    = '777';
        $grandTotal = '123.45';


        $modelPaymentMock = $this->getModelMock('sales/order_payment', ['registerCaptureNotification']);

        $modelPaymentMock->expects($this->once())
            ->method('registerCaptureNotification')
            ->with($grandTotal);

        $this->replaceByMock('model', 'sales/order_payment', $modelPaymentMock);

        $this->_mockOrderModelWithGettingPaymentAndGrandTotalAndSettingParams($orderId, $modelPaymentMock, $grandTotal);


        $this->_modelOrder->handle($status, $orderId);
    }

    /**
     * Set order state and cancel it  with can capture order and status equals two
     *
     * @return void
     */
    public function testSetsOrderStateAndCancelItWithCanCaptureOrderAndStatusEqualsTwo()
    {
        $status = '2';
        $orderId = '777';

        $this->_mockOrderModelWithSettingStateCancelAndSaveIt($orderId);

        $this->_modelOrder->handle($status, $orderId);
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

        $this->_mockOrderModelWithoutCanCancelStatusAndNotSavingIt($orderId);

        $this->_modelOrder->handle($status, $orderId);
    }

    /**
     * Return rounded amount in price format
     *
     * @return void
     */
    public function testReturnsRoundedAmountInPriceFormat()
    {
        $amountInitial   = 123.456;
        $amountFormatted = '123.46';

        $this->assertEquals($amountFormatted, $this->_modelOrder->formatAmount($amountInitial));
    }


    /**
     * Mock and replace sales/order model with getting payment and grand total setting parameters and saving itself
     *
     * @param string                     $orderId Order ID
     * @param EcomDev_PHPUnit_Mock_Proxy $payment Payment mock
     * @param string                     $total   Order grand total
     *
     * @return void
     */
    protected function _mockOrderModelWithGettingPaymentAndGrandTotalAndSettingParams($orderId, $payment, $total)
    {
        $modelOrderMock = $this->getModelMock('sales/order', [
            'loadByIncrementId', 'setState', 'setStatus',
            'sendNewOrderEmail', 'setEmailSent', 'save',
            'getPayment', 'getGrandTotal'
        ]);

        $modelOrderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($orderId)
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);

        $modelOrderMock->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($total);

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
    }

    /**
     * Mock and replace sales/order model with setting parameters and cancel it
     *
     * @param string $orderId Order ID
     *
     * @return void
     */
    protected function _mockOrderModelWithSettingStateCancelAndSaveIt($orderId)
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
    }

    /**
     * Mock and replace sales/order model with setting parameters and cancel it
     *
     * @param string $orderId Order ID
     *
     * @return void
     */
    protected function _mockOrderModelWithoutCanCancelStatusAndNotSavingIt($orderId)
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
    }
}
