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
}
