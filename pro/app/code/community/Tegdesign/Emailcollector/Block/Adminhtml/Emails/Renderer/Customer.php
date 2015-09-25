<?php
class Tegdesign_Emailcollector_Block_Adminhtml_Emails_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
	
		$email = $row->getData('email');

		$db_conn = Mage::getSingleton('core/resource');
		$r_conn = $db_conn->getConnection('core_read');

		$tbl = Mage::getSingleton('core/resource')->getTableName('customer_entity');
		$sql = 'SELECT entity_id FROM ' . $tbl . ' WHERE email = "' . $email . '"';

		$rsp = $r_conn->fetchAll($sql);

		if (!empty($rsp)) {
			return $rsp[0]['entity_id'];
		} else {
			return 'n/a';
		}
	
	}
	
}