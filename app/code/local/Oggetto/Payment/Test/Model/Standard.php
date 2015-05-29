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
     * Authorize order, create and register invoice, save transaction
     *
     * @return void
     */
    public function testAuthorizesOrderCreateAndRegisterInvoiceAndSaveTransaction()
    {
        $order = new Mage_Sales_Model_Order;

        $modelPaymentMock = $this->getModelMock('sales/order_payment', ['getOrder']);

        $modelPaymentMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);

        $this->replaceByMock('model', 'sales/order_payment', $modelPaymentMock);


        $modelInvoiceMock = $this->getModelMock('sales/order_invoice', ['register']);

        $modelInvoiceMock->expects($this->once())
            ->method('register')
            ->willReturnSelf();

        $this->replaceByMock('model', 'sales/order_invoice', $modelInvoiceMock);


        $modelServiceOrderMock = $this->getModelMockBuilder('sales/service_order')
            ->setMethods(['prepareInvoice'])
            ->setConstructorArgs([$order])
            ->getMock();

        $modelServiceOrderMock->expects($this->once())
            ->method('prepareInvoice')
            ->willReturn($modelInvoiceMock);

        $this->replaceByMock('model', 'sales/service_order', $modelServiceOrderMock);


        $modelStandardPaymentMock = $this->getModelMock(
            'oggetto_payment/standard',
            ['_saveTransactionWithAddedInvoice']
        );

        $modelStandardPaymentMock->expects($this->once())
            ->method('_saveTransactionWithAddedInvoice')
            ->with($modelInvoiceMock);

        $this->replaceByMock('model', 'oggetto_payment/standard', $modelStandardPaymentMock);

        $modelStandardPaymentMock->authorize($modelPaymentMock);
    }
}
