<?php
/*
Plugin Name: Papara Woocommerce Gateway
Plugin URI:  www.papara.com
Description: Accept payments on your Wordpress Woocommerce sites with Papara account.
Version: 	 Alpha
Author: 	 PAPARA
Text Domain: papara_woocommerce
Domain Path: /language
Author URI:	 https://www.papara.com
WC requires at least: 6.4.0
WC tested up to: 6.4.0
*/

if (!defined('ABSPATH')) {
    exit;
}

function check_for_requirements_init(){

	$PLUGIN_FULL_PATH = "/papara-woocommerce/woocommerce-papara-gateway.php";

	/**
     * * Create WordPress Error Notice About Required WordPress Version
	 * @return void
	 */
	function wordpress_version_add_error_notice() {
		?>
		<div class="error notice">
			<p><?php __( 'Papara Payment Gateway Plugin needs WordPress Version 5+!', 'papara_woocommerce' ); ?></p>
		</div>
		<?php
	}

	/**
     * * Create WordPress Error Notice About Required WooCommerce Plugin and Version
	 * @return void
	 */
	function woocommerce_version_add_error_notice() {
		?>
		<div class="error notice">
			<p><?php __( 'Papara Payment Gateway Plugin needs Woocommerce Active And Version 6+!', 'papara_woocommerce' ); ?></p>
		</div>
		<?php
	}

	/**
     * Create WordPress Error Notice About Required PHP Version
	 * @return void
	 */
	function php_version_add_error_notice() {
		?>
        <div class="error notice">
            <p><?php __( 'Papara Payment Gateway Plugin needs PHP Version 7+!', 'papara_woocommerce' ); ?></p>
        </div>
		<?php
	}
	/**
	 * Check if PHP Version is bigger than minimum requirements
	 */
	if(version_compare( phpversion(), "7.0", "<" )){

		add_action( 'admin_notices', 'php_version_add_error_notice' );
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( $PLUGIN_FULL_PATH ); // Deactivate our plugin
	}

	/**
	 * Check if WordPress Version is bigger than minimum requirements
	 */
	if ( version_compare( get_bloginfo( 'version' ), '5.0.0', '<' ) )
	{
		add_action( 'admin_notices', 'wordpress_version_add_error_notice' );
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( $PLUGIN_FULL_PATH ); // Deactivate our plugin
	}

	/**
	 * Check if WooCommerce is activated or Deactivate the Plugin.
	 */
	$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';
	if (
		!in_array( $plugin_path, wp_get_active_and_valid_plugins() ) ||
		(function_exists("wp_get_active_network_plugins") && !in_array( $plugin_path, wp_get_active_network_plugins()) )
	) {
		add_action( 'admin_notices', 'woocommerce_version_add_error_notice' );
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( $PLUGIN_FULL_PATH ); // Deactivate our plugin
	}

}

check_for_requirements_init();

function papara_load_text_domain()
{
    load_plugin_textdomain('papara_woocommerce', false, dirname(plugin_basename(__FILE__)) . '/language/');
}

/**
 * Initialize the Papara plugin into WooCommerce
 * @return void
 */
function woocommerce_papara_gateway_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

	class Papara_Payment extends WC_Payment_Gateway
	{

		public function __construct()
		{
			$this -> plugin_version = "Alpha";
			$this -> id = 'papara';
			$this -> method_title = "Papara";
			$this -> title = __("Papara", 'papara_woocommerce');;
			$this -> method_description = __("Papara Payment Gateway Plug-in for WooCommerce", 'papara_woocommerce');
			$this -> icon =  "data:image/png;base64,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";
			$this -> order_button_text = __('Pay with Papara', 'papara_woocommerce');
			$this -> description = $this -> get_option('description');
            $this -> max_amount = 50000;
			$this -> init_form_fields();
			$this -> init_settings();
			$this -> supports[] = 'refunds';
			$this->has_fields = false;

			$adminnotice = new WC_Admin_Notices();

			if( $this->enabled == "yes" && empty($this->get_option('api_key'))){

                $this->enabled = "false";
				$adminnotice->add_custom_notice("empty_api_key",__('Api Key Required For Papara Gateway! You may disabled Gateway for prevent notifications', 'papara_woocommerce'));

            }else{
				$adminnotice->remove_notice("empty_api_key");
			}

			if (is_admin()) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ));
			}

			add_action('woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ));
		}

		public function init_form_fields()
		{
			// configuration for admin page
			$this -> form_fields = array(
				'enabled' => array(
					'title' => __('Enable/Disable', 'papara_woocommerce'),
					'type' => 'checkbox',
					'label' => __('Enable Papara Payment Module.', 'papara_woocommerce'),
					'default' => 'no'),
				'environment' => array(
					'title' => __('Test Mode', 'papara_woocommerce'),
					'label' => __('Enable Test Mode', 'papara_woocommerce'),
					'type' => 'checkbox',
					'description' =>  __('In order to make tests check box', 'papara_woocommerce'),
					'default' => 'no',
				),
				'description' => array(
					'title' => __('Description:', 'papara_woocommerce'),
					'type' => 'textarea',
					'description' => __('This controls the description which the user sees during checkout.', 'papara_woocommerce'),
					'default' => __('Pay securely with Papara Payment System and Secure Servers.', 'papara_woocommerce')),
				'api_key' => array(
					'title' => __('Api Key', 'papara_woocommerce'),
					'type' => 'text',
					'description' =>  __('Given to Merchant by Papara', 'papara_woocommerce'),
					'default' => '',
                    'required' => true
				),
				'secret_key' => array(
					'title' => __('Secret Key', 'papara_woocommerce'),
					'type' => 'text',
					'description' =>  __('In order to secure payments, can be found on Papara Merchant Account', 'papara_woocommerce'),
					'default' => ''
				),
				'secret_key_verification' => array(
					'title' => __('Enable/Disable', 'papara_woocommerce'),
					'type' => 'checkbox',
					'label' => __('Enable Papara Payment Secret Key Verification. (Live ONLY!)', 'papara_woocommerce'),
					'description' => '<b>'. __('Url: ' .(home_url('/') . "?rest_route=/papara/v1/webhook/" . get_option("papara_notification_url")) , 'papara_woocommerce').'</b>',
					'default' => 'no'),
			);
		}

		/**
         *
         * Initialize the Papara Plugin Admin Options Page
         *
		 * @return void
		 */
		public function admin_options() {

			ob_start();
			parent::admin_options();
			$parent_options = ob_get_contents();
			ob_end_clean();
			echo $parent_options;
			$pluginUrl = plugins_url().'/'.plugin_basename(dirname(__FILE__));
			$html = '<p style="text-align:center;"><img alt="papara" src="data:image/jpeg;base64,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" /><br/><strong>V: </strong>'.$this->plugin_version.'</p>';
			echo $html;
		}

		public function receipt_page($order_id)
		{
			global $woocommerce;
			$order = new WC_Order($order_id);

			$environment_url = "https://merchant" . (!( $this -> get_option('environment') == "yes") ? '-' : '.test.') . "api.papara.com/payments";

			//Create Payment Description that shows on Merchant Platform
			$payment_description = get_bloginfo('name') . ' Alışveriş Bedeli';

            $debug_response = "";

            try{
	            wc_clear_notices();

	            $response = wp_remote_post($environment_url,array(
		            'body' => json_encode(array(
			            'amount' => $order -> get_total(),
			            'referenceId' => $order_id,
			            'orderDescription' => $payment_description,
			            'notificationUrl'     => home_url('/') . "?rest_route=/papara/v1/webhook/" . get_option("papara_notification_url"),
			            'redirectUrl'       => $this -> get_return_url($order),
		            )),
		            'headers' => array(
			            'ApiKey' => $this -> get_option('api_key'),
			            'Content-Type' => 'application/json'
		            )
	            ));

	            if ( $response == false){
		            $debug_response = $response;
		            throw new Exception(__("Communication with Papara API Failed", 'papara_woocommerce'));
                }

	            if (is_wp_error($response)){
		            throw new Exception(__("Communication with Papara API Failed" . $environment_url, 'papara_woocommerce'));
	            }

                try{
			            $response_decoded = json_decode($response['body'], true);
                }catch (Exception $exception){

	                $debug_response = $response;
	                throw new Exception(__("Communication with Papara API Failed ", 'papara_woocommerce') . __("Incorrect Response received.", 'papara_woocommerce'));

                }

	            if ($response_decoded === null){
		            $debug_response = $response;
		            throw new Exception(__("Communication with Papara API Failed ", 'papara_woocommerce') . __("Incorrect Response received.", 'papara_woocommerce'));
                }

	            if ($response_decoded['succeeded'] == false) {
		            $debug_response = $response;
		            throw new Exception(__("Papara API succeeded false", 'papara_woocommerce'));
	            }

	            $order->save();

	            $order->add_meta_data('papara_payment_id', $response_decoded['data']['id'], true);
                $order->set_transaction_id($response_decoded['data']['id']);

                if(( $this -> get_option('environment') == "yes")){
	                $order->update_status('processing');
	                $order->add_order_note(__("Order auto updated because of test mode","papara_woocommerce"));

                }
                $order->save();
	            $this -> redirect_user($response_decoded['data']['paymentUrl']);

            }catch (Exception $exception){

                //if debug_response is valid json
	            json_decode($debug_response);
                if(json_last_error() === JSON_ERROR_NONE){
	                $order->add_meta_data('error_code', $debug_response['error']['code'], true);
	                //$order->add_order_note(__("DEBUG ERROR.". $debug_response,"papara_woocommerce"));
	                $order->update_status('failed');
	                $order->save();

	                switch ($order->get_meta('error_code')) {
		                case 997:
			                wc_add_notice(__('Your merchant account is not authorized for payment. Please Contact With Papara.',"papara_woocommerce"), 'error');
			                break;
		                case 998:
			                if ($order->get_total() < 1) {
				                wc_add_notice(__('You can not pay less than 1 Turkish Lira with Papara.',"papara_woocommerce"), 'error');
			                } elseif ($order->get_total() > 50000) {
				                wc_add_notice(__('You can not pay more that 50.000 Turkish Lira with Papara',"papara_woocommerce"), 'error');
			                } else {
				                wc_add_notice(__('Please Contact With Papara Directly.',"papara_woocommerce"), 'error');
			                }
			                break;
		                case 999:
			                if ($this->get_option('api_key') == null) {
				                wc_add_notice(__('API key is wrong. Please recheck your API key.',"papara_woocommerce"), 'error');
			                } else {
				                wc_add_notice(__('Something went wrong. Please try again in few minutes.',"papara_woocommerce"), 'error');
			                }
			                break;
		                default:
			                wc_add_notice(__('Undefined Error. Please Contact With Papara ',"papara_woocommerce"), 'error');
	                }
                }else{

	                //$order->add_order_note(__("DEBUG ERROR.". $debug_response . $exception,"papara_woocommerce"));

	                echo '<!--';
                    print_r($exception);
                    echo '-->';
	                wc_add_notice(__('API KEY verification is Failed. Double check your API\'s and Environment. Otherwise Please Contact With Papara ',"papara_woocommerce"), 'error');
	                $order->save();
                }
            }


		}

		/**
		 * @param $order_id
		 *
		 * @return array
		 */
		public function process_payment($order_id)
		{
			$order = new WC_Order($order_id);

			return array(
				'result'    => 'success',
				'redirect'    => $order->get_checkout_payment_url(true)
			);
		}

		public function redirect_user($redirectUrl)
		{
			header("Location: ".$redirectUrl);
		}

		public function process_refund($order_id, $amount = null, $reason = '')
		{

			$order = new WC_Order($order_id);

			$debug_response = "";
            try{
	            if (empty($amount)) {
		            throw new Exception(__('Amount Error: Amount can not be empty.','papara_woocommerce'));
	            }
	            $environment_url = "https://merchant" . (!( $this -> get_option('environment') == "yes") ? '-' : '.test.') . "api.papara.com/payments";

	            $environment_url .= "/Refund";

	            $paparaId = $order->get_meta("papara_payment_id");

	            if(empty($paparaId)){

		            $paparaId = $order->get_transaction_id();

	            }

                $amount = number_format(floatval($amount), 2);
                $amount = floatval($amount);

                try{
                    $requestBody = json_encode(array('paymentId' => $paparaId,"refundAmount" => $amount));

	                $response = wp_remote_post($environment_url,array(
                        'body' => $requestBody,
                        'headers' => array(
                            'ApiKey' => $this -> get_option('api_key'),
                            'Content-Type' => 'application/json',
                            'Content-Length' => strlen($requestBody)
                        )
                    ));


                }
                catch (Exception $exception){
                    throw new Exception(__("Communication with Papara API Failed ", 'papara_woocommerce'));
                }

	            if ($response == false){
		            $debug_response = $response;
		            throw new Exception(__("Communication with Papara API Failed", 'papara_woocommerce'));
	            }

	            try{
		            $response_decoded = json_decode($response["body"], true);
		            if ($response_decoded === null){
			            $debug_response = $response;
			            throw new Exception(__("Communication with Papara API Failed ", 'papara_woocommerce') . __("Incorrect Response received.", 'papara_woocommerce'));
		            }
	            }
                catch (Exception $exception){
		            $debug_response = $response;
	                throw new Exception(__("Communication with Papara API Failed ", 'papara_woocommerce') . __("Incorrect Response received.", 'papara_woocommerce'));
                }

	            if ($response_decoded['succeeded'] == false) {

		            $debug_response = $response;

		            throw new Exception(__("Papara API succeeded false", 'papara_woocommerce'));


	            }

	            $order->add_order_note(__("Papara Refund Confirmed.","papara_woocommerce"));
	            $order->update_status('refunded');
	            $order->save();

                //Debug responses
	            //$order->add_order_note(__("DEBUG ERROR => response json_encode == ". json_encode($response_decoded),"papara_woocommerce"));
	            //$order->add_order_note(__("DEBUG ERROR => response == ". ($response_decoded),"papara_woocommerce"));

	            // Simple response
	            return new WP_Error('papara', __('Refund succeeded!', 'papara_woocommerce'));

            }catch (Exception $exception){

	            $order->save();
	            //$order->add_order_note(__("DEBUG ERROR => response json_encode == ". json_encode($debug_response),"papara_woocommerce"));
	            //$order->add_order_note(__("DEBUG ERROR => response == ". ($debug_response),"papara_woocommerce"));
	            //$order->add_order_note(__("DEBUG ERROR => response body == ". ($debug_response["body"]),"papara_woocommerce"));
	            //$order->add_order_note(__("DEBUG ERROR => ex == ". $exception->getMessage(),"papara_woocommerce"));
	            $order->save();

	            try{
		            $test = str_replace( '“','"',$debug_response["body"]);
		            $test = str_replace( '”','"',$test);
		            $arte = json_decode($test);

                    return new WP_Error('papara', $exception->getMessage() . " " .$arte->error->message);


	            }catch (Exception $exception){

		            return new WP_Error('papara', $exception->getMessage());

	            }

            }


			$order = new WC_Order($order_id);

			if ($amount == 0 || $amount == null) {
				return new WP_Error('papara', __('Refund Error: You need to specify a refund amount.', 'papara'));
			}
			// notification for full refunds
			if ($amount != $order->get_total()) {
				return new WP_Error('papara', __('Amount error: Need to refund: '.$order->get_total(), 'papara'));
			}

			$environment_url = ($this -> get_option('environment') == 'TRUE') ? 'https://merchant-api.papara.com/payments' : 'https://merchantapi-test-master.papara.com/payments';
			$api_key = $this -> get_option('api_key');

			// HTTP PUT request for refund
			$refund_request_PUT = curl_init();
			curl_setopt_array($refund_request_PUT, array(
				CURLOPT_URL => $environment_url.'?id='.$order->get_transaction_id(),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_CUSTOMREQUEST => 'PUT',
				CURLOPT_HTTPHEADER => array(
					'ApiKey: '.$api_key,
					'Content-Type: application/json'
				),
				CURLOPT_POSTFIELDS => ''
			));
			$refund_request_result_JSON = curl_exec($refund_request_PUT);
			$refund_request_result_ARRAY = json_decode($refund_request_result_JSON, true);

			if ($refund_request_result_ARRAY['succeeded'] == true) {
				$order->update_status('refunded');
				curl_close($refund_request_PUT);

				// notify user, not an error
				return new WP_Error('papara', __('Refund succeeded!', 'papara'));
			}
        }
	}

	add_filter('woocommerce_payment_gateways', 'add_papara_gateway');

	add_action( 'rest_api_init', array('PaparaWebhook','PaparaRegisterRestRoutes') );

	/**
	 * @param $methods
	 *
     * Add Papara Gateway to Payment Method on WooCommerce Methods List
     *
	 * @return mixed
	 */
	function add_papara_gateway($methods)
    {
        $methods[] = 'Papara_Payment';
        return $methods;
    }
}

/**
 * @param $links
 * Add Custom Links for the Papara Plugin to Plugin list on Plugins.php
 * @return string[]
 */
function papara_action_links($links)
{
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=papara') . '">' . __('Settings', 'papara_woocommerce') . '</a>',
    );
    /** @var Customize Plugin Page with $custom_links */
	$custom_links[] = '<a target="_blank" href="https://merchant-api.papara.com/">' . __( 'Docs', 'woocommerce' ) . '</a>';
	$custom_links[] = '<a target="_blank" href="https://www.papara.com/contact">' . __( 'Support', 'papara_woocommerce' ) . '</a>';

    return array_merge($plugin_links, $links);
}

add_action('plugins_loaded', 'woocommerce_papara_gateway_init', 0);

add_action('plugins_loaded', 'papara_load_text_domain' );

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'papara_action_links');

class PaparaWebhook{

	public function __construct() {
		$this->namespace     = 'papara/v1';
		$this->resource_name = 'webhook/'. self::getNotificationURL();
	}

	public static function PaparaRegisterRestRoutes() {
		$controller = new PaparaWebhook();
		$controller->registerRoutes();
	}

	// Register our routes.
	public function registerRoutes() {
		register_rest_route( $this->namespace, '/' . $this->resource_name, array(
			// Here we register the readable endpoint for collections.
			'methods'   => 'POST',
			'callback' => array($this, 'orderUpdate' ),
			'permission_callback' => '__return_true',
		) );
	}

	// Control Order
	function orderUpdate($request) {

		$params = wp_parse_args( $request->get_json_params());

		//Security Check
        try{
            $pluginSettings = get_option('woocommerce_papara_settings');

	        $order = new WC_Order(intval($params["referenceId"]));

	        //$order->add_order_note("Papara request is Received." . json_encode($params),0,false);

	        if(empty($order->get_id())){
		        $order->add_order_note(__("Papara has invalid request. ","papara_woocommerce"),0,false);
		        return new WP_REST_Response('Id doesnt exist. invalid_parameters', 400);
	        }

	        //payment id same with WooCommerce?
	        if($order->get_meta("papara_payment_id") != $params["id"]){
		        $order->add_order_note(__("Papara has invalid request. ","papara_woocommerce"),0,false);
		        return new WP_REST_Response('Papara_payment_id invalid_parameters', 400);
	        }

            //if secret key doesn't fit, cancel order!
	        if($pluginSettings["secret_key_verification"] && $pluginSettings["environment"] == "no" &&
	           ($pluginSettings["secret_key"] != $params["merchantSecretKey"])){

		        $order->update_status('canceled');
		        $order->add_order_note(__("Papara Secret key verification FAILED!.","papara_woocommerce"),0,false);
                $order->save();
		        return new WP_REST_Response(null, 200);

	        }
	        //Order Success?
	        $order->update_status('processing');
	        $order->add_order_note(__("Papara payment confirmed.","papara_woocommerce"),0,false);
	        return new WP_REST_Response(null, 200);


        }catch (Exception $exception){

	        return new WP_REST_Response(null, 400);
        }

	}

	public static function getNotificationURL(){

		if (empty(get_option("papara_notification_url"))){

			update_option("papara_notification_url", substr(base64_encode(time() . mt_rand()),15,6) , '' ,'no');

        }
		return get_option("papara_notification_url");

	}


}

add_action('woocommerce_review_order_before_submit','papara_checkout_css');
function papara_checkout_css(){
	echo '<style>li.wc_payment_method.payment_method_papara label img{width:100%;max-height:inherit!important}li.wc_payment_method.payment_method_papara label{height:112px;font-size:0}</style>';
}
