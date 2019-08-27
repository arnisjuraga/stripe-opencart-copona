<?php
class ControllerExtensionPaymentStripe extends Controller {
    private $error = array();
    private $currencies = [];

    public function index() {

        // load all language variables
        $data = $this->load->language('extension/payment/stripe');
        $this->load->model('extension/payment/stripe');


        $this->load->model('setting/setting');
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $this->document->setTitle($this->language->get('heading_title'));



        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('stripe', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/payment/stripe', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }





        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/stripe', 'token=' . $this->session->data['token'], true)
        );

        $data['currencies'] = $this->currencies;

        //Start Stripe!
        $this->initStripe();

        $data['action'] = $this->url->link('extension/payment/stripe', 'token=' . $this->session->data['token'], true);

        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

        if (isset($this->request->post['stripe_environment'])) {
            $data['stripe_environment'] = $this->request->post['stripe_environment'];
        } elseif ($this->config->has('stripe_environment')) {
            $data['stripe_environment'] = $this->config->get('stripe_environment');
        } else {
            $data['stripe_environment'] = 'test';
        }


                if (isset($this->request->post['stripe_currency'])) {
                    $data['stripe_currency'] = $this->request->post['stripe_currency'];
                } elseif ($this->config->has('stripe_currency')) {
                    $data['stripe_currency'] = $this->config->get('stripe_currency');
                } else {
                    $data['stripe_currency'] = 'usd';
                }

        if (isset($this->request->post['stripe_test_publishable_key'])) {
            $data['stripe_test_publishable_key'] = $this->request->post['stripe_test_publishable_key'];
        } else if($this->config->has('stripe_test_publishable_key')){
            $data['stripe_test_publishable_key'] = $this->config->get('stripe_test_publishable_key');
        } else {
            $data['stripe_test_publishable_key'] = '';
        }

        if (isset($this->request->post['stripe_test_secret_key'])) {
            $data['stripe_test_secret_key'] = $this->request->post['stripe_test_secret_key'];
        } else if($this->config->has('stripe_test_secret_key')){
            $data['stripe_test_secret_key'] = $this->config->get('stripe_test_secret_key');
        } else {
            $data['stripe_test_secret_key'] = '';
        }

        if (isset($this->request->post['stripe_live_publishable_key'])) {
            $data['stripe_live_publishable_key'] = $this->request->post['stripe_live_publishable_key'];
        } else if($this->config->has('stripe_live_publishable_key')){
            $data['stripe_live_publishable_key'] = $this->config->get('stripe_live_publishable_key');
        } else {
            $data['stripe_live_publishable_key'] = '';
        }

        if (isset($this->request->post['stripe_live_secret_key'])) {
            $data['stripe_live_secret_key'] = $this->request->post['stripe_live_secret_key'];
        } else if($this->config->has('stripe_live_secret_key')){
            $data['stripe_live_secret_key'] = $this->config->get('stripe_live_secret_key');
        } else {
            $data['stripe_live_secret_key'] = '';
        }


        if (isset($this->request->post['stripe_store_cards'])) {
            $data['stripe_store_cards'] = $this->request->post['stripe_store_cards'];
        } elseif ($this->config->has('stripe_store_cards')) {
            $data['stripe_store_cards'] = $this->config->get('stripe_store_cards');
        } else {
            $data['stripe_store_cards'] = 0;
        }

        if (isset($this->request->post['stripe_order_success_status_id'])) {
            $data['stripe_order_success_status_id'] = $this->request->post['stripe_order_success_status_id'];
        } else if($this->config->has('stripe_order_success_status_id')){
            $data['stripe_order_success_status_id'] = $this->config->get('stripe_order_success_status_id');
        } else {
            $data['stripe_order_success_status_id'] = '';
        }

        if (isset($this->request->post['stripe_order_failed_status_id'])) {
            $data['stripe_order_failed_status_id'] = $this->request->post['stripe_order_failed_status_id'];
        } else if($this->config->has('stripe_order_failed_status_id')){
            $data['stripe_order_failed_status_id'] = $this->config->get('stripe_order_failed_status_id');
        } else {
            $data['stripe_order_failed_status_id'] = '';
        }

        if (isset($this->request->post['stripe_status'])) {
            $data['stripe_status'] = $this->request->post['stripe_status'];
        } else if($this->config->has('stripe_status')){
            $data['stripe_status'] = (int)$this->config->get('stripe_status');
        } else {
            $data['stripe_status'] = 0;
        }

        /*
            if (isset($this->request->post['stripe_order_status_id'])) {
            $data['stripe_order_status_id'] = $this->request->post['stripe_order_status_id'];
        } else {
            $data['stripe_order_status_id'] = $this->config->get('stripe_order_status_id');
        }
        */



        if (isset($this->request->post['stripe_sort_order'])) {
            $data['stripe_sort_order'] = $this->request->post['stripe_sort_order'];
        } else if($this->config->has('stripe_sort_order')){
            $data['stripe_sort_order'] = (int)$this->config->get('stripe_sort_order');
        } else {
            $data['stripe_sort_order'] = 0;
        }

        if (isset($this->request->post['stripe_debug'])) {
            $data['stripe_debug'] = $this->request->post['stripe_debug'];
        } else if($this->config->has('stripe_debug')){
            $data['stripe_debug'] = (int)$this->config->get('stripe_debug');
        } else {
            $data['stripe_debug'] = 0;
        }

        // populate errors
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->error['test_publishable_key'])) {
            $data['test_publishable_key'] = $this->error['test_publishable_key'];
        } else {
            $data['test_publishable_key'] = '';
        }

        if (isset($this->error['test_secret_key'])) {
            $data['error_test_secret_key'] = $this->error['test_secret_key'];
        } else {
            $data['error_test_secret_key'] = '';
        }

        if (isset($this->error['live_publishable_key'])) {
            $data['live_publishable_key'] = $this->error['live_publishable_key'];
        } else {
            $data['live_publishable_key'] = '';
        }

        if (isset($this->error['live_secret_key'])) {
            $data['error_live_secret_key'] = $this->error['live_secret_key'];
        } else {
            $data['error_live_secret_key'] = '';
        }

        $data['token'] = $this->session->data['token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/stripe', $data));
    }

    public function install() {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('extension/payment/stripe');

            $this->model_extension_payment_stripe->install();
        }
    }

    public function uninstall() {
        if ($this->user->hasPermission('modify', 'extension/extension')) {
            $this->load->model('extension/payment/stripe');

            $this->model_extension_payment_stripe->uninstall();
        }
    }
    public function refund() {
        $this->load->language('extension/payment/stripe');
        $this->initStripe();

        $json = array();
        $json['error'] = false;

        if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
            $this->load->model('extension/payment/stripe');
            $this->load->model('user/user');

            $stripe_order = $this->model_extension_payment_stripe->getOrder($this->request->post['order_id']);
            $user_info = $this->model_user_user->getUser($this->user->getId());


            $charges_all= \Stripe\Charge::all([
                'payment_intent' => $stripe_order['stripe_order_id'],
                // Limit the number of objects to return (the default is 10)
                'limit' => 3,
            ]);



            if($charges_all && !empty($charges_all->data[0])) {
                $data['charge'] = $charges_all->data[0];
            }

            if(!empty($data['charge']->id)) {
                try {
                    \Stripe\Refund::create(array(
                        "charge" => $data['charge']->id,
                        "amount" => $this->request->post['amount'] * 100,
                        "metadata" => array(
                            "opencart_user_username" => $user_info['username'],
                            "opencart_user_id" => $this->user->getId()
                        )
                    ));
                } catch(Exception $e){
                    $json['error'] = true;
                    $json['msg'] = 'Stripe Exception: ' . $e->getMessage();
                }
            } else {
                $json['error'] = true;
                $json['msg'] = 'Missing charge data for paymentIntent: "' . $stripe_order['stripe_order_id'] . '"';
            }



        } else {
            $json['error'] = true;
            $json['msg'] = 'Missing data';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function order() {




        if ($this->config->get('stripe_status')) {

            $this->initStripe();

            $this->load->model('extension/payment/stripe');
            $this->load->language('extension/payment/stripe');

            $data['order_id'] = $this->request->get['order_id'];





            $stripe_order = $this->model_extension_payment_stripe->getOrder($this->request->get['order_id']);



            if ($stripe_order && $this->initStripe()) {



                $data['stripe_environment'] = $stripe_order['environment'];

                //prd($stripe_order);

                // $data['charge'] = \Stripe\Charge::retrieve($stripe_order['stripe_order_id']);

                $charges_all= \Stripe\Charge::all([
                    'payment_intent' => $stripe_order['stripe_order_id'],
                    // Limit the number of objects to return (the default is 10)
                    'limit' => 3,
                ]);

                // prd(\Stripe\PaymentIntent::retrieve( $stripe_order['stripe_order_id'] ) ) ;




                $data['transaction'] = null;
                $data['charge'] = null;






                if($charges_all && !empty($charges_all->data[0])){
                    $data['charge'] = $charges_all->data[0];

                    $data['transaction'] = \Stripe\BalanceTransaction::retrieve($data['charge']['balance_transaction']);
                }


                // prd($data['charge']);




                $data['text_confirm_refund'] = $this->language->get('text_confirm_refund');
                $data['text_refund_ok'] = $this->language->get('text_refund_ok');
                $data['button_refund'] = $this->language->get('button_refund');
                $data['datetime_format'] = $this->language->get('datetime_format');

                $data['token'] = $this->request->get['token'];

                return $this->load->view('extension/payment/stripe_order', $data);
            }
        }
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/stripe')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if(isset($this->request->post['stripe_environment'])){

            if($this->request->post['stripe_environment'] == 'test'){

                if(!isset($this->request->post['stripe_test_publishable_key']) || trim($this->request->post['stripe_test_publishable_key']) == ''){
                    $this->error['test_publishable_key'] = $this->language->get('error_test_publishable_key');
                }
                if(!isset($this->request->post['stripe_test_secret_key']) || trim($this->request->post['stripe_test_secret_key']) == ''){
                    $this->error['test_secret_key'] = $this->language->get('error_test_secret_key');
                }

            } else {

                if(!isset($this->request->post['stripe_live_publishable_key']) || trim($this->request->post['stripe_live_publishable_key']) == ''){
                    $this->error['live_publishable_key'] = $this->language->get('error_live_publishable_key');
                }
                if(!isset($this->request->post['stripe_live_secret_key']) || trim($this->request->post['stripe_live_secret_key']) == ''){
                    $this->error['live_secret_key'] = $this->language->get('error_live_secret_key');
                }
            }
        } else {
            $this->error['environment'] = $this->language->get('error_environment');
        }

        return !$this->error;
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

        return false;

    }

}
