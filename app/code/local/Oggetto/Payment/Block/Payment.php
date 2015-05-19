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
 * Block for displaying questions
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage Block
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_Block_Payment extends Mage_Core_Block_Template
{
    protected $_fields;
    /**
     * Init object
     *
     * @return Oggetto_Payment_Block_Payment
     */
    public function __construct()
    {
        parent::__construct();

        $this->_fields = [
            'order_id' , 'total',
            'items'    , 'success_url',
            'error_url', 'payment_report_url',
            'hash'
        ];
    }

    public function getFields()
    {
        return $this->_fields;
    }

    public function getHashedSignature()
    {
        $helper = Mage::helper('oggetto_payment');
    }
}
