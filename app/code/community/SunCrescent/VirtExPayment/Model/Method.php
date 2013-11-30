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

class SunCrescent_VirtExPayment_Model_Method extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'virtexpayment';

    protected $_canUseInternal = false;
    protected $_isInitializeNeeded = true;
    protected $_canManageRecurringProfiles = false;

    /**
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (strtoupper($currencyCode) != "CAD") {
            return false;
        }
        return parent::canUseForCurrency($currencyCode);
    }

    /**
     * Instantiate state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object $stateObject
     * @return SunCrescent_VirtExPayment_Model_Method
     */
    public function initialize($paymentAction, $stateObject)
    {
        Mage::helper('virtexpayment')->resetOrderConfiguration();
        $state = Mage_Sales_Model_Order::STATE_NEW;
        $stateObject->setState($state);
        $stateObject->setStatus('pending');
        $stateObject->setIsNotified(false);
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('virtex/api', array('_secure' => true));
    }
}