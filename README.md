VirtEx Bitcoin Payment API Magento Extension
============================================

Magento Virtex Payment Gateway Module. Allows Magento website to accept payments in Bitcoin currency. Mostly functionnal needs to be tested (a lot) more. Currently tested on Magento 1.7 and 1.8. Its very hard to test the module since the VirtEx API is very unstable at the moment (changes are made without notifying us and doc isn't updated).

This extension requires a merchant account with VirtEx http://www.cavirtex.com/

How does it work?
-----------------
Prices for your products remain in fiat currency (canadian dollars). During checkout, the order's balance in $CA is converted to Bitcoins using the curent exchange rate. Your customers will be instructed to send a BTC payment to a special receiving address linked to your merchant account. Once the payment is received, Magento will complete the checkout process.

Features
--------
- Fully cusomizable CSS style sheet included.
- Fully translatable (french and english included).
- Realtime order payment form.
- QR Code generation for payment form. Customers can pay with their phone which is extremely convenient
- IPN validation of total btc amount received in your account.
- Order statuses updated depending on payment status.
- Automatic invoice generation for paid orders
- Customer email notification on payment confirmation
- Tested on Magento CE 1.7, 1.8

Screenshots
-----------
![alt text](http://static.suncrescent.net/sc/Image20131130_001.png)


Installation
------------

Required information:

- Your merchant API key (found on the VirtEx merchant account page)
- Your merchant API secret (found on the VirtEx merchant account page)

Instructions:

1. Download the code (download as zip)
2. Upload all files to your Magento installation.
3. Login to your backend
4. Reset the cache to load the extension
5. Under System->Payment Method->VirExt Pay


Known Issues
------------
- There appears to be some MAJOR rounding issues with low amounts of BTC int VirEx API which breaks payment modules.
- The VirtEX  API's validation functions return invalid responses for very low amounts of BTC.
- Very low balances (below 1$) are most likely to have issues.
- It seems the IPN validation always fails at the moment. (VirtExt API issue?)
