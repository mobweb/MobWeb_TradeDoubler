<?php

class MobWeb_TradeDoubler_Model_Observer
{
	public function controllerActionLayoutGenerateXmlBefore(Varien_Event_Observer $observer)
	{
		$layout = $observer->getEvent()->getLayout();

		// Add the block for retargeting
		$update = '' .
		'<reference name="before_body_end">
			<block type="tradedoubler/RetargetingTag" name="tradedoubler_block_retargetingtag"></block>
		</reference>';

		// Add the block for the purchase trackback
		$update .= '' .
		'<reference name="before_body_end">
			<block type="tradedoubler/PurchaseTrackback" name="tradedoubler_block_purchasetrackback"></block>
		</reference>';

		$layout->getUpdate()->addUpdate($update);

		return $this;
	}
}