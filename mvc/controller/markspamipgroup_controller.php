<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Ip Group Controller */
class MarkSpamIpGroupController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
        $ip_group = $this->get_value('ip_group');
        SpamMan::Instance()->mark_spam($ip_group, Spam::TYPE_IP_GROUP);
        
        CHtmlPage::set_last_info("IP group {$ip_group} was marked as spam");
        return true;
    }
}
?>