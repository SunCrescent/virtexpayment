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

class SunCrescent_VirtExPayment_Block_Form extends Mage_Core_Block_Template
{
    /**
     * @var array
     */
    protected $_configuration = null;

    public function _construct()
    {
        /** @var SunCrescent_VirtExPayment_Helper_Data $helper */
        $helper = Mage::helper('virtexpayment');

        if (!$helper->hasOrderConfiguration()) {
            $helper->initOrderConfiguration();
        }

        $this->_configuration = $helper->getOrderConfiguration();

        $this->_configuration['qrcode'] = Mage::helper('virtexpayment/qrcode')->generateQrCode($this->_configuration['btc_payment_address'], $this->_configuration['btc_total']);
    }

    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('virtexpayment/form.phtml');
        }
        return parent::_beforeToHtml();
    }

    public function isApiCallSuccessful()
    {
        return $this->_configuration !== null;
    }

    public function getMerchantKey()
    {
        return $this->_configuration['merchant_key'];
    }

    public function getOrderKey()
    {
        return $this->_configuration['order_key'];
    }

    public function getBtcAmount()
    {
        return number_format($this->_configuration['btc_total'], 8, '.', '');;
    }

    public function getBtcAddress()
    {
        return $this->_configuration['btc_payment_address'];
    }

    public function getExpiration()
    {
        return date('Y-m-d H:i:s', $this->_configuration['expiration']);
    }

    public function getExchangeRate()
    {
        return $this->_configuration['exchange_rate'];
    }

    public function getQrCode()
    {
        return $this->_configuration['qrcode'];
    }

    public function getBalanceCheckUrl()
    {
        return SunCrescent_VirtExPayment_Helper_Api::GET_URL_BALANCE_CHECK;
    }
}