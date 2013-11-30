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

class SunCrescent_VirtExPayment_Helper_Qrcode extends Mage_Core_Helper_Abstract
{
    const MEDIA_CACHE_FOLDER = 'virtexpayment';

    protected function _getCacheFolderPath()
    {
        $folder = Mage::getBaseDir('media') . '/' . self::MEDIA_CACHE_FOLDER;
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        return $folder;
    }

    public function generateQrCode($address, $amount)
    {
        require_once('phpqrcode/qrlib.php');

        $data = "bitcoin:$address?amount=" . number_format($amount, 8, '.', '');

        $file = md5($data) . '.png';
        $path = $this->_getCacheFolderPath() . '/' . $file;

        if (!file_exists($path)) {
            QRcode::png($data, $path, QR_ECLEVEL_Q);
        }

        return $file;
    }
}