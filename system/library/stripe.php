<?php
// Stripe singleton



require(__DIR__ . '/stripe/Stripe.php');

// Utilities
require(__DIR__ . '/stripe/Util/AutoPagingIterator.php');
require(__DIR__ . '/stripe/Util/CaseInsensitiveArray.php');
require(__DIR__ . '/stripe/Util/LoggerInterface.php');
require(__DIR__ . '/stripe/Util/DefaultLogger.php');
require(__DIR__ . '/stripe/Util/RandomGenerator.php');
require(__DIR__ . '/stripe/Util/RequestOptions.php');
require(__DIR__ . '/stripe/Util/Set.php');
require(__DIR__ . '/stripe/Util/Util.php');

// HttpClient
require(__DIR__ . '/stripe/HttpClient/ClientInterface.php');
require(__DIR__ . '/stripe/HttpClient/CurlClient.php');

// Errors
require(__DIR__ . '/stripe/Error/Base.php');
require(__DIR__ . '/stripe/Error/Api.php');
require(__DIR__ . '/stripe/Error/ApiConnection.php');
require(__DIR__ . '/stripe/Error/Authentication.php');
require(__DIR__ . '/stripe/Error/Card.php');
require(__DIR__ . '/stripe/Error/Idempotency.php');
require(__DIR__ . '/stripe/Error/InvalidRequest.php');
require(__DIR__ . '/stripe/Error/Permission.php');
require(__DIR__ . '/stripe/Error/RateLimit.php');
require(__DIR__ . '/stripe/Error/SignatureVerification.php');

// OAuth errors
require(__DIR__ . '/stripe/Error/OAuth/OAuthBase.php');
require(__DIR__ . '/stripe/Error/OAuth/InvalidClient.php');
require(__DIR__ . '/stripe/Error/OAuth/InvalidGrant.php');
require(__DIR__ . '/stripe/Error/OAuth/InvalidRequest.php');
require(__DIR__ . '/stripe/Error/OAuth/InvalidScope.php');
require(__DIR__ . '/stripe/Error/OAuth/UnsupportedGrantType.php');
require(__DIR__ . '/stripe/Error/OAuth/UnsupportedResponseType.php');

// API operations
require(__DIR__ . '/stripe/ApiOperations/All.php');
require(__DIR__ . '/stripe/ApiOperations/Create.php');
require(__DIR__ . '/stripe/ApiOperations/Delete.php');
require(__DIR__ . '/stripe/ApiOperations/NestedResource.php');
require(__DIR__ . '/stripe/ApiOperations/Request.php');
require(__DIR__ . '/stripe/ApiOperations/Retrieve.php');
require(__DIR__ . '/stripe/ApiOperations/Update.php');

// Plumbing
require(__DIR__ . '/stripe/ApiResponse.php');
require(__DIR__ . '/stripe/StripeObject.php');
require(__DIR__ . '/stripe/ApiRequestor.php');
require(__DIR__ . '/stripe/ApiResource.php');
require(__DIR__ . '/stripe/SingletonApiResource.php');

// Stripe API Resources
require(__DIR__ . '/stripe/Account.php');
require(__DIR__ . '/stripe/AlipayAccount.php');
require(__DIR__ . '/stripe/ApplePayDomain.php');
require(__DIR__ . '/stripe/ApplicationFee.php');
require(__DIR__ . '/stripe/ApplicationFeeRefund.php');
require(__DIR__ . '/stripe/Balance.php');
require(__DIR__ . '/stripe/BalanceTransaction.php');
require(__DIR__ . '/stripe/BankAccount.php');
require(__DIR__ . '/stripe/BitcoinReceiver.php');
require(__DIR__ . '/stripe/BitcoinTransaction.php');
require(__DIR__ . '/stripe/Card.php');
require(__DIR__ . '/stripe/Charge.php');
require(__DIR__ . '/stripe/Collection.php');
require(__DIR__ . '/stripe/CountrySpec.php');
require(__DIR__ . '/stripe/Coupon.php');
require(__DIR__ . '/stripe/Customer.php');
require(__DIR__ . '/stripe/Discount.php');
require(__DIR__ . '/stripe/Dispute.php');
require(__DIR__ . '/stripe/EphemeralKey.php');
require(__DIR__ . '/stripe/Event.php');
require(__DIR__ . '/stripe/ExchangeRate.php');
require(__DIR__ . '/stripe/File.php');
require(__DIR__ . '/stripe/FileLink.php');
require(__DIR__ . '/stripe/FileUpload.php');
require(__DIR__ . '/stripe/Invoice.php');
require(__DIR__ . '/stripe/InvoiceItem.php');
require(__DIR__ . '/stripe/InvoiceLineItem.php');
require(__DIR__ . '/stripe/IssuerFraudRecord.php');
require(__DIR__ . '/stripe/Issuing/Authorization.php');
require(__DIR__ . '/stripe/Issuing/Card.php');
require(__DIR__ . '/stripe/Issuing/CardDetails.php');
require(__DIR__ . '/stripe/Issuing/Cardholder.php');
require(__DIR__ . '/stripe/Issuing/Dispute.php');
require(__DIR__ . '/stripe/Issuing/Transaction.php');
require(__DIR__ . '/stripe/LoginLink.php');
require(__DIR__ . '/stripe/Order.php');
require(__DIR__ . '/stripe/OrderItem.php');
require(__DIR__ . '/stripe/OrderReturn.php');
require(__DIR__ . '/stripe/PaymentIntent.php');
require(__DIR__ . '/stripe/Payout.php');
require(__DIR__ . '/stripe/Plan.php');
require(__DIR__ . '/stripe/Product.php');
require(__DIR__ . '/stripe/Recipient.php');
require(__DIR__ . '/stripe/RecipientTransfer.php');
require(__DIR__ . '/stripe/Refund.php');
require(__DIR__ . '/stripe/Reporting/ReportRun.php');
require(__DIR__ . '/stripe/Reporting/ReportType.php');
require(__DIR__ . '/stripe/SKU.php');
require(__DIR__ . '/stripe/Sigma/ScheduledQueryRun.php');
require(__DIR__ . '/stripe/Source.php');
require(__DIR__ . '/stripe/SourceTransaction.php');
require(__DIR__ . '/stripe/Subscription.php');
require(__DIR__ . '/stripe/SubscriptionItem.php');
require(__DIR__ . '/stripe/Terminal/ConnectionToken.php');
require(__DIR__ . '/stripe/Terminal/Location.php');
require(__DIR__ . '/stripe/Terminal/Reader.php');
require(__DIR__ . '/stripe/ThreeDSecure.php');
require(__DIR__ . '/stripe/Token.php');
require(__DIR__ . '/stripe/Topup.php');
require(__DIR__ . '/stripe/Transfer.php');
require(__DIR__ . '/stripe/TransferReversal.php');
require(__DIR__ . '/stripe/UsageRecord.php');
require(__DIR__ . '/stripe/UsageRecordSummary.php');

// OAuth
require(__DIR__ . '/stripe/OAuth.php');

// Webhooks
require(__DIR__ . '/stripe/Webhook.php');
require(__DIR__ . '/stripe/WebhookSignature.php');


class Stripe {

}
