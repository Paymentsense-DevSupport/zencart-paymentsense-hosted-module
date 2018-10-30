<?php
/*
$Id$

Zencart PaymentSense Re-Direct Payment Module
Copyright (C) 2011 PaymentSense.
Support: ecomsupport@paymentsense.com

------------------------

Last Updated: 13/12/2011

------------------------

Released under the GNU General Public License
*/

	global $db;
	chdir('../');
	require_once('includes/application_top.php');
		// PaymentSense Example Code
		// SERVER Postback Method - Handle Hosted Payment Form Response.
		// Version 1.0
		// By PaymentSense, 13/12/2010
        // This code is provided on an "as is" basis. It is the responsibility of the developer to test its implementation.
		
		// The developer should amend this code in order to update the merchants website order system.
		// *PLEASE LOOK FOR "You should put your code that does any post transaction tasks".
		
		// The merchants system should store the status of the payment.
		// When the customer returns to the merchants website via the callback URL the status can then be sort so the correct response to the customer is displayed.
		// This page should echo a repsonse form the gateway to inform the gateway the message was delivered correctly.
		// Anything other than a "0" echoed in the response code for this page with envoke the gateway to send an email to the merchant with the error. The customer will then not return to the merchants website.
		// "0" simply means that the message was delivered correctly and is NOT an echo of the payment "StatusCode".
		
		function createhash($PreSharedKey,$Password) { 
			$str="PreSharedKey=" . $PreSharedKey;
			$str=$str . '&MerchantID=' . $_POST["MerchantID"];
			$str=$str . '&Password=' . $Password;
			$str=$str . '&StatusCode=' . $_POST["StatusCode"];
			$str=$str . '&Message=' . $_POST["Message"];
			$str=$str . '&PreviousStatusCode=' . $_POST["PreviousStatusCode"];
			$str=$str . '&PreviousMessage=' . $_POST["PreviousMessage"];
			$str=$str . '&CrossReference=' . $_POST["CrossReference"];
			$str=$str . '&Amount=' . $_POST["Amount"];
			$str=$str . '&CurrencyCode=' . $_POST["CurrencyCode"];
			$str=$str . '&OrderID=' . $_POST["OrderID"];
			$str=$str . '&TransactionType=' . $_POST["TransactionType"];
			$str=$str . '&TransactionDateTime=' . $_POST["TransactionDateTime"];
			$str=$str . '&OrderDescription=' . $_POST["OrderDescription"];
			$str=$str . '&CustomerName=' . $_POST["CustomerName"];
			$str=$str . '&Address1=' . $_POST["Address1"];
			$str=$str . '&Address2=' . $_POST["Address2"];
			$str=$str . '&Address3=' . $_POST["Address3"];
			$str=$str . '&Address4=' . $_POST["Address4"];
			$str=$str . '&City=' . $_POST["City"];
			$str=$str . '&State=' . $_POST["State"];
			$str=$str . '&PostCode=' . $_POST["PostCode"];
			$str=$str . '&CountryCode=' . $_POST["CountryCode"];
			return sha1($str);
		}
		
		// String together other strings using a "," as a seperator.
		function addStringToStringList($szExistingStringList, $szStringToAdd)
		{
			$szReturnString = "";
			$szCommaString = "";

			if (strlen($szStringToAdd) == 0)
			{
				$szReturnString = $szExistingStringList;
			}
			else
			{
				if (strlen($szExistingStringList) != 0)
				{
					$szCommaString = ", ";
				}
				$szReturnString = $szExistingStringList.$szCommaString.$szStringToAdd;
			}

			return ($szReturnString);
		}
		
		$szHashDigest = "";
		$szOutputMessage = "";
		$boErrorOccurred = false;
		$nStatusCode = 30;
		$szMessage = "";
		$nPreviousStatusCode = 0;
		$szPreviousMessage = "";
		$szCrossReference = "";
		$nAmount = 0;
		$nCurrencyCode = 0;
		$szOrderID = "";
		$szTransactionType= "";
		$szTransactionDateTime = "";
		$szOrderDescription = "";
		$szCustomerName = "";
		$szAddress1 = "";
		$szAddress2 = "";
		$szAddress3 = "";
		$szAddress4 = "";
		$szCity = "";
		$szState = "";
		$szPostCode = "";
		$nCountryCode = "";

		try
			{
				// hash digest
				if (isset($_POST["HashDigest"]))
				{
					$szHashDigest = $_POST["HashDigest"];
				}

				// transaction status code
				if (!isset($_POST["StatusCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [StatusCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["StatusCode"] == "")
					{
						$nStatusCode = null;
					}
					else
					{
						$nStatusCode = intval($_POST["StatusCode"]);
					}
				}
				// transaction message
				if (!isset($_POST["Message"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Message] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szMessage = $_POST["Message"];
				}
				// status code of original transaction if this transaction was deemed a duplicate
				if (!isset($_POST["PreviousStatusCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [PreviousStatusCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["PreviousStatusCode"] == "")
					{
						$nPreviousStatusCode = null;
					}
					else
					{
						$nPreviousStatusCode = intval($_POST["PreviousStatusCode"]);
					}
				}
				// status code of original transaction if this transaction was deemed a duplicate
				if (!isset($_POST["PreviousMessage"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [PreviousMessage] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szPreviousMessage = $_POST["PreviousMessage"];
				}
				// cross reference of transaction
				if (!isset($_POST["CrossReference"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CrossReference] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szCrossReference = $_POST["CrossReference"];
				}
				// amount (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["Amount"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Amount] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["Amount"] == null)
					{
						$nAmount = null;
					}
					else
					{
						$nAmount = intval($_POST["Amount"]);
					}
				}
				// currency code (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["CurrencyCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CurrencyCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["CurrencyCode"] == null)
					{
						$nCurrencyCode = null;
					}
					else
					{
						$nCurrencyCode = intval($_POST["CurrencyCode"]);
					}
				}
				// order ID (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["OrderID"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [OrderID] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szOrderID = $_POST["OrderID"];
				}
				// transaction type (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["TransactionType"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [TransactionType] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szTransactionType = $_POST["TransactionType"];
				}
				// transaction date/time (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["TransactionDateTime"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [TransactionDateTime] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szTransactionDateTime = $_POST["TransactionDateTime"];
				}
				// order description (same as value passed into payment form - echoed back out by payment form)
				if (!isset($_POST["OrderDescription"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [OrderDescription] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szOrderDescription = $_POST["OrderDescription"];
				}
				// customer name (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["CustomerName"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CustomerName] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szCustomerName = $_POST["CustomerName"];
				}
				// address1 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address1"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address1] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress1 = $_POST["Address1"];
				}
				// address2 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address2"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address2] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress2 = $_POST["Address2"];
				}
				// address3 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address3"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address3] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress3 = $_POST["Address3"];
				}
				// address4 (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["Address4"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [Address4] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szAddress4 = $_POST["Address4"];
				}
				// city (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["City"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [City] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szCity = $_POST["City"];
				}
				// state (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["State"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [State] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szState = $_POST["State"];
				}
				// post code (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["PostCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [PostCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					$szPostCode = $_POST["PostCode"];
				}
				// country code (not necessarily the same as value passed into payment form - as the customer can change it on the form)
				if (!isset($_POST["CountryCode"]))
				{
					$szOutputMessage = addStringToStringList($szOutputMessage, "Expected variable [CountryCode] not received");
					$boErrorOccurred = true;
				}
				else
				{
					if ($_POST["CountryCode"] == "")
					{
						$nCountryCode = null;
					}
					else
					{
						$nCountryCode = intval($_POST["CountryCode"]);
					}
				}
			}
		catch (Exception $e)
		{
			$boErrorOccurred = true;
			$szOutputMessage = "Error";
			if (!isset($_POST["Message"]))
			{
				$szOutputMessage = $_POST["Message"];
			}
		}
		
	// Check the passed HashDigest against our own to check the values passed are legitimate.
	$str1 = $_POST["HashDigest"];
	$hashcode = createhash(MODULE_PAYMENT_PAYMENTSENSE_PRESHAREDKEY,MODULE_PAYMENT_PAYMENTSENSE_PASSWORD);
	if ($hashcode != $str1) {
		//$nOutputProcessedOK = 30; 
		//$szOutputMessage = "Hashes did not match";
	}
	
	// The nOutputProcessedOK should return 0 except if there has been an error talking to the gateway or updating the website order system.
	// Any other process status shown to the gateway will prompt the gateway to send an email to the merchant stating the error.
	// The customer will also be shown a message on the hosted payment form detailing the error and will not return to the merchants website.
	$nOutputProcessedOK = 0;
	$transstatus = "failed";
	
	if (is_null($nStatusCode))
	{
		$nOutputProcessedOK = 30;		
	}
	
	if ($boErrorOccurred == true)
	{
		$nOutputProcessedOK = 30;
	}

	// *********************************************************************************************************
	// You should put your code that does any post transaction tasks
	// (e.g. updates the order object, sends the customer an email etc) in this section
	// *********************************************************************************************************
	if ($nOutputProcessedOK != 30)
		{	
			$nOutputProcessedOK = 0;
			// Alter this line once you've implemented the code.
			//$szOutputMessage = $szMessage."--"."Environment specific function needs to be implemented by merchant developer";
			try
			{
				switch ($nStatusCode)
				{
					// transaction authorised
					case 0:						
						$transstatus = "passed";
						break;
					// card referred (treat as decline)
					case 4:						
						$transstatus = "failed";
						break;
					// transaction declined
					case 5:
						$transstatus = "failed";
						break;				
					// duplicate transaction
					case 20:
						// need to look at the previous status code to see if the
						// transaction was successful
						if ($nPreviousStatusCode == 0)
						{
							$transstatus = "passed";	
							break;
						} else {
							$transstatus = "failed";
							break;
						}
						break;
					// error occurred
					case 30:
						$transstatus = "failed";	
						break;
					default:
						$transstatus = "failed";
						break;
				}
				
				//start of order update
				if ($transstatus == "failed") 
				{
					//insert result into paymentsense table
					$db->Execute("INSERT into " . DB_PREFIX . "paymentsense (cross_reference, zen_order_id, auth_code, message, amount_received, transaction_result) VALUES ('". $szCrossReference ."', '". $szOrderID ."', '0', '". $szMessage ."', '0', 30)");
					//update order status with trans attempt
					//$db->Execute("INSERT into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) VALUES ('". $szOrderID ."', ". MODULE_PAYMENT_PAYMENTSENSE_ORDER_STATUS_ID_FAILED .", now(), 1, '". $szMessage ."')");
					$db->Execute("INSERT into " . TABLE_ORDERS_STATUS_HISTORY . "(orders_id, orders_status_id, date_added, customer_notified, comments) VALUES ('". $szOrderID ."', '5', now(), 1, '". $szMessage ."')");
					//update order status
					//$db->Execute("UPDATE " . TABLE_ORDERS . " set orders_status = '". MODULE_PAYMENT_PAYMENTSENSE_ORDER_STATUS_ID_FAILED ."', last_modified = now() where orders_id = '" . (int)$szOrderID . "'");
					$db->Execute("UPDATE " . TABLE_ORDERS . " set orders_status = '5', last_modified = now() where orders_id = '" . (int)$szOrderID . "'");
					require_once("includes/languages/english/checkout_process.php");
					//get order details
					
					$orders_statuses = array();
					$orders_status_array = array();
					$orders_status = $db->Execute("select orders_status_id, orders_status_name
												 from " . TABLE_ORDERS_STATUS . "
												 where language_id = '1' order by orders_status_id");
					while (!$orders_status->EOF) {
						$orders_statuses[] = array('id' => $orders_status->fields['orders_status_id'],
												   'text' => $orders_status->fields['orders_status_name'] . ' [' . $orders_status->fields['orders_status_id'] . ']');
						$orders_status_array[$orders_status->fields['orders_status_id']] = $orders_status->fields['orders_status_name'];
						$orders_status->MoveNext();
					}
					
					$check_status = $db->Execute("select customers_name, customers_email_address, orders_status,
                                      date_purchased from " . TABLE_ORDERS . "
                                      where orders_id = '" . $szOrderID . "'");
									  					
					//send emails
					define('EMAIL_TEXT_ORDER_NUMBER', 'Order Number:');
					define('EMAIL_TEXT_INVOICE_URL', 'Detailed Invoice:');
					define('EMAIL_TEXT_DATE_ORDERED', 'Date Ordered:');
					define('EMAIL_TEXT_COMMENTS_UPDATE', '<em>The comments for your order are: </em>');
					define('EMAIL_TEXT_STATUS_UPDATED', 'Your order has been updated to the following status:' . "\n");
					define('EMAIL_TEXT_STATUS_LABEL', '<strong>New status:</strong> %s' . "\n\n");
					define('EMAIL_TEXT_STATUS_PLEASE_REPLY', 'Please reply to this email if you have any questions.' . "\n");
					
					$notify_comments = EMAIL_TEXT_COMMENTS_UPDATE . "\n\nPayment Failed\n\nCross Reference: ". $szCrossReference ." | ". $szMessage . "\n\n";
					
					$message = EMAIL_TEXT_ORDER_NUMBER . ' ' . $szOrderID . "\n\n" . EMAIL_TEXT_INVOICE_URL . "\n" . zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $szOrderID, 'SSL', false) . "\n\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($check_status->fields['date_purchased']) . "\n\n" . strip_tags($notify_comments) . EMAIL_TEXT_STATUS_UPDATED . sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[MODULE_PAYMENT_PAYMENTSENSE_ORDER_STATUS_ID_FAILED] ) . EMAIL_TEXT_STATUS_PLEASE_REPLY;
					
					$html_msg['EMAIL_CUSTOMERS_NAME']    = $check_status->fields['customers_name'];
					$html_msg['EMAIL_TEXT_ORDER_NUMBER'] = EMAIL_TEXT_ORDER_NUMBER . ' ' . $szOrderID;
					$html_msg['EMAIL_TEXT_INVOICE_URL']  = zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $szOrderID, 'SSL', false);
					$html_msg['EMAIL_TEXT_DATE_ORDERED'] = EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($check_status->fields['date_purchased']);
					$html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br($notify_comments);
					$html_msg['EMAIL_TEXT_STATUS_UPDATED'] = str_replace('\n','', EMAIL_TEXT_STATUS_UPDATED);
					$html_msg['EMAIL_TEXT_STATUS_LABEL'] = str_replace('\n','', sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[MODULE_PAYMENT_PAYMENTSENSE_ORDER_STATUS_ID_FAILED] ));
					$html_msg['EMAIL_TEXT_NEW_STATUS'] = $orders_status_array[MODULE_PAYMENT_PAYMENTSENSE_ORDER_STATUS_ID_FAILED];
					$html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = str_replace('\n','', EMAIL_TEXT_STATUS_PLEASE_REPLY);
					
					zen_mail($check_status->fields['customers_name'], $check_status->fields['customers_email_address'], 'Order Update' . ' #' . $szOrderID, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status');		
					
				}
				
				else {
					//insert result into paymentsense table
					$db->Execute("INSERT into " . DB_PREFIX . "paymentsense (cross_reference, zen_order_id, auth_code, message, amount_received, transaction_result) VALUES ('". $szCrossReference ."', '". $szOrderID ."', '". str_replace("AuthCode: ","",$szMessage) ."', '". $szMessage ."', '". $nAmount ."', 0)");						
					//update order status with trans attempt									
					$db->Execute("INSERT into " . TABLE_ORDERS_STATUS_HISTORY . "(orders_id, orders_status_id, date_added, customer_notified, comments) VALUES ('". $szOrderID ."', '6', now(), 1, 'Cross Reference: ". $szCrossReference ." | ". $szMessage ."')");
					//update order status
					$db->Execute("update " . TABLE_ORDERS . " set orders_status = '6', last_modified = now() where orders_id = '" . (int)$szOrderID . "'");
									
					$orders_statuses = array();
					$orders_status_array = array();
					$orders_status = $db->Execute("select orders_status_id, orders_status_name
												 from " . TABLE_ORDERS_STATUS . "
												 where language_id = '1' order by orders_status_id");
					while (!$orders_status->EOF) {
						$orders_statuses[] = array('id' => $orders_status->fields['orders_status_id'],
												   'text' => $orders_status->fields['orders_status_name'] . ' [' . $orders_status->fields['orders_status_id'] . ']');
						$orders_status_array[$orders_status->fields['orders_status_id']] = $orders_status->fields['orders_status_name'];
						$orders_status->MoveNext();
					}
					
					require_once(DIR_WS_CLASSES . 'shipping.php');
					require_once(DIR_WS_CLASSES . 'payment.php');
					require_once(DIR_WS_CLASSES . 'order.php');
					require_once(DIR_WS_CLASSES . 'order_total.php');
					
					$order = new order($szOrderID);	
					
					$check_status = $db->Execute("select customers_name, customers_email_address, orders_status,
                                      date_purchased from " . TABLE_ORDERS . "
                                      where orders_id = '" . $szOrderID . "'");
									  					
					//send emails
					define('EMAIL_TEXT_ORDER_NUMBER', 'Order Number:');
					define('EMAIL_TEXT_INVOICE_URL', 'Detailed Invoice:');
					define('EMAIL_TEXT_DATE_ORDERED', 'Date Ordered:');
					define('EMAIL_TEXT_COMMENTS_UPDATE', '<em>The comments for your order are: </em>');
					define('EMAIL_TEXT_STATUS_UPDATED', 'Your order has been updated to the following status:' . "\n");
					define('EMAIL_TEXT_STATUS_LABEL', '<strong>New status:</strong> %s' . "\n\n");
					define('EMAIL_TEXT_STATUS_PLEASE_REPLY', 'Please reply to this email if you have any questions.' . "\n");
					
					$notify_comments = EMAIL_TEXT_COMMENTS_UPDATE . "\n\nPayment Successful\n\nCross Reference: ". $szCrossReference ." | ". $szMessage . "\n\n";
					
					$message = EMAIL_TEXT_ORDER_NUMBER . ' ' . $szOrderID . "\n\n". EMAIL_TEXT_INVOICE_URL . "\n" . zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $szOrderID, 'SSL', false) . "\n\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($check_status->fields['date_purchased']) . "\n\n" . strip_tags($notify_comments) . EMAIL_TEXT_STATUS_UPDATED . sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[MODULE_PAYMENT_PAYMENTSENSE_ORDER_STATUS_ID] ) . EMAIL_TEXT_STATUS_PLEASE_REPLY;
					
					$html_msg['EMAIL_CUSTOMERS_NAME']    = $check_status->fields['customers_name'];
					$html_msg['EMAIL_TEXT_ORDER_NUMBER'] = EMAIL_TEXT_ORDER_NUMBER . ' ' . $szOrderID;
					$html_msg['EMAIL_TEXT_INVOICE_URL']  = zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $szOrderID, 'SSL', false);
					$html_msg['EMAIL_TEXT_DATE_ORDERED'] = EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($check_status->fields['date_purchased']);
					$html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br($notify_comments);
					$html_msg['EMAIL_TEXT_STATUS_UPDATED'] = str_replace('\n','', EMAIL_TEXT_STATUS_UPDATED);
					$html_msg['EMAIL_TEXT_STATUS_LABEL'] = str_replace('\n','', sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[MODULE_PAYMENT_PAYMENTSENSE_ORDER_STATUS_ID] ));
					$html_msg['EMAIL_TEXT_NEW_STATUS'] = $orders_status_array[MODULE_PAYMENT_PAYMENTSENSE_ORDER_STATUS_ID];
					$html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = str_replace('\n','', EMAIL_TEXT_STATUS_PLEASE_REPLY);
					
					zen_mail($check_status->fields['customers_name'], $check_status->fields['customers_email_address'], 'Order Update' . ' #' . $szOrderID, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status');
										
					//$order_total_modules = new order_total;
					//$order_totals = $order_total_modules->process();	
					//$insert_id = $order->create($order_totals);	
					//$order->create_add_products($insert_id);
					
					for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
						// Stock Update
						if (STOCK_LIMITED == 'true') {
							if (DOWNLOAD_ENABLED == 'true') {
								$stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
													FROM " . TABLE_PRODUCTS . " p
													LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
													ON p.products_id=pa.products_id
													LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
													ON pa.products_attributes_id=pad.products_attributes_id
													WHERE p.products_id = '" . zen_get_prid($order->products[$i]['id']) . "'";
								// Will work with only one option for downloadable products
								// otherwise, we have to build the query dynamically with a loop
								$products_attributes = $order->products[$i]['attributes'];
							
								if (is_array($products_attributes)) {
									$stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
								}
							
								$stock_query = $stock_query_raw;
							} else {
								$stock_query = "select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . zen_get_prid($order->products[$i]['id']) . "'";
							}
						
							$stock_values = $db->Execute($stock_query);
						
							if ($stock_values->RecordCount() > 0) {							
								// do not decrement quantities if products_attributes_filename exists							
								if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values->fields['products_attributes_filename'])) {
									$stock_left = $stock_values->fields['products_quantity'] - $order->products[$i]['qty'];
								} else {
									$stock_left = $stock_values->fields['products_quantity'];
								}
				
								$db->Execute("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . zen_get_prid($order->products[$i]['id']) . "'");
			
								if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
									$db->Execute("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . zen_get_prid($order->products[$i]['id']) . "'");
								}
							}
						}
					}
					
					//require_once(DIR_WS_LANGUAGES . 'english/checkout_process.php');
					//$order->send_order_email($szOrderID);
					 
				}
				
			}
			catch (Exception $e)
			{
				$nOutputProcessedOK = 30;
				$szOutputMessage = "Error updating website system, please ask the developer to check code";
			}
        }

	if ($nOutputProcessedOK != 0 &&
		$szOutputMessage == "")
	{
		$szOutputMessage = "Unknown error";
	}	

	// output the status code and message letting the payment form
	// know whether the transaction result was processed successfully
	echo("StatusCode=".$nOutputProcessedOK."&Message=".$szOutputMessage);
	
	require_once('includes/application_bottom.php');
?>