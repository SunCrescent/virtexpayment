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

class SunCrescent_VirtExPayment_Helper_Api extends Mage_Core_Helper_Abstract
{
    const POST_URL_PURCHASE = 'https://www.cavirtex.com/merchant_purchase';
    const POST_URL_IPN_CHECK = 'https://www.cavirtex.com/merchant_confirm_ipn';
    const GET_URL_BALANCE_CHECK = 'https://www.cavirtex.com/merchant_invoice_balance_check';

    public function callMerchantPurchase($merchantKey, $amount)
    {
        $data = array(
            'name' => 'payment',
            'price' => $amount,
            'shipping_required' => 0,
            'format' => 'json',
        );

        // add merchant key to post url
        $url = self::POST_URL_PURCHASE . '/' . $merchantKey;

        $client = Mage_HTTP_Client::getInstance();
        $client->post($url, $data);

        // get data returned from virtex
        $body = $client->getBody();
        if (!$body) {
            throw new Exception('VirtEx merchant API call failed. Please try again later.');
        }

        // decode json
        $result = json_decode($body, true);
        if (!$result) {
            throw new Exception('VirtEx merchant API returned invalid data. Please try again later.');
        }

        return $result;
    }

    public function callMerchantInvoiceBalanceCheck($merchantKey, $orderKey)
    {
        // add merchant and order keys to url
        $url = self::GET_URL_BALANCE_CHECK . '?merchant_key=' . urlencode($merchantKey) . '&order_key=' . urlencode($orderKey);

        $client = Mage_HTTP_Client::getInstance();
        $client->get($url);

        // get data returned from virtex
        $body = $client->getBody();

        if (!$body) {
            throw new Exception('VirtEx merchant API call failed. Please try again later.');
        }

        return $body;
    }

    public function callMerchantConfirmIpn($data)
    {
        // add merchant key to post url
        $url = self::POST_URL_IPN_CHECK;

        $client = Mage_HTTP_Client::getInstance();
        $client->post($url, $data);

        // get data returned from virtex
        $body = $client->getBody();
        if (!$body) {
            throw new Exception('VirtEx merchant API call failed. Please try again later.');
        }

        // decode json
        $result = json_decode($body, true);
        if (!$result) {
            throw new Exception('VirtEx merchant API returned invalid data. Please try again later.');
        }

        return $result;
    }
}