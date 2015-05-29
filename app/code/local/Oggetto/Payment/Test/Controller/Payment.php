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
 * Controller test class for Payment on frontend
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage Test
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_Test_Controller_Payment extends EcomDev_PHPUnit_Test_Case_Controller
{
    /**
     * Tests redirect action checks layout rendered
     *
     * @return void
     */
    public function testRedirectActionChecksLayoutRendered()
    {
        $this->dispatch('oggetto_payment/payment/redirect');

        $this->_assertRequestsDispatchForwardRouteAndController('redirect');


        $this->assertLayoutHandleLoaded('oggetto_payment_payment_redirect');
        $this->assertLayoutRendered();

        $this->assertLayoutBlockCreated('redirect.content');
        $this->assertLayoutBlockInstanceOf('redirect.content', 'Oggetto_Payment_Block_Redirect');
        $this->assertLayoutBlockParentEquals('redirect.content', 'content');
        $this->assertLayoutBlockRendered('redirect.content');
    }

    /**
     * Test Response Action sets OK(200) HTTP Response Status if input data is valid
     *
     * @param array $post post data
     * @return void
     *
     * @dataProvider dataProvider
     */
    public function testResponseActionSetsOkStatusIfDataIsValid($post)
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPost($post);

        $modelOrderMock = $this->getModelMock('oggetto_payment/order', [
            'validate', 'setStatus', 'handle']
        );

        $modelOrderMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $modelOrderMock->expects($this->once())
            ->method('handle')
            ->with($post['status'], $post['order_id']);

        $this->replaceByMock('model', 'oggetto_payment/order', $modelOrderMock);

        $this->dispatch('oggetto_payment/payment/response');

        $this->_assertRequestsDispatchForwardRouteAndController('response');

        $this->assertResponseHttpCode(200);
    }

    /**
     * Test Response Action sets Bad Request(400) HTTP Response Status if input data is not valid
     *
     * @param array $post post data
     * @return void
     *
     * @dataProvider dataProvider
     */
    public function testResponseActionSetsBadRequestStatusIfDataIsNotValid($post)
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPost($post);


        $modelOrderMock = $this->getModelMock('oggetto_payment/order', ['validate']);

        $modelOrderMock->expects($this->once())
            ->method('validate')
            ->willReturn(false);

        $this->replaceByMock('model', 'oggetto_payment/order', $modelOrderMock);


        $this->dispatch('oggetto_payment/payment/response');

        $this->_assertRequestsDispatchForwardRouteAndController('response');

        $this->assertResponseHttpCode(400);
    }

    /**
     * Test Response Action sets Bad Request(400) HTTP Response Status if data status is not 1 or 2
     *
     * @return void
     */
    public function testResponseActionSetsBadRequestStatusIfDataStatus()
    {
        $post = ['status' => 3];

        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPost($post);


        $modelOrderMock = $this->getModelMock('oggetto_payment/order', ['validate']);

        $modelOrderMock->expects($this->never())
            ->method('validate');

        $this->replaceByMock('model', 'oggetto_payment/order', $modelOrderMock);


        $this->dispatch('oggetto_payment/payment/response');

        $this->_assertRequestsDispatchForwardRouteAndController('response');

        $this->assertResponseHttpCode(400);
    }

    /**
     * Test Response Action sets Internal Server Error(500) HTTP Response Status if was exception
     *
     * @return void
     */
    public function testResponseActionSetsInternalServerErrorStatusIfWasException()
    {
        $post = ['status' => 2];

        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPost($post);


        $modelOrderMock = $this->getModelMock('oggetto_payment/order', ['validate']);

        $modelOrderMock->expects($this->once())
            ->method('validate')
            ->willThrowException(new Exception);

        $this->replaceByMock('model', 'oggetto_payment/order', $modelOrderMock);


        $this->dispatch('oggetto_payment/payment/response');

        $this->_assertRequestsDispatchForwardRouteAndController('response');

        $this->assertResponseHttpCode(500);
    }

    /**
     * Test Cancel Action redirects with error message
     *
     * @return void
     */
    public function testCancelActionRedirectsWithErrorMessage()
    {
        $errorMessage = 'test';
        $this->getRequest()->setParam('message', $errorMessage);

        $coreSessionMock = $this->getModelMock('core/session', ['addError']);

        $coreSessionMock->expects($this->once())
            ->method('addError')
            ->with($errorMessage);

        $this->replaceByMock('singleton', 'core/session', $coreSessionMock);


        $modelOrderMock = $this->getModelMock('sales/order', [
            'getId', 'cancel', 'save'
        ]);

        $modelOrderMock->expects($this->once())
            ->method('getId')
            ->willReturn(777);

        $modelOrderMock->expects($this->once())
            ->method('cancel')
            ->willReturnSelf();

        $modelOrderMock->expects($this->once())
            ->method('save');

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);

        $modelInvoiceMock = $this->getModelMock('sales/order_invoice', ['cancel', 'save']);

        $modelInvoiceMock->expects($this->once())
            ->method('cancel')
            ->willReturnSelf();

        $modelInvoiceMock->expects($this->once())
            ->method('save');

        $this->replaceByMock('model', 'sales/order_invoice', $modelInvoiceMock);


        $modelPaymentMock = $this->getModelMock('oggetto_payment/order', ['getInvoiceFromOrder']);

        $modelPaymentMock->expects($this->once())
            ->method('getInvoiceFromOrder')
            ->with($modelOrderMock)
            ->willReturn($modelInvoiceMock);

        $this->replaceByMock('model', 'oggetto_payment/order', $modelPaymentMock);


        $helperDataMock = $this->getHelperMock('oggetto_payment', ['getOrder']);

        $helperDataMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($modelOrderMock);

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);

        $this->dispatch('oggetto_payment/payment/cancel');

        $this->_assertRequestsDispatchForwardRouteAndController('cancel');

        $this->assertRedirectTo('checkout/onepage/failure');
    }


    /**
     * Test pack for asserting Request dispatched, not forwarded, Controller module, name and action for oggetto faq
     *
     * @param string $actionName Name of action
     *
     * @return void
     */
    protected function _assertRequestsDispatchForwardRouteAndController($actionName)
    {
        $this->assertRequestDispatched();
        $this->assertRequestNotForwarded();
        $this->assertRequestControllerModule('Oggetto_Payment');
        $this->assertRequestRouteName('oggetto_payment');
        $this->assertRequestControllerName('payment');
        $this->assertRequestActionName($actionName);
    }
}
