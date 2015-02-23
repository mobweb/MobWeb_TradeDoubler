<?php

class MobWeb_TradeDoubler_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function log($msg)
	{
		if(Mage::helper('tradedoubler')->getIsDebug()) {
			Mage::log($msg, NULL, 'MobWeb_TradeDoubler.log');
		}
	}

	public function getSettings($field)
	{
		$value = Mage::getStoreConfig('tradedoubler/' . $field);
		return $value;
	}

	public function getIsDebug()
	{
		return (Mage::helper('tradedoubler')->getSettings('general/enable_debug') == '1');
	}

	public function prepareJsConsoleScript($output, $title = NULL) {

		// Escape the markup for JS pasting
		$output = str_replace('"', "'", substr(json_encode($output), 1, -1));

		// Log the data into the console
		$return = sprintf('' .
		'<script>
			console.log("MobWeb_TradeDoubler: %s");
			console.log("%s");
		</script>', $title, htmlspecialchars($output));

		return $return;
	}

	public function getPageType()
	{
		/*
		 *
		 * Get the type of page that we are currently on, by looking at the current
		 * module, controller and action. Possible page type values:
		 * home, searchresults, category, product, cart, purchase, other
		 *
		 */
		$moduleName = Mage::app()->getRequest()->getModuleName();
		$controllerName = Mage::app()->getRequest()->getControllerName();
		$actionName = Mage::app()->getRequest()->getActionName();

		switch($moduleName . ',' . $controllerName . ',' . $actionName) {

			case 'cms,index,index':
				$pageType = 'home';
				break;

			case 'catalogsearch,result,index':
				$pageType = 'searchresults';
				break;

			case 'catalog,category,view':
				$pageType = 'category';
				break;

			case 'catalog,product,view':
				$pageType = 'product';
				break;

			case 'checkout,cart,index':
				$pageType = 'cart';
				break;

			case 'checkout,onepage,success':
				$pageType = 'purchase';
				break;

			default:
				$pageType = 'other';
				break;
		}

		Mage::helper('tradedoubler')->log(sprintf('getPageType: Pagetype is "%s", module is "%s", controller is "%s", action is "%s"', $pageType, $moduleName, $controllerName, $actionName));

		return $pageType;
	}

	public function getCurrentCurrency()
	{
		return Mage::app()->getStore()->getCurrentCurrencyCode();
	}

	public function getCurrentCategory()
	{
		$pageType = Mage::helper('tradedoubler')->getPageType();

		// Only pass the category for the "category" and "product" page types
		if(!in_array($pageType, array('category', 'product'))) {
			Mage::helper('tradedoubler')->log(sprintf('getCurrentCategory: Pagetype is "%s", not passing a category', $pageType));

			return;
		}

		if($pageType === 'category') {

			// On the category page, extract the current category from the registry
			$currentCategory = Mage::registry('current_category');
			Mage::helper('tradedoubler')->log(sprintf('getCurrentCategory: Current category is "%s"', $currentCategory->getEntityId()));

			return $currentCategory;
		} else {

			// On the product page, get the current product
			$product = Mage::registry('current_product');
			Mage::helper('tradedoubler')->log(sprintf('getCurrentCategory: Product loaded: %s', $product->getId()));

			// Get a list of all the categories that the product is assigned to
			$categories = $product->getCategoryIds();

			// Get the ID of the root category
			$rootCategoryId = Mage::app()->getStore()->getRootCategoryId();

			// If the product is assigned to no category, use the root category
			if(!$categories || !count($categories)) {
				$mainCategoryId = $rootCategoryId;
			} else if(count($categories) === 1) {

				// If the product is assigned to only one category, use that category as 
				// the main category
				$mainCategoryId = $categories[0];
			} else {

				// If the product is assigned to multiple categories, get the ID
				// of the first category that is NOT the root category, and use that
				// as the product's "main" category
				$mainCategoryId = $categories[0] == $rootCategoryId ? $categories[1] : $categories[0];
			}

			// Return the main category
			return Mage::getModel('catalog/category')->load($mainCategoryId);
		}
	}

	public function getProductMainImage(Mage_Catalog_Model_Product $product)
	{
		$productImageName = $product->getImage();

		if($productImageName) {
			return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $productImageName;
		} else {
			return '';
		}
	}
}