# Security Policy

## Reporting a Vulnerability

While reviewing VaahStore’s checkout flow, I noticed a pricing logic issue related to how the transaction currency is handled during purchase. 
The checkout process appears to rely on a currency value provided by the client when confirming an order.
This value is trusted by the backend without being fully validated against server-side pricing rules or the product’s base currency. 
Because of this, the final payable amount can be calculated under an unintended currency context rather than being derived strictly from server-controlled order data. 
In practice, this means a user could manipulate the currency context during checkout and complete a purchase at a lower price than intended, which could result in financial loss for merchants using the platform. 
