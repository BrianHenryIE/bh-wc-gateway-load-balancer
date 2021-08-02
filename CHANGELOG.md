# Changelog

1.3.1

* Fix customer race condition: if two customers load checkout at the same time, Gateway A is presented, when Customer A completes their order, Gateway A is no longer available when Customer B clicks Place Order. 
* Use native WC_Logger
* Create releases on GitHub

1.3.0

Add settings checkbox "Include all new orders" 

1.2.2

Now hooked on payment-complete, order-created, and order-status changed, to 