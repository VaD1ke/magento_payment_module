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
 * Event observer test class
 *
 * @category   Oggetto
 * @package    Oggetto_Payment
 * @subpackage Test
 * @author     Vladislav Slesarenko <vslesarenko@oggettoweb.com>
 */
class Oggetto_Payment_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Sets transaction objects and register invoice with canInvoice Order status and not null order quantity
     *
     * @return void
     */
    public function testSetsTransactionObjectsAndRegisterInvoiceWithCanInvoiceOrderStatusAndNotNullQty()
    {
        $order = new Mage_Sales_Model_Order;

        $observer = $this->_createEventObserverWithOrder($order);

        $modelStandardMock = $this->getModelMock('oggetto_payment/standard', ['handlePlacedOrder']);

        $modelStandardMock->expects($this->once())
            ->method('handlePlacedOrder')
            ->with($order);

        $this->replaceByMock('model', 'oggetto_payment/standard', $modelStandardMock);

        $modelObserver = new Oggetto_Payment_Model_Observer;

        $modelObserver->saveOrderAfterPlace($observer);
    }


    /**
     * Create event observer with order
     *
     * @param Mage_Sales_Model_Order $order Order
     * @return Varien_Event_Observer
     */
    protected function _createEventObserverWithOrder($order)
    {
        $event = new Varien_Event;
        $event->setOrder($order);
        $observer = new Varien_Event_Observer;
        $observer->setEvent($event);

        return $observer;
    }
}
