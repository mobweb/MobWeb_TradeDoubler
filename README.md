# MobWeb_TradeDoubler extension for Magento

A simple Magento extension that adds support for TradeDoubler's retargeting offerings to your shop.

The following components are implemented:

- Retargeting tags for all pages, with specific data for: category page, product page, cart page, purchase confirmation page, and a general tag for all other pages.

- A redirect page that is used by TradeDoubler to place a specific cookie in your visitor's session so that a possible sale can then be tracked.

- A trackback pixel on the purchase confirmation page that sends various data about the order that was just placed back to TradeDoubler.

## Installation

Install using [colinmollenhour/modman](https://github.com/colinmollenhour/modman/).

## Configuration

In your shop's configuration under **System -> Configuration**, a new tab named **TradeDoubler* has been added. Here you have to enter your account data as provided by TradeDoubler.

You will also have to submit the URL for the redirect page to your TradeDoubler customer service representative. This URL is: **your-shops-base-url.com/tradedoubler/redirect*.

Everything else works out of the box. To inspect the retargeting tags and the trackback pixel, either check your page's source-code directly, or activate the **Debug Mode** in this extension's settings and activate your browser's development console to view the data for each page directly in your development console.

If you need to modify the data that is submitted to TradeDoubler (for example if your store uses a different attribute for the Product Description), you need to modify the **RetargetingTag.php** script directly.

## Questions? Need help?

Most of my repositories posted here are projects created for customization requests for clients, so they probably aren't very well documented and the code isn't always 100% flexible. If you have a question or are confused about how something is supposed to work, feel free to get in touch and I'll try and help: [info@mobweb.ch](mailto:info@mobweb.ch).