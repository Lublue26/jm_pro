<?php
class Tegdesign_Emailcollector_Model_Mysql4_Emails extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {   
        $this->_init('tegdesign_emailcollector/emails', 'id');
    }   
}