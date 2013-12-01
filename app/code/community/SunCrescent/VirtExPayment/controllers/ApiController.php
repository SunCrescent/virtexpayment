<?php
/**
 * SunCrescent VirtExPayment Extension
 * Copyright (C) 2013  Stefan Graf
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class SunCrescent_VirtExPayment_ApiController extends Mage_Core_Controller_Front_Action
{
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function indexAction()
    {
        $orderIncrementId = $this->_getSession()->getLastRealOrderId();
        if ($orderIncrementId) {
            // show payment form
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_redirect('/');
        }
    }

    /**
     * Called to update payment page status and remaining time.
     */
    public function statusAction()
    {
        $jsonData = '{}';
        $helper = Mage::helper('virtexpayment');

        if ($helper->hasOrderConfiguration()) {
            $configuration = $helper->getOrderConfiguration();
            $merchantKey = $configuration['merchant_key'];
            $orderKey = $configuration['order_key'];
            $jsonData = Mage::helper('virtexpayment/api')->callMerchantInvoiceBalanceCheck($merchantKey, $orderKey);
        }

        // update order state according to the status received
        $this->_updateOrderStatus($jsonData);

        // return status info to javascript ajax
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    protected function _updateOrderStatus($jsonData)
    {
        $data = json_decode($jsonData, true);
        error_log("update order status with data: " . print_r($data, true));
        if (is_array($data) && isset($data['status'])) {
            switch ($data['status']) {
                case 'paid':
                    $this->_updateOrder();
                    break;
                case 'expired':
                    $this->_cancelOrder();
                    break;
            }
        }
    }

    /**
     * Called after payment is received to set status to pending_payment until IPN confirmation comes in.
     */
    protected function _updateOrder()
    {
        $orderIncrementId = $this->_getSession()->getLastRealOrderId();
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW) {
            $comment = Mage::helper('virtexpayment')->__('Payment received, waiting for confirmation');

            // update order status
            $order->setData('state', Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
            $order->addStatusHistoryComment($comment, true)->setIsCustomerNotified(true);
            $order->save();

            // notify customer
            $order->sendOrderUpdateEmail(true, $comment);
        }
    }

    /**
     * Called if payment expires to set order status to canceled.
     */
    protected function _cancelOrder()
    {
        if (Mage::getModel('virtexpayment/method')->getConfigData('autocancel')) {
            $orderIncrementId = $this->_getSession()->getLastRealOrderId();
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

            if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW) {
                $comment = Mage::helper('virtexpayment')->__('Order canceled because payment was not received');

                // cancel order
                $order->registerCancellation();
                $order->addStatusHistoryComment($comment, true)->setIsCustomerNotified(true);
                $order->save();

                // notify customer
                $order->sendOrderUpdateEmail(true, $comment);
            }
        }
    }

    /**
     * Called by VirtEx to notify us about a payment.
     */
    public function ipnAction()
    {
        // -- IPN CALL VALIDATION DISABLED FOR NOW UNTIL API WORKS --
        return;

        // json request data from post
        $data = json_decode($this->getRequest()->getRawBody(), true);

        if (!$data) {
            error_log("json invalid");
            return;
        }

        // find matching order payment data
        $orderPayment = $this->_getOrderPayment($data['order_key']);

        if (!$orderPayment) {
            error_log("payment not found");
            return;
        }

        // get order data
        $orderData = unserialize($orderPayment->getAdditionalData());

        if (is_array($orderData) && isset($orderData['btc_total'])) {

            // check if amount matches
            if ($data['btc_received'] >= $orderData['btc_total']) {

                $secretKey = Mage::helper('core')->decrypt(Mage::getModel('virtexpayment/method')->getConfigData('secret_key'));
                $data['secret_key'] = $secretKey;

                // error_log("confirming ipn with data: " . print_r($data, true));

                $response = Mage::helper('virtexpayment/api')->callMerchantConfirmIpn($data);

                // error_log("response: " . print_r($response, true));

                // validate ipn response
                if (is_array($response) && isset($response['status']) && $response['status'] != 'error') {

                    // load order based on payment
                    $order = Mage::getModel('sales/order')->load($orderPayment->getParentId());

                    if ($order->getId()) {
                        $this->_confirmOrder($order);
                    }
                }
            }
        }
    }

    protected function _getOrderPayment($orderKey)
    {
        // find order by using the order_key that was saved in the payment table earlier
        $collection = Mage::getModel('sales/order_payment')
            ->getCollection()
            ->addAttributeToFilter('last_trans_id', array('eq' => $orderKey));

        if ($collection->count() == 1) {
            return $collection->getFirstItem();
        }

        return false;
    }

    /**
     * Called after IPN is validated to set status to processing and send invoice.
     * @param Mage_Sales_Model_Order $order
     */
    protected function _confirmOrder($order)
    {
        if ($order->getState() != Mage_Sales_Model_Order::STATE_PROCESSING) {
            $comment = Mage::helper('virtexpayment')->__('Payment confirmed');

            // update order status
            $order->setData('state', Mage_Sales_Model_Order::STATE_PROCESSING);
            $order->addStatusHistoryComment($comment, true)->setIsCustomerNotified(true);
            $order->save();

            // notify customer
            $order->sendOrderUpdateEmail(true, $comment);

            // email invoice
            if (Mage::getModel('virtexpayment/method')->getConfigData('autoinvoice')) {
                if ($order->canInvoice()) {
                    $invoice = $order->prepareInvoice();
                    $invoice->register();
                    $invoice->pay();
                    Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();

                    $invoice->sendEmail(true, '');
                }
            }
        }
    }
}