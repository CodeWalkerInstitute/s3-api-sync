<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       zekeswepson.com
 * @since      1.0.0
 *
 * @package    S3_Api_Sync
 * @subpackage S3_Api_Sync/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    S3_Api_Sync
 * @subpackage S3_Api_Sync/admin
 * @author     Zeke Swepson <zeke@codewalker.institute>
 */
class S3_Api_Sync_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in S3_Api_Sync_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The S3_Api_Sync_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/s3-api-sync-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in S3_Api_Sync_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The S3_Api_Sync_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/s3-api-sync-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function setup_menu() {
		add_menu_page("S3 API Sync", "S3 API Sync", "manage_options", "s3-api-sync-settings", array($this, "settings_page"));
		add_action( 'admin_init', array($this, 'setup_settings' ));
		error_log("register on save");
		add_action( 'save_post', array($this, 'handle_object_save'));
	}

	public function setup_settings() {
		register_setting( 's3-api-sync-settings-group', 'aws-access-key-id' );
		register_setting( 's3-api-sync-settings-group', 'aws-secret-access-key' );
		register_setting( 's3-api-sync-settings-group', 'aws-region' );
		register_setting( 's3-api-sync-settings-group', 'aws-bucket' );
	}

	public function handle_object_save() {
		//TODO: Get all endpoints
		$this->do_aws_upload("/", "general.json");
		$this->do_aws_upload("/wp/v2/posts", "posts.json");
		$this->do_aws_upload("/wp/v2/pages", "pages.json");
	}

	public function settings_page() {		
		if (!current_user_can( 'manage_options' )) {
			wp_die( "Sorry. You don't have access to use this plugin." );
		}
		echo include(plugin_dir_path( __FILE__ ) . "partials/s3-api-sync-admin-display.php");
		wp_die();
	}

	private function do_aws_upload($endpoint, $filename) {
		error_log("do upload: ". $endpoint . "\t".$filename);
		/// AWS API keys
		$aws_access_key_id = get_option('aws-access-key-id');
		error_log("aws_access_key_id: ". $aws_access_key_id);
		$aws_secret_access_key = get_option('aws-secret-access-key');
		error_log("aws_secret_access_key: ". $aws_secret_access_key);
		// Bucket
		$bucket_name = get_option('aws-bucket');

		// AWS region and Host Name (Host names are different for each AWS region)
		// As an example these are set to us-east-1 (US Standard)
		$aws_region = get_option('aws-region');
		$host_name = $bucket_name . '.s3.amazonaws.com';

		// Server path where content is present. This is just an example
		$content_path = site_url('/wp-json/') . $endpoint;
		
		$request = new WP_REST_Request( 'GET', $endpoint );
		//$request->set_query_params( [ 'per_page' => 12 ] );
		$response = rest_do_request( $request );
		$server = rest_get_server();
		$data = $server->response_to_data( $response, false );
		$content = wp_json_encode( $data );

		//error_log("Content: " . var_export($content, true));
		// AWS file permissions
		$content_acl = 'public-read';

		// MIME type of file. Very important to set if you later plan to load the file from a S3 url in the browser (images, for example)
		$content_type = 'application/json';
		// Name of content on S3
		$content_title = $filename;

		// Service name for S3
		$aws_service_name = 's3';

		// UTC timestamp and date
		$timestamp = gmdate('Ymd\THis\Z');
		$date = gmdate('Ymd');

		// HTTP request headers as key & value
		$request_headers = array();
		$request_headers['Content-Type'] = $content_type;
		$request_headers['Date'] = $timestamp;
		$request_headers['Host'] = $host_name;
		$request_headers['x-amz-acl'] = $content_acl;
		$request_headers['x-amz-content-sha256'] = hash('sha256', $content);
		// Sort it in ascending order
		ksort($request_headers);

		// Canonical headers
		$canonical_headers = [];
		foreach($request_headers as $key => $value) {
			$canonical_headers[] = strtolower($key) . ":" . $value;
		}
		$canonical_headers = implode("\n", $canonical_headers);

		// Signed headers
		$signed_headers = [];
		foreach($request_headers as $key => $value) {
			$signed_headers[] = strtolower($key);
		}
		$signed_headers = implode(";", $signed_headers);

		// Cannonical request 
		$canonical_request = [];
		$canonical_request[] = "PUT";
		$canonical_request[] = "/" . $content_title;
		$canonical_request[] = "";
		$canonical_request[] = $canonical_headers;
		$canonical_request[] = "";
		$canonical_request[] = $signed_headers;
		$canonical_request[] = hash('sha256', $content);
		$canonical_request = implode("\n", $canonical_request);
		$hashed_canonical_request = hash('sha256', $canonical_request);

		// AWS Scope
		$scope = [];
		$scope[] = $date;
		$scope[] = $aws_region;
		$scope[] = $aws_service_name;
		$scope[] = "aws4_request";

		// String to sign
		$string_to_sign = [];
		$string_to_sign[] = "AWS4-HMAC-SHA256"; 
		$string_to_sign[] = $timestamp; 
		$string_to_sign[] = implode('/', $scope);
		$string_to_sign[] = $hashed_canonical_request;
		$string_to_sign = implode("\n", $string_to_sign);

		// Signing key
		$kSecret = 'AWS4' . $aws_secret_access_key;
		$kDate = hash_hmac('sha256', $date, $kSecret, true);
		$kRegion = hash_hmac('sha256', $aws_region, $kDate, true);
		$kService = hash_hmac('sha256', $aws_service_name, $kRegion, true);
		$kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

		// Signature
		$signature = hash_hmac('sha256', $string_to_sign, $kSigning);

		// Authorization
		$authorization = [
			'Credential=' . $aws_access_key_id . '/' . implode('/', $scope),
			'SignedHeaders=' . $signed_headers,
			'Signature=' . $signature
		];
		$authorization = 'AWS4-HMAC-SHA256' . ' ' . implode( ',', $authorization);

		// Curl headers
		$curl_headers = [ 'Authorization: ' . $authorization ];
		foreach($request_headers as $key => $value) {
			$curl_headers[] = $key . ": " . $value;
		}

		$url = 'https://' . $host_name . '/' . $content_title;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);

		$r = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($http_code != 200) {
			error_log('Error : Failed to upload to s3 - ' . $http_code);
			$this->$http_code;
			return false;
		}
		return true;
	}
}