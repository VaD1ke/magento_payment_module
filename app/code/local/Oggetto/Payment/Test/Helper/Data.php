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
 * Helper data test class
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage Test
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Helper Data
     *
     * @var Oggetto_Payment_Helper_Data
     */
    protected $_helper;

    /**
     * Set Up initial variables
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_helper = Mage::helper('oggetto_payment');
    }


    /**
     * Returns fields for request form with established parameters and values
     *
     * @param array $fields  form fields
     * @param array $methods helper data methods
     *
     * @return void
     *
     * @dataProvider dataProvider
     */
    public function testReturnsFieldsForRequestFormFromOrderWithEstablishedParametersAndValues($fields, $methods)
    {
        $modelOrderMock = $this->getModelMock('sales/order', ['getIncrementId']);

        $modelOrderMock->expects($this->once())
            ->method('getIncrementId')
            ->willReturn($fields['order_id']);

        $this->replaceByMock('model', 'sales/order', $modelOrderMock);


        $helperDataMock = $this->getHelperMock('oggetto_payment', $methods);

        foreach ($methods as $i => $method) {
            $helperDataMock->expects($this->at($i))
                ->method($method)
                ->willReturn($this->expected()->$method());
        }

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);


        $this->assertEquals($fields, $helperDataMock->getFormFieldsFromOrder($modelOrderMock));
    }

    /**
     * Return hashed signature from input data
     *
     * @return void
     */
    public function testReturnsHashedSignatureFromInputData()
    {
        $fields            = $this->expected()->getFields();
        $signature         = $this->expected()->getSignature();
        $signWithSecretKey = $this->expected()->getSignatureWithSecretKey();
        $hashedSignature   = md5($signature);


        $helperDataMock = $this->getHelperMock('oggetto_payment', [
            'generateSignature', 'getApiSecretKey', 'getHash'
        ]);

        $helperDataMock->expects($this->once())
            ->method('generateSignature')
            ->with($fields)
            ->willReturn($signature);

        $helperDataMock->expects($this->once())
            ->method('getApiSecretKey')
            ->willReturn($this->expected()->getSecretKey());

         $helperDataMock->expects($this->once())
             ->method('getHash')
             ->with($signWithSecretKey)
             ->willReturn($hashedSignature);

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);

        $this->assertEquals($hashedSignature, $helperDataMock->getHashedSignature($fields));
    }

    /**
     * Return generated signature from input data
     *
     * @return void
     */
    public function testReturnsGeneratedSignatureFromInputData()
    {
        $data = [
            'z' => 'qwe',
            'q' => 'asd',
            'a' => 'zxc'
        ];

        $signature = 'a:zxc|q:asd|z:qwe';

        $this->assertEquals($signature, $this->_helper->generateSignature($data));
    }

    /**
     * Return Oggetto Payment API secret key from store config
     *
     * @return void
     *
     * @loadFixture
     */
    public function testReturnsOggettoPaymentApiSecretKeyFromStoreConfig()
    {
        $secretKey = 'testKey';

        $this->assertEquals($secretKey, $this->_helper->getApiSecretKey());
    }

    /**
     * Return Oggetto Payment Redirect URL with secure option
     *
     * @return void
     */
    public function testReturnsOggettoPaymentRedirectUrlWithSecureOption()
    {
        $url = 'testUrl';

        $coreUrl = $this->getModelMock('core/url', ['getUrl']);

        $coreUrl->expects($this->once())
            ->method('getUrl')
            ->with('oggetto_payment/payment/redirect', ['_secure' => true])
            ->willReturn($url);

        $this->replaceByMock('model', 'core/url', $coreUrl);

        $this->assertEquals($url, $this->_helper->getRedirectSecureUrl());
    }

    /**
     * Return Oggetto Payment API submit URL from store config
     *
     * @return void
     *
     * @loadFixture
     */
    public function testReturnsOggettoPaymentApiSubmitUrlFromStoreConfig()
    {
        $url = 'testUrl';

        $this->assertEquals($url, $this->_helper->getSubmitUrl());
    }

    /**
     * Return Oggetto Payment API success URL from store config
     *
     * @return void
     */
    public function testReturnsOggettoPaymentApiSuccessUrl()
    {
        $url = 'testUrl';

        $this->replaceCoreUrlModelMockForGettingUrl('checkout/onepage/success', $url);

        $this->assertEquals($url, $this->_helper->getSuccessUrl());
    }

    /**
     * Return Oggetto Payment API error URL
     *
     * @return void
     */
    public function testReturnsOggettoPaymentApiErrorUrl()
    {
        $url = 'testUrl';

        $this->replaceCoreUrlModelMockForGettingUrl('oggetto_payment/payment/cancel', $url);

        $this->assertEquals($url, $this->_helper->getErrorUrl());
    }

    /**
     * Return Oggetto Payment API report URL
     *
     * @return void
     */
    public function testReturnsOggettoPaymentApiReportUrl()
    {
        $url = 'testUrl';

        $this->replaceCoreUrlModelMockForGettingUrl('oggetto_payment/payment/response', $url);

        $this->assertEquals($url, $this->_helper->getPaymentReportUrl());
    }

    /**
     * Return Oggetto Payment
     *
     * @return void
     */
    public function testReturnsOggettoPaymentApiTotal()
    {
        $testTotal = 123.45;
        $testStr = '123.45';

        $modelSalesOrderMock = $this->getModelMock('sales/order', ['getGrandTotal']);

        $modelSalesOrderMock->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($testTotal);

        $this->replaceByMock('model', 'sales/order', $modelSalesOrderMock);

        $helperDataMock = $this->getHelperMock('oggetto_payment', [
            'convertPriceFromFloatToCommaFormat'
        ]);

        $helperDataMock->order = $modelSalesOrderMock;


        $helperDataMock->expects($this->once())
            ->method('convertPriceFromFloatToCommaFormat')
            ->with($testTotal)
            ->willReturn($testStr);

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);


        $this->assertEquals($testStr, $helperDataMock->getTotal());
    }

    /**
     * Return MD5 hash from input string
     *
     * @return void
     */
    public function testReturnsHashFromInputString()
    {
        $testStr = 'qwerty';

        $this->assertEquals(md5($testStr), $this->_helper->getHash($testStr));
    }

    /**
     * Return price in format with comma from float
     *
     * @return void
     */
    public function testReturnsPriceInCommaFormatFromFloat()
    {
        $testPrice = 123.456;
        $testStr = '123,46';

        $this->assertEquals($testStr, $this->_helper->convertPriceFromFloatToCommaFormat($testPrice));
    }

    /**
     * Return price in format with comma from float
     *
     * @return void
     */
    public function testReturnsPriceFromCommaFormatToFloat()
    {
        $testPrice = 123.46;
        $testStr = '123,456';

        $this->assertEquals($testPrice, $this->_helper->convertPriceFromCommaFormatToFloat($testStr));
    }

    /**
     * Return order from sales/order model with established order id
     *
     * @return void
     */
    public function testReturnsOrderWithEstablishedOrderId()
    {
        $testOrderId = '777';

        $helperDataMock = $this->getHelperMock('oggetto_payment', ['getOrderId']);

        $helperDataMock->expects($this->once())
            ->method('getOrderId')
            ->willReturn($testOrderId);

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);


        $modelSalesOrderMock = $this->getModelMock('sales/order', ['loadByIncrementId']);

        $modelSalesOrderMock->expects($this->once())
            ->method('loadByIncrementId')
            ->with($testOrderId)
            ->willReturnSelf();

        $this->replaceByMock('model', 'sales/order', $modelSalesOrderMock);

        $this->assertEquals($modelSalesOrderMock, $helperDataMock->getOrder());
    }

    /**
     * Return order items string with comma separation
     *
     * @return void
     */
    public function testReturnsOrderItemsStringWithCommaSeparation()
    {
        $items   = [
            new Varien_Object(['name' => 'test1']),
            new Varien_Object(['name' => 'test2']),
            new Varien_Object(['name' => 'test3'])
        ];

        $itemStr = 'test1,test2,test3';


        $modelSalesOrderMock = $this->getModelMock('sales/order', ['getAllVisibleItems']);

        $modelSalesOrderMock->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn($items);

        $this->replaceByMock('model', 'sales/order', $modelSalesOrderMock);

        /** @var Oggetto_Payment_Helper_Data $helperData */
        $helperData = Mage::helper('oggetto_payment');

        $helperData->order = $modelSalesOrderMock;


        $this->assertEquals($itemStr, $helperData->getOrderItemsString());
    }


    /**
     * Mock and replace core/url model for getting url
     *
     * @param string $route     route
     * @param string $testValue test url value
     *
     * @return void
     */
    protected function replaceCoreUrlModelMockForGettingUrl($route, $testValue)
    {
        $coreUrl = $this->getModelMock('core/url', ['getUrl']);

        $coreUrl->expects($this->once())
            ->method('getUrl')
            ->with($route)
            ->willReturn($testValue);

        $this->replaceByMock('model', 'core/url', $coreUrl);
    }
}
