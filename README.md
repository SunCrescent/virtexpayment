VirtEx Payment Magento Extension
================================

Magento Virtex Payment Gateway Module. Allows Magento website to accept payments in Bitcoin currency. Mostly functionnal needs to be tested (a lot) more. Currently tested on Magento 1.7 and 1.8. Its very hard to test the module since the VirtEx API is very unstable at the moment (changes are made without notifying developers and documentation isn't updated).

* This extension requires a merchant account with VirtEx http://www.cavirtex.com/
* This extension will only work with Magento stores using Canadian Dollars as base currency.

How does it work?
-----------------
Prices for your products remain in fiat currency (canadian dollars only). During checkout, the order's balance in $CA is converted to Bitcoins using the curent exchange rate. Your customers will be instructed to send a BTC payment to a special receiving address linked to your merchant account. Once the payment is received, Magento will complete the checkout process.

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
6. If its not already the case, set your "Base Currency" to Canadian Dollars under the Currency Setup tab.
7. Locate the "VirExt Merchant API" payment module in the Payment Methods tab.
8. Configure your merchant API key and secret key.
9. Login to your VirtEx merchant admin and set the IPN url to <em>https://www.domain.com<strong>/virtex/api/ipn</strong></em><br/>(use http://... if you don't have an SSL setup)




Known Issues
------------
- The VirEx API seems to have some rounding issues with very low amounts of BTC (less than 0.0001) which impacts payments made through the API.
- It seems like the API's IPN validation always fails at the moment (meaning IPN is not used at the moment even tough it is implemented)
