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
 * Helper Data
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage Block
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Generate not hashed signature from data
     *
     * @param array $data data
     * @return string
     */
    public function generateSignature(array $data)
    {
        ksort($data);

        $signature = '';

        foreach ($data as $key => $value) {
            $signature .= $key . ':' . $value . '|';
        }

         $signature = substr($signature, 0, strlen($signature) - 1);

        return $signature;
    }

    /**
     * Get hashed string
     *
     * @param string $string string to hash
     * @return string
     */
    public function getHash($string)
    {
        return md5($string);
    }

    /**
     * Get order ID from checkout session
     *
     * @return mixed
     */
    public function getOrderId()
    {
        $checkoutSession = Mage::getSingleton('checkout/session');
        $orderId = $checkoutSession->getLastRealOrderId();
        return $orderId;
    }

    /**
     * Get order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        /** @var Mage_Sales_Model_Order $salesOrder */
        $salesOrder = Mage::getModel('sales/order');
        $order = $salesOrder->loadByIncrementId($this->getOrderId());
        return $order;
    }

    /**
     * Get order total price
     *
     * @return string
     */
    public function getTotal()
    {
        $grandTotal = $this->getOrder()->getGrandTotal();

        return $this->convertPriceFromFloatToCommaFormat($grandTotal);
    }

    /**
     * Convert price to format with comma
     *
     * @param float $price price
     * @return string
     */
    public function convertPriceFromFloatToCommaFormat($price)
    {
        $price = number_format($price, 2, ',', '');
        return $price;
    }

    /**
     * Convert price from format with comma to float
     *
     * @param string $price price
     * @return float
     */
    public function convertPriceFromCommaFormatToFloat($price)
    {
        $price = floatval(str_replace(',', '.', $price));
        return sprintf('%0.2f', $price);
    }

    /**
     * Get Oggetto Payment Api secret key from store config
     *
     * @return string
     */
    public function getApiSecretKey()
    {
        return Mage::getStoreConfig('payment/oggetto_payment/api_secret');
    }

    /**
     * Get submit URL from store config
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return Mage::getStoreConfig('payment/oggetto_payment/submit_url');
    }

    /**
     * Get success URL for request
     *
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->_getUrl('checkout/onepage/success');
    }

    /**
     * Get error URL for request
     *
     * @return string
     */
    public function getErrorUrl()
    {
        return $this->_getUrl('oggetto_payment/payment/cancel');
    }
}
