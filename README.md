VirtEx Bitcoin Payment API Magento Extension
============================================

Magento Virtex Payment Gateway Module. Allows Magento website to accept payments in Bitcoin currency. Mostly functionnal needs to be tested (a lot) more. Currently tested on Magento 1.7 and 1.8. Its very hard to test the module since the VirtEx API is very unstable at the moment (changes are made without notifying developers and documentation isn't updated).

This extension requires a merchant account with VirtEx http://www.cavirtex.com/

How does it work?
-----------------
Prices for your products remain in fiat currency (canadian dollars). During checkout, the order's balance in $CA is converted to Bitcoins using the curent exchange rate. Your customers will be instructed to send a BTC payment to a special receiving address linked to your merchant account. Once the payment is received, Magento will complete the checkout process.

Features
--------
- Realtime order payment form with expiration count-down.
- QR Code generation for payment form. Customers can pay with their wallet apps which is extremely convenient
- IPN validation of total btc amount received in your account.
- Order statuses updated depending on payment status.
- Automatic invoice generation for paid orders
- Customer email notification on payment confirmation
- Fully cusomizable CSS style sheet included.
- Fully translatable (French and English translatios included).
- Tested on Magento CE 1.7, 1.8

Screenshots
-----------



<dl>
<dt>Payment Page</dt>
<dd>
<img src="http://static.suncrescent.net/sc/Image20131130_001.png"/>
</dd>
<dt>Admin Settings</dt>
<dd>
<img src="http://static.suncrescent.net/sc/Image20131130_002.png"/>
</dd>
</dl>


Installation
------------

Required information:

- Your merchant API key (found on the VirtEx merchant account page)
- Your merchant API secret (found on the VirtEx merchant account page)

Instructions:

1. Download the code (download as zip on the right -->)
2. Upload all files to your Magento installation root folder.
3. Login to your backend.
4. Reset the cache to load the extension. (System->Cache Management)
5. Go to system configuration (System->Configuration)
6. Locate the payment module under Payment Methods->VirExt Merchant API
7. Enable the module
8. Configure your API keys


Known Issues
------------
- The VirEx API seems to have some rounding issues with very low amounts (under 0.0001) of BTC which impacts payments made through the API.
- It seems like the API's IPN validation always fails at the moment.
