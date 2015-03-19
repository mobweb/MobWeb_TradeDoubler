<?php
class MobWeb_TradeDoubler_Block_PurchaseTrackback extends Mage_Core_Block_Abstract
{
	protected function _toHtml()
	{
		// If the tags are disabled, don't do anything
		if(!Mage::helper('tradedoubler')->getSettings('purchase_trackback/enabled')) {
			return;
		}

		// Only display the pixel on the purchase page
		if(Mage::helper('tradedoubler')->getPageType() !== 'purchase') {
			return;
		}

		// Get the variables from the settings
		$baseUrl = Mage::helper('tradedoubler')->getSettings('purchase_trackback/base_url');
		$organizationId = Mage::helper('tradedoubler')->getSettings('purchase_trackback/organization_id');
		$eventId = Mage::helper('tradedoubler')->getSettings('purchase_trackback/event_id');
		$checksumCode = Mage::helper('tradedoubler')->getSettings('purchase_trackback/checksum_code');
		$checksumCode2 = Mage::helper('tradedoubler')->getSettings('purchase_trackback/checksum_code_2');

		// Get the last order
		$lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$lastOrder = Mage::getModel('sales/order')->load($lastOrderId);

		// Get the dynamic values from the order that was just submitted
		$orderNumber = $lastOrderId;
		$orderValue = $lastOrder->getSubtotal();
		$currency = Mage::helper('tradedoubler')->getCurrentCurrency();
		$voucherCode = $lastOrder->getCouponCode();

		// Get the TDUID if it's available as a cookie
		$tduid = NULL;
		if(!empty($_SESSION["TRADEDOUBLER"])) {
		    $tduid = $_SESSION["TRADEDOUBLER"];
		} else if(!empty($_COOKIE["TRADEDOUBLER"])) {
		     $tduid = $_COOKIE["TRADEDOUBLER"];   
		}

		// Calculate the checksum
		$checksum = $checksumCode2 . md5($checksumCode . $orderNumber . $orderValue);

		// Gather the report info (TODO)
		$reportInfo = NULL;

		$markup = sprintf('%sorganization=%s&event=%s&ordernumber=%s&orderValue=%s&currency=%s&voucher=%s&tduid=%s&checksum=%s&reportInfo=%s', $baseUrl, $organizationId, $eventId, $orderNumber, $orderValue, $currency, $voucherCode, $tduid, $checksum, $reportInfo);

		$return = '';

		// If logging is enabled, output the markup to the development console
		if(Mage::helper('tradedoubler')->getIsDebug()) {
			$return .= Mage::helper('tradedoubler')->prepareJsConsoleScript($markup, 'Purchase Trackback pixel source URL:');
		}

		// Prepare the markup to be injected as an image
		$return .= sprintf('<img src="%s" width="1" height="1" border="0" />', $markup);

		return $return;
	}
}