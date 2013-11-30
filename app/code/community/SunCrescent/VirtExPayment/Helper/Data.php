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
 *
 * SunCrescent_VirtExPayment_Helper_Data
 * Save order payment data in the session.
 * Could be changed to save the data in the database.
 */
class SunCrescent_VirtExPayment_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function hasOrderConfiguration()
    {
        return $this->_getSession()->getData('virtexpayment_configuration_data') !== null;
    }

    public function getOrderConfiguration()
    {
        return $this->_getSession()->getData('virtexpayment_configuration_data');
    }

    public function resetOrderConfiguration()
    {
        return $this->_getSession()->unsetData('virtexpayment_configuration_data');
    }

    public function initOrderConfiguration()
    {
        $orderIncrementId = $this->_getSession()->getLastRealOrderId();
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        /** @var SunCrescent_VirtExPayment_Helper_Api $helper */
        $helper = Mage::helper('virtexpayment/api');

        /** @var SunCrescent_VirtExPayment_Model_Method $payment */
        $payment = Mage::getModel('virtexpayment/method');

        // call api
        $merchantKey = Mage::helper('core')->decrypt($payment->getConfigData('merchant_key'));
        $result = $helper->callMerchantPurchase($merchantKey, $order->getGrandTotal());

        if (is_array($result)) {

            // calculate actual expiration time of this invoice
            $result['expiration'] = Mage::app()->getLocale()->storeTimeStamp() + $result['time_left'];

            // fix rounding of btc_total
            error_log("we have rounded btc_total from  " . $result['btc_total'] . " to " . round($result['btc_total'], 8));
            $result['btc_total'] = round($result['btc_total'], 8);

            // generate qr code
            if ($payment->getConfigData('qrcode')) {
                $result['qrcode'] = Mage::helper('virtexpayment/qrcode')->generateQrCode($result['btc_payment_address'], $result['btc_total']);
            }

            // update order "order_key" in order payment data
            $payment = $order->getPayment();
            /** @var $payment Mage_Sales_Model_Order_Payment */
            $payment->setLastTransId($result['order_key']);
            $payment->setAdditionalData(serialize($result));
            $payment->save();

            // save un session
            $this->_getSession()->setData('virtexpayment_configuration_data', $result);
        } else {
            $this->_getSession()->setData('virtexpayment_configuration_data', null);
        }
    }

}