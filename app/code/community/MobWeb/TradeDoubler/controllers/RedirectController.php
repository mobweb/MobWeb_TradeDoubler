<?php

class MobWeb_TradeDoubler_RedirectController extends Mage_Core_Controller_Front_Action
{
    public function indexAction() {

    	// If a "tduid" parameter has been passed, save it in a cookie and the current session
    	if(!empty($_GET["tduid"])) {
    		$tduid = $_GET['tduid'];
    		$domain = $_SERVER['HTTP_HOST'];

    		setcookie("TRADEDOUBLER", $tduid, (time() + 3600 * 24 * 365), "/", '.' . $domain);
    		$_SESSION["TRADEDOUBLER"] = $tduid;
    	}

    	// If a redirect URL has been set, redirect to that URL
    	if(!empty($_GET["url"])) {
    		$redirectUrl = urldecode(substr(strstr($_SERVER["QUERY_STRING"], "url"), 4));
    	}

    	$redirectUrl = $redirectUrl ? $redirectUrl : Mage::getBaseUrl();

    	header("Location: " . $redirectUrl);
    	exit;
    }
}