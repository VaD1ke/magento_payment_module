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
 * Block test class for displaying redirect form
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage Test
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_Test_Block_Redirect extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Oggetto Payment Redirect block
     *
     * @var Oggetto_Payment_Block_Redirect
     */
    protected $_redirect;

    /**
     * Set up initial variables
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_redirect = new Oggetto_Payment_Block_Redirect;
    }

    /**
     * Returns fields for request form with established parameters and values from Oggetto Payment Helper Data
     *
     * @return void
     */
    public function testReturnsFieldsForRequestFormWithOrderAndEstablishedParametersAndValuesFromHelper()
    {
        $hashedSignature = 'hashedSignature';
        $fields = ['test' => 'test'];

        $order = new Mage_Sales_Model_Order;

        $helperDataMock = $this->getHelperMock('oggetto_payment', [
            'getFormFieldsFromOrder', 'getHashedSignature', 'getOrder'
        ]);

        $helperDataMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);

        $helperDataMock->expects($this->once())
            ->method('getFormFieldsFromOrder')
            ->with($order)
            ->willReturn($fields);

        $helperDataMock->expects($this->once())
            ->method('getHashedSignature')
            ->with($fields)
            ->willReturn($hashedSignature);

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);


        $fields['hash'] = $hashedSignature;


        $this->assertEquals($fields, $this->_redirect->getFields());
    }

    /**
     * Return submit URL from Oggetto Payment Helper Data
     *
     * @return void
     */
    public function testReturnsSubmitUrlFromHelperData()
    {
        $url = 'testUrl';

        $helperDataMock = $this->getHelperMock('oggetto_payment', ['getSubmitUrl']);

        $helperDataMock->expects($this->once())
            ->method('getSubmitUrl')
            ->willReturn($url);

        $this->replaceByMock('helper', 'oggetto_payment', $helperDataMock);


        $this->assertEquals($url, $this->_redirect->getSubmitUrl());
    }
}
