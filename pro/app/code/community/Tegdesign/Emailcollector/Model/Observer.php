<?php
class Tegdesign_Emailcollector_Model_Observer extends Mage_Core_Model_Abstract {

    public function addToNewsletter($observer) {
    
    	try{
    	
    		// Magento actually fires the customer_save_after event twice
    		if (Mage::registry('customer_save_observer_executed')) {
    		    return $this;
    		}
    	
	    	$customer = $observer->getCustomer();
	    	$customer_id = $customer->getId();
	    	$website_id = $customer->getWebsiteId();
	    	$store_id = $customer->getStoreId();
	    	$email = $customer->getEmail();

			if (Mage::getStoreConfig('tegdesign_emailcollector_options/regopts/addtonewsletter', $store_id)) {

			    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
			        
			    if (!$subscriber->getId() 
			        || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED 
			        || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
			            
			        $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
			        $subscriber->setSubscriberEmail($email);
			        $subscriber->setSubscriberConfirmCode($subscriber->RandomSequence());
			    }

			    $subscriber->setStoreId($store_id);
			    $subscriber->setCustomerId($customer_id);
			        
			    try {

			        $subscriber->save();
			    } catch (Exception $e) {
			        //throw new Exception($e->getMessage());
			    }

			}

			Mage::register('customer_save_observer_executed',true);

		} catch (Exception $e) {
	        //throw new Exception($e->getMessage());
	    }

	}
    
}
