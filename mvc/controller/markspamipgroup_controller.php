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
        $is_spam = $this->get_value('is_spam');
        $is_spam = SpamMan::Instance()->mark_spam($ip_group, Spam::TYPE_IP_GROUP, $is_spam);
        
        $str = $is_spam ? "IP group {$ip_group} was set as spam" : "IP group {$ip_group} was marked";
        CHtmlPage::set_last_info($str);
        return true;
    }
}
?>