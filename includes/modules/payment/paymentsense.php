<?php

class paymentsense 
{
  //$code determines the internal 'code' name used to designate "this" payment module
  var $code;
  
  //$version is the version of the Payment Method
  var $version;
  
  //$title is the displayed name for this payment method
  var $title;
 
  //$description is a soft name for this payment method
  var $description;
   
  //$enabled determines whether this module shows or not... in catalog.
  var $enabled;
  
  var $db;
  
  // class constructor
  function paymentsense() 
  {
  	global $order,$db ;
  	
  	$this->code = MODULE_PAYMENT_PAYMENTSENSE_TEXT_CODE;
  	$this->title = MODULE_PAYMENT_PAYMENTSENSE_TEXT_TITLE;
  	$this->version = MODULE_PAYMENT_PAYMENTSENSE_TEXT_VERSION;
  	$this->description = MODULE_PAYMENT_PAYMENTSENSE_TEXT_DESCRIPTION;
  	$this->sort_order = MODULE_PAYMENT_PAYMENTSENSE_SORT_ORDER;
  	$this->enabled = ((MODULE_PAYMENT_PAYMENTSENSE_STATUS == 'True') ? true : false);
  	$this->order_status = MODULE_PAYMENT_PAYMENTSENSE_ORDER_STATUS_ID;
  	$this->form_action_url = 'https://mms.paymentsensegateway.com/Pages/PublicPages/PaymentForm.aspx';
    
	if (is_object($order))
    {
      $this->update_status();
    }
  }

  // class methods  
  function update_status()
  {
   global $order, $db;
  }
  
  // Validate the credit card information via javascript (not applicable)
  function javascript_validation()
  {
  	return false;
  }
  
  //Display Credit Card Information Submission Fields on the Checkout Payment Page (not applcable)
  function selection()
  {
  	return array('id' => $this->code, 'module' => $this->title);
  }
  
  // Evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date (not applicable)
  function pre_confirmation_check()
  {
    return false;
  }
  
  // Display Credit Card Information on the Checkout Confirmation Page 
  function confirmation()
  {
		return false;
  }
  
  // These are hidden fields on the checkout confirmation page
  function process_button()
  {
  	global $db, $order, $order_total_modules, $currencies, $currency, $cartID, $code, $title, $description, $enabled, $db;
  	
  	require_once('includes/application_top.php');	
	require_once(DIR_WS_CLASSES . 'shipping.php');
	require_once(DIR_WS_CLASSES . 'payment.php');
	require_once(DIR_WS_CLASSES . 'order.php');
	require_once(DIR_WS_CLASSES . 'order_total.php');
	$payment_modules = new payment($_SESSION['payment']);
	$shipping_modules = new shipping($_SESSION['shipping']);
	$order = new order();
	$order_total_modules = new order_total;
	$order_totals = $order_total_modules->process();	
	$insert_id = $order->create($order_totals);	
	$order->create_add_products($insert_id);
	require_once(DIR_WS_LANGUAGES . 'english/checkout_process.php');
	//$order->send_order_email($insert_id);
  	
    $_SESSION['cartID'] = $cartID;
	
	define('STOCK_LIMITED', $orig_STOCK_LIMITED);
	
	switch (MODULE_PAYMENT_PAYMENTSENSE_CURRENCY) 
	{
		case 'GBP':
		  $currencyISO = 826;
		  break;
		case 'EUR':
		  $currencyISO = 978;
		  break;
		case 'USD':
		  $currencyISO = 840;
		  break;
	}
	$amountFormat = number_format((float)$order->info['total'], 2, '.', '');
	
	
  	$PreSharedKey = MODULE_PAYMENT_PAYMENTSENSE_PRESHAREDKEY;
  	$MerchantID = MODULE_PAYMENT_PAYMENTSENSE_MERCHANT_ID;
  	$Password = MODULE_PAYMENT_PAYMENTSENSE_PASSWORD;
  	$Amount = $amountFormat*100;
  	$CurrencyCode = $currencyISO;
  	$OrderID = $insert_id;
  	$TransactionType = 'SALE';
  	$TransactionDateTime = date('Y-m-d H:i:s P');
  	$CallbackURL = HTTP_SERVER . DIR_WS_CATALOG . 'paymentsense_callback/callback.php';
  	
  	$OrderDescription = STORE_NAME . " " . date('Ymdhis');
  	$CustomerName = $order->billing['firstname'] . ' ' . $order->billing['lastname'];
  	$Address1 = $order->billing['street_address'];
  	$Address2 = $order->billing['suburb'];
  	$Address3 = '';
  	$Address4 = '';
  	$City = $order->billing['city'];
  	$State = $order->billing['state'];
  	$PostCode = $order->billing['postcode'];
  	$CountryCode = '826';
  	$CV2Mandatory = 'TRUE';
  	$Address1Mandatory = 'TRUE';
  	$CityMandatory = 'TRUE';
  	$PostCodeMandatory = 'TRUE';
  	$StateMandatory = 'TRUE';
  	$CountryMandatory = 'TRUE';
  	$ResultDeliveryMethod = 'SERVER';
  	$ServerResultURL = HTTP_SERVER . DIR_WS_CATALOG . 'paymentsense_callback/serverresult.php';
  	$PaymentFormDisplaysResult = 'FALSE';
  	
  
  	$digest_string = 'PreSharedKey=' . $PreSharedKey;
	$digest_string = $digest_string . '&MerchantID=' . $MerchantID;
	$digest_string = $digest_string . '&Password=' . $Password;
	$digest_string = $digest_string . '&Amount=' . $Amount;
	$digest_string = $digest_string . '&CurrencyCode=' . $CurrencyCode;
	$digest_string = $digest_string . '&OrderID=' . $OrderID;
	$digest_string = $digest_string . '&TransactionType=' . $TransactionType;
	$digest_string = $digest_string . '&TransactionDateTime=' . $TransactionDateTime;
	$digest_string = $digest_string . '&CallbackURL=' . $CallbackURL;
	$digest_string = $digest_string . '&OrderDescription=' . $OrderDescription;
	$digest_string = $digest_string . '&CustomerName=' . $CustomerName;
	$digest_string = $digest_string . '&Address1=' . $Address1;
	$digest_string = $digest_string . '&Address2=' . $Address2;
	$digest_string = $digest_string . '&Address3=' . $Address3;
	$digest_string = $digest_string . '&Address4=' . $Address4;
	$digest_string = $digest_string . '&City=' . $City;
	$digest_string = $digest_string . '&State=' . $State;
	$digest_string = $digest_string . '&PostCode=' . $PostCode;
	$digest_string = $digest_string . '&CountryCode=' . $CountryCode;
	$digest_string = $digest_string . "&CV2Mandatory=" . $CV2Mandatory;
	$digest_string = $digest_string . "&Address1Mandatory=" . $Address1Mandatory;
	$digest_string = $digest_string . "&CityMandatory=" . $CityMandatory;
	$digest_string = $digest_string . "&PostCodeMandatory=" . $PostCodeMandatory;
	$digest_string = $digest_string . "&StateMandatory=" . $StateMandatory;
	$digest_string = $digest_string . "&CountryMandatory=" . $CountryMandatory;
	$digest_string = $digest_string . "&ResultDeliveryMethod=" . $ResultDeliveryMethod;
	$digest_string = $digest_string . "&ServerResultURL=" . $ServerResultURL;
	$digest_string = $digest_string . "&PaymentFormDisplaysResult=" . $PaymentFormDisplaysResult;
	$digest_string = $digest_string . "&ServerResultURLCookieVariables=" . '';
	$digest_string = $digest_string . "&ServerResultURLFormVariables=" . '';
	$digest_string = $digest_string . "&ServerResultURLQueryStringVariables=" . '';

	$digest = sha1($digest_string);
  	
  	
  	$process_button_string = 	zen_draw_hidden_field('HashDigest', $digest) .
								zen_draw_hidden_field('MerchantID', $MerchantID) .
								zen_draw_hidden_field('Amount', $Amount) .                                       
								zen_draw_hidden_field('CurrencyCode',  $CurrencyCode) .
								zen_draw_hidden_field('OrderID', $OrderID) .
								zen_draw_hidden_field('TransactionType', $TransactionType) .
								zen_draw_hidden_field('TransactionDateTime', $TransactionDateTime) .
								zen_draw_hidden_field('CallbackURL',  $CallbackURL) .
								zen_draw_hidden_field('OrderDescription',  $OrderDescription) .
								zen_draw_hidden_field('CustomerName',  $CustomerName) .
								zen_draw_hidden_field('Address1', $Address1) .
								zen_draw_hidden_field('Address2', $Address2) .
								zen_draw_hidden_field('Address3', $Address3) .
								zen_draw_hidden_field('Address4', $Address4) .
								zen_draw_hidden_field('City', $City) . 
								zen_draw_hidden_field('State', $State) .
								zen_draw_hidden_field('PostCode', $PostCode) .
								zen_draw_hidden_field('CountryCode', $CountryCode) .
								zen_draw_hidden_field('CV2Mandatory', $CV2Mandatory) .
								zen_draw_hidden_field('Address1Mandatory', $Address1Mandatory) .
								zen_draw_hidden_field('CityMandatory', $CityMandatory) .
								zen_draw_hidden_field('PostCodeMandatory', $PostCodeMandatory) .
								zen_draw_hidden_field('StateMandatory', $StateMandatory) .
								zen_draw_hidden_field('CountryMandatory', $CountryMandatory) .
								zen_draw_hidden_field('ResultDeliveryMethod', $ResultDeliveryMethod) .
								zen_draw_hidden_field('ServerResultURL', $ServerResultURL) .
								zen_draw_hidden_field('PaymentFormDisplaysResult', $PaymentFormDisplaysResult) .
								zen_draw_hidden_field('ServerResultURLCookieVariables', "") .
								zen_draw_hidden_field('ServerResultURLFormVariables', "") .
								zen_draw_hidden_field('ServerResultURLQueryStringVariables', "");
    
    return $process_button_string;
  }
  
  //Prepare and submit the authorization to the gateway
  function before_process()
  {
   return false;
  }
  
  function after_process()
  {
  	return false;
  }
  
  function after_order_create()
  {
    return false;
  }
  
  function admin_notification ()
  {
    return false;
  }
  
  function get_error()
  {
  	return false;
  }
  
  function check()
  {
  	global $db;
      if (!isset($this->_check)) {
        $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYMENTSENSE_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
  }
  
  function install()
  {
      global $db, $messageStack;
      if (defined('MODULE_PAYMENT_PAYMENTSENSE_STATUS')) 
      {
        $messageStack->add_session('PaymentSense module already installed.', 'error');
        zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=paymentsense', 'NONSSL'));
        return 'failed';
      }
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PaymentSense Module', 'MODULE_PAYMENT_PAYMENTSENSE_STATUS', 'True', 'Do you want to accept payments via PaymentSense Secure Payment Page?', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gateway MerchantID.', 'MODULE_PAYMENT_PAYMENTSENSE_MERCHANT_ID', 'Enter MerchantID', 'Enter Your Gateway MerchantID.', '6', '2', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gateway Password.', 'MODULE_PAYMENT_PAYMENTSENSE_PASSWORD', 'Enter Password', 'Enter Your Gateway Password.', '6', '3', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gateway PreSharedKey.', 'MODULE_PAYMENT_PAYMENTSENSE_PRESHAREDKEY', 'Enter PreSharedKey', 'Enter Your Gateway PreSharedKey.', '6', '4', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYMENTSENSE_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '5', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Currency', 'MODULE_PAYMENT_PAYMENTSENSE_CURRENCY', 'GBP', 'The currency to use for credit card transactions', '6', '6', 'zen_cfg_select_option(array(\'GBP\', \'EUR\', \'USD\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYMENTSENSE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '7', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYMENTSENSE_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '8', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");

     
      $query = "SELECT * FROM ". TABLE_ORDERS_STATUS ." ORDER BY orders_status_id desc LIMIT 1";
	  $row = $db->Execute($query);
      
      $LastInsertID = intval($row->fields["orders_status_id"]);
	  $LastInsertID1 = $LastInsertID + 1;
	  $LastInsertID2 = $LastInsertID + 2;
		
	  $db->Execute("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values (". $LastInsertID1 .", 1, 'Payment Failed')");
	  $db->Execute("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values (". $LastInsertID2 .", 1, 'Payment Successful')");	
		
	  $db->Execute("CREATE TABLE ". DB_PREFIX . "paymentsense (id int(11) NOT NULL auto_increment,
					cross_reference varchar(25) NOT NULL,
					zen_order_id int(11) NOT NULL,
					auth_code varchar(15) NOT NULL,
					message varchar(255) default NULL,
					amount_received varchar(15) default NULL,
					transaction_result int(11) default NULL,
					PRIMARY KEY (id))");
					
    }
  
  function remove()
  {
  	 global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
      $db->Execute("delete from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Payment Failed'");
	  $db->Execute("delete from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Payment Successful'");
	  $db->Execute("drop table " . DB_PREFIX . "paymentsense");
  }
  
  function keys()
  {
  	return array('MODULE_PAYMENT_PAYMENTSENSE_STATUS', 'MODULE_PAYMENT_PAYMENTSENSE_MERCHANT_ID', 'MODULE_PAYMENT_PAYMENTSENSE_PASSWORD', 'MODULE_PAYMENT_PAYMENTSENSE_PRESHAREDKEY', 'MODULE_PAYMENT_PAYMENTSENSE_ZONE', 'MODULE_PAYMENT_PAYMENTSENSE_CURRENCY', 'MODULE_PAYMENT_PAYMENTSENSE_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYMENTSENSE_SORT_ORDER');
  }
  /*
  function _log()
  {
  	
  }
  
  function _sendRequest()
  {
  
  }
  
  function _updateOrderStatus()
  {
  	
  }
  
  function _doRefund()
  {
  
  }
  
  function _doCapt()
  {
  	
  }
  
  function _doVoid()
  {
  	
  }
  */
}
?>