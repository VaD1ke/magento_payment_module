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
 * Standard payment Model
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage Model
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Unique internal payment method identifier
     * @var string
     */
    protected $_code = 'oggetto_payment';

    /**
     * Is this payment method a gateway (online auth/charge) ?
     *
     * @var bool
     */
    protected $_isGateway              = true;
    /**
     * Is initialize needed
     * @var bool
     */
    protected $_isInitializeNeeded     = true;
    /**
     * Can use this payment method in administration panel?
     * @var bool
     */
    protected $_canUseInternal         = false;
    /**
     * Is this payment method suitable for multi-shipping checkout?
     * @var bool
     */
    protected $_canUseForMultishipping = false;
    /**
     * Can capture funds online?
     * @var bool
     */
    protected $_canCapture             = true;


    /**
     * Return Order place redirect url when customer clicks on place order
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        /** @var Oggetto_Payment_Helper_Data $helper */
        $helper = Mage::helper('oggetto_payment');
        return $helper->getRedirectSecureUrl();
    }
}
