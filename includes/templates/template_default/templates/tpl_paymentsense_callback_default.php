<?php
/*
$Id$

Zencart PaymentSense Re-Direct Payment Module
Copyright (C) 2018 PaymentSense.
Support: ecomsupport@paymentsense.com

------------------------

Last Updated: 30/10/2018

------------------------

Released under the GNU General Public License

File Notes:

Page Template
Loaded automatically by index.php?main_page=paymentsense_callback
Displays error details after payment has failed

*/

$Message = $_GET["Message"];

?>
<div class="centerColumn" id="checkoutSuccess">
  <h1 id="checkoutSuccessHeading"><?php echo HEADING_TITLE; ?></h1>
  <div id="checkoutSuccessOrderNumber" class="content">
	  <?php echo TEXT_FAILED; ?><br /><br />
    <span style="color:red;"><b><?php echo $Message; ?></b></span><br /><br />
	  <?php echo TEXT_FAILED2; ?><br /><br />
    <a href="<?php echo zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL')?>"><?php echo zen_image_submit(BUTTON_IMAGE_CONTINUE_CHECKOUT, BUTTON_CONTINUE_ALT); ?></a>
  </div>
</div>