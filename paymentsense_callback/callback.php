<?php
/*
$Id$

Zencart PaymentSense Payment Module
Copyright (C) 2013 PaymentSense.
Support: ecomsupport@paymentsense.com

------------------------

Last Updated: 20/03/2013

------------------------

Released under the GNU General Public License
*/
	chdir('../');
	require('includes/application_top.php');
	global $cart, $db;
	
	$HashDigest = $_GET["HashDigest"];
	$MerchantID = $_GET["MerchantID"];
	$CrossReference = $_GET["CrossReference"];
	$OrderID = $_GET["OrderID"];	
	
	if ($HashDigest != "" && $MerchantID != "" && $CrossReference != "" && $OrderID != "") {	
		$hashcode="PreSharedKey=" . MODULE_PAYMENT_PAYMENTSENSE_PRESHAREDKEY;
		$hashcode=$hashcode . '&MerchantID=' . $MerchantID;
		$hashcode=$hashcode . '&Password=' . MODULE_PAYMENT_PAYMENTSENSE_PASSWORD;
		$hashcode=$hashcode . '&CrossReference=' . $CrossReference;
		$hashcode=$hashcode . '&OrderID=' . $OrderID;
		$hashcode = sha1($hashcode);
		
		if ($hashcode == $HashDigest) { 			
			$query = "SELECT * FROM ". DB_PREFIX . "paymentsense WHERE zen_order_id = ". $OrderID ." AND cross_reference = '". $CrossReference ."' ORDER BY id desc"; 
			$result = $query;
			$row = $db->Execute($result);
			
			$TransactionResult = $row->fields["transaction_result"];
			$Message = $row->fields["message"];
					
			if ($TransactionResult == 0) {
					$resultURL = zen_href_link(FILENAME_CHECKOUT_SUCCESS, 'HashDigest='. $HashDigest .'&MerchantID='. $MerchantID .'&CrossReference='. $CrossReference .'&OrderID='. $OrderID, 'SSL', true, false);			
					// unregister session variables used during checkout
					$_SESSION['order_number_created'] = $OrderID;
					$_SESSION['cart']->reset(true);
					unset($_SESSION['cartID']);
					unset($_SESSION['billto']);
					unset($_SESSION['shipping']);
					unset($_SESSION['payment']);
					unset($_SESSION['comments']);				
			} else {
					//Redirect back to custom page - ZenCart is very awkward about its error handling, using a custom page allows us to display a full error message.
					$resultURL = zen_href_link(FILENAME_PAYMENTSENSE_CALLBACK, '', 'SSL', true, false) . '&OrderID='. $OrderID.'&Message=' . urlencode($Message);
					$_SERVER['cart']->restore_contents;					
			}		
				
			//zen_redirect($resultURL);
			
			echo '<script type="text/javascript">
				  window.location = "'. $resultURL .'";
				  </script>';
		
		} else {
			echo "Hash check failed"; 
		}
	} else {
		echo "HashDigest, MerchantID, CrossReference or OrderID missing";
	}
	require('includes/application_bottom.php');
?>