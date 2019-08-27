<?php
class ControllerExtensionPaymentStripe extends Controller {

    private $secret;
    private $pub;
    private $amount = 0;
    private $currency = "EUR";
    private $customer_email = '';

    public function __construct($registry){
        parent::__construct($registry);
        $this->config = $registry->get('config');
        $this->currency = $registry->get('currency');

        if ($this->config->get('stripe_environment') == 'live'){
            $this->secret = $this->config->get('stripe_live_secret_key');
            $this->pub = $this->config->get('stripe_live_publishable_key');
        } else {
            $this->secret = $this->config->get('stripe_test_secret_key');
            $this->pub = $this->config->get('stripe_test_publishable_key');
        }

        $this->log = new Log('stripe_payments.log');

        $this->load->model('checkout/order');

        // $this->document->addScript('//js.stripe.com/v3/');
        // $this->amount = round($this->currency->format( $this->cart->getTotals_maison( ['total_numeric' => 1 ]) ,'',1,false) * 100);
        // $this->stripe_currency = strtoupper( $this->session->data['currency'] );

        // pr($this->amount);

    }

	public function index() {

		// load all language variables
		$data = $this->load->language('extension/payment/stripe');

		/* Refund modulis:
        $data['can_store_cards'] = ($this->customer->isLogged() && $this->config->get('stripe_store_cards'));
        $data['cards'] = [];

        if($this->customer->isLogged() && $this->config->get('stripe_store_cards')) {
            $data['cards'] = $this->model_extension_payment_stripe->getCards($this->customer->getId());
        }
        // */

		if ($this->request->server['HTTPS']) {
			$data['store_url'] = HTTPS_SERVER;
		} else {
			$data['store_url'] = HTTP_SERVER;
		}

		if($this->config->get('stripe_environment') == 'live') {
			$data['stripe_publishable_key'] = $this->config->get('stripe_live_publishable_key');
			$data['test_mode'] = false;
		} else {
			$data['stripe_publishable_key'] = $this->config->get('stripe_test_publishable_key');
			$data['test_mode'] = true;
		}

		// get order info
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		// get order billing country
		$this->load->model('localisation/country');
  		$country_info = $this->model_localisation_country->getCountry($order_info['payment_country_id']);

		// we will use this owner info to send Stripe from client side
		$data['billing_details'] = array(
										'billing_details' => array(
											'name' => $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'],
											'email' => $order_info['email'],
											'address' => array(
												'line1'	=> $order_info['payment_address_1'],
												'line2'	=> $order_info['payment_address_2'],
												'city'	=> $order_info['payment_city'],
												'state'	=> $order_info['payment_zone'],
												'postal_code' => $order_info['payment_postcode'],
												'country' => $country_info['iso_code_2']
											)
										)
									);

		// handles the XHR request for client side
		$data['action'] = $this->url->link('extension/payment/stripe/confirm', '', true);

		return $this->load->view('extension/payment/stripe', $data);
	}

	public function confirm(){

		$this->load->model('extension/payment/stripe');
		$json = array('error' => 'Server did not get valid request to process');

		try{

			if(!isset($this->session->data['order_id'])){
				throw new Exception("Your order seems lost in session. We did not charge your payment. Please contact administrator for more information.");
			}

			// retrieve json from POST body
			$json_str = file_get_contents('php://input');
			$json_obj = json_decode($json_str);

			// load stripe libraries
			$this->initStripe();

			// get order info
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			if(empty($order_info)){
				throw new Exception("Your order seems lost before payment. We did not charge your payment. Please contact administrator for more information.");
			}

			// Create the PaymentIntent
			if (isset($json_obj->payment_method_id)) {

				// amount to charge for the order
				$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

				// multiple by 100 to get value in cents
				$amount = $amount * 100;

				// Create the PaymentIntent
				$intent = \Stripe\PaymentIntent::create(array(
					'payment_method' => $json_obj->payment_method_id,
					'amount' => $amount,
					'currency' => strtolower($order_info['currency_code']),
					'confirmation_method' => 'manual',
					'confirm' => true,
					'description' => "Charge for Order #".$order_info['order_id'],
					'metadata' => array(
												'order_id'	=> $order_info['order_id'],
												'email'		=> $order_info['email']
											),
				));
			}

			if (isset($json_obj->payment_intent_id)) {
				$intent = \Stripe\PaymentIntent::retrieve(
					 $json_obj->payment_intent_id
				);
				$intent->confirm();
			}

			if(!empty($intent)) {
				if (($intent->status == 'requires_action' || $intent->status == 'requires_source_action') &&
				$intent->next_action->type == 'use_stripe_sdk') {
					// Tell the client to handle the action
					$json = array(
						'requires_action' => true,
						'payment_intent_client_secret' => $intent->client_secret
					);
				} else if ($intent->status == 'succeeded') {
					// The payment didn’t need any additional actions and completed!
					// Handle post-payment fulfillment

					// charge this customer and update order accordingly
					$charge_result = $this->chargeAndUpdateOrder($intent, $order_info);

					// set redirect to success or failure page as per payment charge status
					if($charge_result) {
						$json = array('success' => $this->url->link('checkout/success', '', true));
					} else {
						$json = array('error' => 'Payment could not be completed. Please try again.');
					}

				} else {
					// Invalid status
					$json = array('error' => 'Invalid PaymentIntent Status ('.$intent->status.')');
				}
			}

		} catch (\Stripe\Error\Base $e) {
			// Display error on client
			$json = array('error' => $e->getMessage());

			$this->model_extension_payment_stripe->log($e->getFile(), $e->getLine(), "Stripe Exception caught in confirm() method", $e->getMessage());

		} catch (\Exception $e) {
			$json = array('error' => $e->getMessage());

			$this->model_extension_payment_stripe->log($e->getFile(), $e->getLine(), "Exception caught in confirm() method", $e->getMessage());

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		return;
	}


	/**
	 * this method charges the source and update order accordingly
	 * @returns boolean
	 */
	private function chargeAndUpdateOrder($intent, $order_info){

		if(isset($intent->id)) {

			// insert stripe order
			$message = 'Payment Intent ID: '.$intent->id. PHP_EOL .'Status: '. $intent->status;

			$this->load->model('checkout/order');

			/* REFUND un CARDS modulis!

			# If customer is logged, but isn't registered as a customer in Stripe
			if($this->customer->isLogged() && !$this->model_extension_payment_stripe->getCustomer($this->customer->getId())) {
				$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

				if(isset($customer_info['email']) && ! empty($customer_info['email'])) {
					$stripe_customer = \Stripe\Customer::create(array(
						'email' => $customer_info['email'],
						'metadata' => array(
							'customerId' => $this->customer->getId()
						)
					));

					$this->model_extension_payment_stripe->addCustomer(
						$stripe_customer,
						$this->customer->getId(),
						$stripe_environment
					);
				}

			}

			# If customer exists we use it
			$stripe_customer = $this->model_extension_payment_stripe->getCustomer($this->customer->getId());


			# May be the customer want to save its credit card
			if($stripe_customer && ($use_existing_card == false)) {
				$stripe_charge_parameters['customer'] = $stripe_customer['stripe_customer_id'];
				$customer = \Stripe\Customer::retrieve($stripe_customer['stripe_customer_id']);
				$stripe_card = $customer->sources->create(array("source" => $this->request->post['card']));
				$stripe_charge_parameters['customer'] = $customer['id'];
				$stripe_charge_parameters['source'] = $stripe_card['id'];

				if(!!json_decode($this->request->post['saveCreditCard'])) {
					$this->model_extension_payment_stripe->addCard(
						$stripe_card,
						$this->customer->getId(),
						$stripe_environment
					);
				}
			} else {
				$stripe_charge_parameters['source'] = $this->request->post['card'];
			}

			if($use_existing_card && $stripe_customer) {
				$stripe_charge_parameters['customer'] = $stripe_customer['stripe_customer_id'];
			}

			$charge = \Stripe\Charge::create($stripe_charge_parameters);

			if(!json_decode($this->request->post['saveCreditCard']) && isset($customer) && isset($stripe_card)) {
				$customer->sources->retrieve($stripe_card['id'])->delete();
			}

			*/

			// update order statatus & addOrderHistory
			// paid will be true if the charge succeeded, or was successfully authorized for later capture.
			if($intent->status == "succeeded") {
				$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('stripe_order_success_status_id'), $message, false);
                $this->model_extension_payment_stripe->addOrder($order_info, $intent->id, $this->config->get('stripe_environment'));
			} else {
				$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('stripe_order_failed_status_id'), $message, false);
			}


			// charge completed successfully
			return true;

		} else {
			// charge could not be completed
			return false;
		}
	}

    private function initStripe() {
        $copona_path = realpath(__DIR__ . "/../../../../system/library/stripe.php");

        if(file_exists($copona_path)){
            // Copona load - just require
            require_once( $copona_path);
        } else {
            // Opencart 2.3.0.2 loader.
            $this->load->library('stripe');
        }


        //  || (isset($this->request->request['livemode']) && $this->request->request['livemode'] == "true")
        if($this->config->get('stripe_environment') == 'live') {
            $stripe_secret_key = $this->config->get('stripe_live_secret_key');
        } else {
            $stripe_secret_key = $this->config->get('stripe_test_secret_key');
        }

        if($stripe_secret_key != '' && $stripe_secret_key != null) {
            try{

                \Stripe\Stripe::setApiKey($stripe_secret_key);
                \Stripe\Stripe::setAppInfo("Stripe-Opencart-Copona", "0.0.1", "https://github.com/arnisjuraga/stripe-opencart-copona");
                $this->currencies = \Stripe\CountrySpec::retrieve("US")['supported_payment_currencies'];

            } catch(Exception $e){

                $this->log->write($e->getMessage());
                return false;
            }
            return true;
        }

        $this->load->model('extension/payment/stripe');
        $this->model_extension_payment_stripe->log(__FILE__, __LINE__, "Unable to load stripe libraries");
        throw new Exception("Unable to load stripe libraries.");
        // return false;
    }
}