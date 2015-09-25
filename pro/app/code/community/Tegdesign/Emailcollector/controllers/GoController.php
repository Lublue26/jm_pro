<?php
ob_start();

require_once Mage::getModuleDir('', 'Tegdesign_Emailcollector') . '/lib/MailChimp.class.php';

class Tegdesign_Emailcollector_GoController extends Mage_Core_Controller_Front_Action {

    public function joinAction() {

        $debug_mode = false;
        if (Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/debug_mode')) {
            $debug_mode = Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/debug_mode');
        }

        $postData = Mage::app()->getRequest()->getPost();

        if ($this->getRequest()->isPost()) {

            if (isset($postData['store_id'])) {

                $store = Mage::getModel('core/store')->load($postData['store_id']);
                $store_name = $store->getName();
                $store_id = $store->getId();
                $website_id = $store->getWebsiteId();

            } else {

                if ($debug_mode) {
                    Mage::log('no post data sent', null, 'tegdesign_emailcollector_debug.log');
                }

                $this->redirectToLandingPage(true, 0);

            }

            if (strtolower(trim($postData['popup_email'])) != 'email address'){

                if ($postData['popup_email'] != '') {

                    if ($debug_mode) {
                        Mage::log($postData, null, 'tegdesign_emailcollector_debug.log');
                    }

                    $postData['popup_email'] = trim(strtolower($postData['popup_email']));

                    // check to see if email already exists in system
                    $exists = Mage::getModel('tegdesign_emailcollector/emails')
                                        ->getCollection()
                                        ->addFieldToSelect('*')
                                        ->addFieldToFilter('email', $postData['popup_email'])
                                        ->addFieldToFilter('store_id', $store_id)
                                        ->addFieldToFilter('website_id', $website_id)
                                        ->getFirstItem();
        
                    if ($exists->getId()) {

                        $this->redirectToLandingPage(false, $store_id);

                    } else {

                        // are we registering a customer ?
                        if (isset($postData['password']) && !empty($postData['password'])) {

                            if (Mage::helper('core/string')->strlen($postData['password']) <= 6) {

                                Mage::log('CODE0: password is not the right length', null, 'tegdesign_emailcollector.log');

                                $this->redirectToLandingPage(true, $store_id);

                            } else {

                                $website_id = $store->getWebsiteId();
                                $customer = Mage::getModel('customer/customer');
                                $customer->setWebsiteId($website_id);
                                $customer->loadByEmail($postData['popup_email']);

                                if (!$customer->getId()) {

                                    // setting data such as email, firstname, lastname, and password 
                                    $customer->setEmail($postData['popup_email']); 
                                    $customer->setPassword($postData['password']);

                                }
                                
                                try {

                                    // the save the data and send the new account email.
                                    $customer->setConfirmation(null);

                                    if (Mage::getStoreConfig('tegdesign_emailcollector_options/regopts/extra_fields', $store_id)) {

                                        $extra_fields = Mage::getStoreConfig('tegdesign_emailcollector_options/regopts/extra_fields', $store_id);
                                        $extra_fields_html = '';
                                        $extra_fields = explode(',', $extra_fields);

                                        
                                        foreach ($postData as $key => $value) {

                                            // find any date fields
                                            if (strpos($key,'epcdate_') !== false) {

                                                $key = str_replace('epcdate_', '', $key);
                                                $value = strtotime($value);

                                            }

                                            if (!empty($value)) {

                                                $customer->setData($key, $value);

                                            }
                                            
                                        }

                                    }

                                    $customer->setStore($store);
                                    $customer->setWebsiteId($website_id);
                                    $customer->save(); 
                                    $customer->sendNewAccountEmail();

                                    try {

                                        $website_id = $store->getWebsiteId();
                                        $customer = Mage::getModel('customer/customer');
                                        $customer->setWebsiteId($website_id);
                                        $customer->loadByEmail($postData['popup_email']);

                                        //login customer
                                        Mage::getSingleton('customer/session')
                                            ->setCustomerAsLoggedIn($customer)
                                            ->renewSession();

                                    } catch (Exception $e) {

                                        Mage::log('CODE1: ' . $e->getMessage(), null, 'tegdesign_emailcollector.log');
                                    
                                    }

                                } catch (Exception $e) {
                                    
                                    Mage::log('CODE2: ' . $e->getMessage(), null, 'tegdesign_emailcollector.log');

                                }

                            }

                        } else {

                            if (Mage::getStoreConfig('tegdesign_emailcollector_options/regopts/addtonewsletter', $store_id)) {

                                $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($postData['popup_email']);
                                            
                                if (!$subscriber->getId() 
                                    || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED 
                                    || $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                                        
                                    $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
                                    $subscriber->setSubscriberEmail($postData['popup_email']);
                                    $subscriber->setSubscriberConfirmCode($subscriber->RandomSequence());
                                }

                                $subscriber->setStoreId($store_id);
                                    
                                try {

                                    $subscriber->save();

                                } catch (Exception $e) {

                                    Mage::log('CODE3: ' . $e->getMessage(), null, 'tegdesign_emailcollector.log');

                                }

                            }

                        }

                        $emailcollector = Mage::getModel('tegdesign_emailcollector/emails');
                        $emailcollector->setEmail($postData['popup_email']);
                        $emailcollector->setDateCollected(strtotime('now'));
                        $emailcollector->setStoreId($store_id);
                        $emailcollector->setWebsiteId($website_id);
                        
                        $generated_coupon = '';

                        if (Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/use_coupon', $store_id)) { 

                            if (Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/promocoupon', $store_id)) {

                                $generated_coupon = $this->generateCoupon($postData['popup_email'], $store);
                                $emailcollector->setCoupon($generated_coupon);

                            }

                        }

                        // add email to database
                        $emailcollector->save();

                        $mailchimp_synch = false;
                        $mailchimp_apikey = '';
                        $mailchimp_listid = '';
                        $mailchimp_autoresponder = false;

                        if (Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_enabled', $store_id) && Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_apikey', $store_id) && Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_listid', $store_id)) {

                            $mailchimp_synch = true;
                            $mailchimp_apikey = Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_apikey', $store_id);
                            $mailchimp_listid = Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_listid', $store_id);

                        }

                        if (Mage::getStoreConfig('tegdesign_emailcollector_options/settings/magento_email', $store_id)) {

                            if (Mage::getStoreConfig('tegdesign_emailcollector_options/settings/emailcollector_template', $store_id)) {

                                    if ($debug_mode) {
                                        Mage::log('magento_template_mode', null, 'tegdesign_emailcollector_debug.log');
                                    }

                                    $mailTemplate = Mage::getModel('core/email_template');
                                    $translate  = Mage::getSingleton('core/translate');
                                    $templateId = Mage::getStoreConfig('tegdesign_emailcollector_options/settings/emailcollector_template', $store_id);
                                    $template_collection =  $mailTemplate->load($templateId);
                                    $template_data = $template_collection->getData();

                                    // make sure the template exists
                                    if (!empty($template_data)) {

                                        $templateId = $template_data['template_id'];
                                        $mailSubject = $template_data['template_subject'];
                                        $from_email = Mage::getStoreConfig('trans_email/ident_general/email'); //fetch sender email
                                        $from_name = Mage::getStoreConfig('trans_email/ident_general/name'); //fetch sender name

                                        $sender = array('name'  => Mage::getStoreConfig('trans_email/ident_general/name', $store_id), 'email' => Mage::getStoreConfig('trans_email/ident_general/email', $store_id));   
                                        $model = $mailTemplate->setReplyTo($sender['email'])->setTemplateSubject($mailSubject);

                                        $vars = array();

                                        if (Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/use_coupon', $store_id)) {
                                            $vars['coupon'] = $generated_coupon;
                                        }

                                        $model->sendTransactional($templateId, $sender, $postData['popup_email'], $store_name, $vars, $store_id);
                                        
                                        if (!$mailTemplate->getSentSuccess()) {

                                            $template_dump = $mailTemplate->getData();
                                            Mage::log('CODE4:', null, 'tegdesign_emailcollector.log');
                                            Mage::log($template_dump, null, 'tegdesign_emailcollector.log');

                                        }

                                        $translate->setTranslateInline(true);
              
                                    } else {

                                        Mage::log('CODE44: magento_template_empty', null, 'tegdesign_emailcollector.log');

                                    }

                            }

                        } elseif (Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_autoresponder_enabled', $store_id)) {

                            $mailchimp_autoresponder = true;

                        }
                    
                        if ($mailchimp_synch) {

                            if ($debug_mode) {
                                Mage::log('mailchimp_enabled', null, 'tegdesign_emailcollector_debug.log');
                            }

                            $MailChimp = new MailChimp($mailchimp_apikey);

                            $mc_data = array();

                            if ($mailchimp_autoresponder) {

                                if ($debug_mode) {
                                    Mage::log('mailchimp_autoresponder', null, 'tegdesign_emailcollector_debug.log');
                                }

                                if (Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/use_coupon', $store_id)) {
                                
                                    $mailchimp_autoresponderfield = Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_autoresponderfield', $store_id);
                                
                                    if (Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_send_coupon_enabled', $store_id)) {

                                        $mailchimp_coupon_merge_field = Mage::getStoreConfig('tegdesign_emailcollector_options/settings/mailchimp_coupon_merge_field', $store_id);

                                        if ($generated_coupon == '') {

                                            $mc_data[$mailchimp_coupon_merge_field] = $generated_coupon;

                                        } else {

                                            $mc_data[$mailchimp_coupon_merge_field] = $this->generateCoupon($postData['popup_email'], $store);
                                        }

                                        // this is the value that triggers the autoresponder at Mailchimp
                                        $mc_data[$mailchimp_autoresponderfield] = 'yes';

                                    }
                                    
                                }

                            }

                            if ($debug_mode) {
                                Mage::log('list_id: ' . $mailchimp_listid, null, 'tegdesign_emailcollector_debug.log');
                                Mage::log($mc_data, null, 'tegdesign_emailcollector_debug.log');
                            }

                            $result = $MailChimp->call('lists/subscribe', array(
                                'id'                => $mailchimp_listid,
                                'email'             => array('email' => $postData['popup_email']),
                                'merge_vars'        => $mc_data,
                                'double_optin'      => false,
                                'update_existing'   => true,
                                'replace_interests' => false,
                                'send_welcome'      => false,
                            ));

                            if ($debug_mode) {
                                Mage::log($result, null, 'tegdesign_email_collector_mailchimp.log');
                            }

                        }

                        $this->redirectToLandingPage(false, $store_id);

                    }

                }

            }

            $this->redirectToLandingPage(false, $store_id);

        } else {

            $this->redirectToLandingPage(true, $store_id);

        }
    }

    public function redirectToLandingPage($error = false, $store_id) {

        if ($error) {

            if (Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/error_url', $store_id)) {
                header('Location: ' . Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/error_url', $store_id));
                exit();
            } else {
                header('Location: /');
                exit();
            }

        } else {

            if (Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/redirect_opts', $store_id)) {

                $redirect_opts = Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/redirect_opts', $store_id);

                if ($redirect_opts == 'redirect_same_page') {

                    if (isset($_SERVER['HTTP_REFERER'])) {

                        header('Location: ' . $_SERVER['HTTP_REFERER']);
                        exit();

                    } else {
                        header('Location: /');
                        exit();
                    }
            
                } else {

                    if (Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/redirect_url', $store_id)) {
                        header('Location: ' . Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/redirect_url', $store_id));
                        exit();
                    } else {
                        header('Location: /');
                        exit();
                    }

                }

            } else {
                header('Location: /');
                exit();
            }

        }
    }

    public function generateUniqueId($length = null)
    {
        $rndId = crypt(uniqid(rand(),1));
        $rndId = strip_tags(stripslashes($rndId));
        $rndId = str_replace(array(".", "$"),"",$rndId);
        $rndId = strrev(str_replace("/","",$rndId));

        if (!is_null($rndId)){
            return strtoupper(substr($rndId, 0, $length));
        }

        return strtoupper($rndId);
    }

    public function generateCoupon($email, $store) {

        $store_id = $store->getId();

        $code = $this->generateUniqueId(12);
        $label = $email; //coupon label

        $couponCode = Mage::getStoreConfig('tegdesign_emailcollector_options/promosettings/promocoupon', $store_id);
        $coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
        $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());

        $from_date = $rule->getFromDate();
        $to_date = $rule->getToDate();
        $conditions = $rule->getConditionsSerialized();
        $actions = $rule->getActionsSerialized();
        $rules_processing = $rule->getStopRulesProcessing();
        $adv = $rule->getIsAdvanced();
        $pids = $rule->getProductIds();
        $sorto = $rule->getSortOrder();
        $disqty = $rule->getDiscountQty();
        $sfreeship = $rule->getSimpleFreeShipping();
        $applyship = $rule->getApplyToShipping();
        $rss = $rule->getIsRss();
        $coupon_type = $rule->getCouponType();
        $simple_action = $rule->getSimpleAction();
        $amount = $rule->getDiscountAmount();

        $name = $label;
        $labels[0] = $label;

        $new_coupon = Mage::getModel('salesrule/rule');

        $new_coupon->setName($name)
            ->setDescription($name)
            ->setFromDate($from_date)
            ->setToDate($to_date)
            ->setCouponCode($code)
            ->setUsesPerCoupon(1)
            ->setUsesPerCustomer(1)
            ->setCustomerGroupIds($this->getAllCustomerGroups())
            ->setIsActive(1)
            ->setConditionsSerialized($conditions)
            ->setActionsSerialized($actions)
            ->setStopRulesProcessing($rules_processing)
            ->setIsAdvanced($adv)
            ->setProductIds($pids)
            ->setSortOrder($sorto)
            ->setDiscountQty($disqty)
            ->setDiscountStep($sfreeship)
            ->setSimpleFreeShipping($sfreeship)
            ->setApplyToShipping($applyship)
            ->setIsRss($rss)
            ->setWebsiteIds($store->getWebsiteId())
            ->setCouponType($coupon_type)
            ->setStoreLabels($labels)
            ->setDiscountAmount($amount)
            ->setSimpleAction($simple_action);

        $new_coupon->save();

        return $code;

    }


    public function getAllCustomerGroups() {

        $customerGroupsCollection = Mage::getModel('customer/group')->getCollection();
        $customerGroupsCollection->addFieldToFilter('customer_group_code',array('nlike'=>'%auto%'));
        $groups = array();

        foreach ($customerGroupsCollection as $group) {
            $groups[] = $group->getId();
        }

        return $groups;
    }

    public function getAllWebsites() {

        $websites = Mage::getModel('core/website')->getCollection();
        $websiteIds = array();

        foreach ($websites as $website) {
            $websiteIds[] = $website->getId();
        }

        return $websiteIds;
    }


}