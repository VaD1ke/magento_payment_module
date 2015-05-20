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
            'hash'     => 'qwert'
        ];

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
            'convertPriceFromFloatToCommaFormat',
            'getFormFields', 'getHashedSignature'
        ]);

        $helperDataMock->expects($this->once())
            ->method('convertPriceFromFloatToCommaFormat')
            ->with($grandTotal)
            ->willReturn($data['total']);

        $helperDataMock->expects($this->once())
            ->method('getFormFields');

        $helperDataMock->expects($this->once())
            ->method('getHashedSignature')
            ->willReturn($data['hash']);

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
        $data = ['order_id' => 777];

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
            'convertPriceFromFloatToCommaFormat',
            'getFormFields', 'getHashedSignature'
        ]);

        $helperDataMock->expects($this->once())
            ->method('convertPriceFromFloatToCommaFormat')
            ->with($grandTotal)
            ->willReturn($data['total']);

        $helperDataMock->expects($this->once())
            ->method('getFormFields');

        $helperDataMock->expects($this->once())
            ->method('getHashedSignature')
            ->willReturn($signature);

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);


        $this->assertEquals(false, $this->_modelOrder->validate($data));
    }
}
