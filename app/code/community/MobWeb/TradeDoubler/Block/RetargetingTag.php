<?php
class MobWeb_TradeDoubler_Block_RetargetingTag extends Mage_Core_Block_Abstract
{
	protected function getDefaultPageData()
	{
		/* Required data example:
		protocol: document.location.protocol,
		containerTagId: "1234"
		*/

		$data = array();
		$data['protocol'] = 'document.location.protocol';

		// Get the container tag ID for this specific page type
		$data['containerTagId'] = Mage::helper('tradedoubler')->getSettings('retargeting/container_tag_id_general');

		return $data;
	}

	protected function getCategoryPageData()
	{
		/* Required data example:
		products: [{
				id: "[product-id1]",
				price: "[price1]",
				currency: "[currency1]",
				name: "[product-name1]"
			}, {
				id: "[product-id2]",
				price: "[price2]",
				currency: "[currency2]",
				name: "[product-name2]"
			},
		],
		Category_name: "[Name of category products]",
		containerTagId: "1234"
		*/

		$data = array();

		// Get the current category
		$currentCategory = Mage::helper('tradedoubler')->getCurrentCategory();

		if($currentCategory INSTANCEOF Mage_Catalog_Model_Category) {

			// Get the current category name
			$data['Category_name'] = $currentCategory->getName();
		} else {
			Mage::helper('tradedoubler')->log(sprintf('getCategoryPageData: Illegal / unknown category object: %s', print_r($currentCategory, true)));
		}

		// Get the currently listed products
		$currentlyListedProductsCollection = $this->getLayout()->getBlockSingleton('catalog/product_list')->getLoadedProductCollection();

		// Prepare the array containg the required product information for the currently listed products
		$currentlyListedProducts = array();
		foreach($currentlyListedProductsCollection AS $product) {
			$productInformation = array();
			$productInformation['id'] = $product->getId();
			$productInformation['price'] = $product->getFinalPrice();
			$productInformation['currency'] = Mage::helper('tradedoubler')->getCurrentCurrency();
			$productInformation['name'] = $product->getName();

			$currentlyListedProducts[] = $productInformation;
		}

		$data['products'] = $currentlyListedProducts;

		// Get the container tag ID for this specific page type
		$data['containerTagId'] = Mage::helper('tradedoubler')->getSettings('retargeting/container_tag_id_product_listing');

		return $data;
	}

	protected function getProductPageData()
	{
		/* Required data example::
		productId: "[product-id]",
		category: "[main-category-name]",
		brand: "[brand]",
		productName: "[product-name]",
		productDescription: "[product-description]",
		price: "[price]",
		currency: "[currency]",
		url: "[click-url]",
		imageUrl: "[url-to-product-image]",
		containerTagId: "1234"
		*/

		$data = array();

		$product = Mage::registry('current_product');

		if($product INSTANCEOF Mage_Catalog_Model_Product) {

			$data['productId'] = $product->getId();
			$data['category'] = Mage::helper('tradedoubler')->getCurrentCategory()->getName();
			$data['brand'] = $product->getAttributeText('manufacturer');
			$data['productName'] = $product->getName();
			$data['productDescription'] = strip_tags($product->getDescription());
			$data['price'] = $product->getFinalPrice();
			$data['currency'] = Mage::helper('tradedoubler')->getCurrentCurrency();
			$data['url'] = $product->getUrlInStore();
			$data['imageUrl'] = Mage::helper('tradedoubler')->getProductMainImage($product);
		} else {
			Mage::helper('tradedoubler')->log(sprintf('getProductPageData: Illegal / unknown product object: %s', print_r($product, true)));
		}

		// Get the container tag ID for this specific page type
		$data['containerTagId'] = Mage::helper('tradedoubler')->getSettings('retargeting/container_tag_id_product');

		return $data;
	}

	protected function getCartPageData()
	{
		/* Required data example::
		products: [{
				id: "[product-id1]",
				price: "[price1]",
				currency: "[currency1]",
				name: "[product-name1]",
				qty: "[quantity1]"
			}, {
				id: "[product-id2]",
				price: "[price2]",
				currency: "[currency2]",
				name: "[product-name1]",
				qty: "[quantity2]"
			},
		],
		containerTagId: "1234"
		*/

		$data = array();

		// Loop through the products currently in the cart and get their data
		$currentCartProducts = array();
		foreach (Mage::getModel('checkout/cart')->getQuote()->getAllItems() as $cartItem) {
			$cartProduct = $cartItem->getProduct();
			$cartProductData = array();

			if($cartProduct INSTANCEOF Mage_Catalog_Model_Product) {

				$cartProductData['id'] = $cartProduct->getId();
				$cartProductData['price'] = $cartItem->getPrice();
				$cartProductData['currency'] = Mage::helper('tradedoubler')->getCurrentCurrency();
				$cartProductData['name'] = $cartProduct->getName();
				$cartProductData['qty'] = $cartItem->getQty();

				$currentCartProducts[] = $cartProductData;
			} else {
				Mage::helper('tradedoubler')->log(sprintf('getCartPageData: Illegal / unknown product object: %s', print_r($cartProduct, true)));
			}
		}

		$data['products'] = $currentCartProducts;

		// Get the container tag ID for this specific page type
		$data['containerTagId'] = Mage::helper('tradedoubler')->getSettings('retargeting/container_tag_id_cart');

		return $data;
	}

	protected function getPurchasePageData()
	{
		/* Required data example::
		products: [{
				id: "[product-id1]",
				price: "[price1]",
				currency: "[currency1]",
				name: "[product-name1]",
				grpId: "[group-product-id1]",
				qty: "[quantity1]"
			}, {
				id: "[product-id2]",
				price: "[price2]",
				currency: "[currency2]",
				name: "[product-name1]",
				grpId: "[group-product-id2]",
				qty: "[quantity2]"
			},
		],
		orderId: "[orderId]",
		orderValue: "[orderValue]",
		currency: "[currency]",
		voucherCode: "[voucherCode]",
		validOn: "[validOn]",
		containerTagId: "1234"
		*/

		$data = array();

		// Load the order that was just submitted
		$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->load($orderId);

		// Loop through the purchased items and get their data
		$products = array();
		foreach($order->getAllVisibleItems() AS $orderItem) {
			$orderProduct = $orderItem->getProduct();
			$orderProductData = array();

			if($orderProduct INSTANCEOF Mage_Catalog_Model_Product) {

				// If the product is a simple product, get its parent product ID
				if($orderProduct->getTypeId() === "simple"){

					// Check if the product is a bundle product
					$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($orderProduct->getId());
					if(!$parentIds) {

						// If not, check if it's a configurable product
						$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($orderProduct->getId());
					}

					// If a parent product has been detected, load it
					if(isset($parentIds[0])){
						$orderProductParentProduct = Mage::getModel('catalog/product')->load($parentIds[0]);
					}
				}

				$orderProductData['id'] = $orderProduct->getId();
				$orderProductData['price'] = $orderItem->getPrice();
				$orderProductData['currency'] = Mage::helper('tradedoubler')->getCurrentCurrency();
				$orderProductData['name'] = $orderProduct->getName();
				$orderProductData['grpId'] = isset($orderProductParentProduct) ? $orderProductParentProduct->getId() : NULL;
				$orderProductData['qty'] = $orderItem->getQtyOrdered();

				$products[] = $orderProductData;
			} else {
				Mage::helper('tradedoubler')->log(sprintf('getPurchasePageData: Illegal / unknown product object: %s', print_r($orderProductData, true)));
			}
		}

		$data['products'] = $products;
		$data['orderId'] = $orderId;
		$data['orderValue'] = $order->getSubtotal();
		$data['currency'] = Mage::helper('tradedoubler')->getCurrentCurrency();
		$data['voucherCode'] = $order->getCouponCode();
		$data['validOn'] = NULL;

		// Get the container tag ID for this specific page type
		$data['containerTagId'] = Mage::helper('tradedoubler')->getSettings('retargeting/container_tag_id_purchase');

		return $data;
	}

	public function getStaticData()
	{
		$src = Mage::getBaseUrl('js') . 'mobweb_tradedoubler/tradedoubler_static.js';
		return sprintf('<script type="text/javascript" src="%s"></script>', $src);
	}

	protected function _toHtml()
	{
		// If the tags are disabled, don't do anything
		if(!Mage::helper('tradedoubler')->getSettings('retargeting/enabled')) {
			return;
		}

		// Figure out what page type we're on and get the required data for that page
		switch(Mage::helper('tradedoubler')->getPageType()) {

			case 'category':
				$data = $this->getCategoryPageData();
			break;

			case 'product':
				$data = $this->getProductPageData();
			break;

			case 'cart':
				$data = $this->getCartPageData();
			break;

			case 'purchase':
				$data = $this->getPurchasePageData();
			break;

			default:
				$data = $this->getDefaultPageData();
			break;
		}

		// Prepare the markup
		$markup = "var TDConf = TDConf || {}; \n TDConf.Config = %s";
		$markup = sprintf($markup, json_encode($data, JSON_PRETTY_PRINT));

		// Prepare the return data
		$return = sprintf('<script type="text/javascript">%s</script>', $markup);

		// If logging is enabled, output the markup to the development console
		if(Mage::helper('tradedoubler')->getIsDebug()) {
			$return .= Mage::helper('tradedoubler')->prepareJsConsoleScript($markup, 'Retargeting tag:');
		}

		// Append the script that contains the static data
		$return .= $this->getStaticData();

		// Return the tag so that it can be printed
		return $return;
	}
}