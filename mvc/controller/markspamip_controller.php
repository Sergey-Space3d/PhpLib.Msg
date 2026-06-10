<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Ip Controller */
class MarkSpamIpController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
        $ip = $this->get_value('ip');
        $is_spam = $this->get_value('is_spam');
        SpamMan::Instance()->mark_spam($ip, Spam::TYPE_IP, $is_spam);
        
        $str = $is_spam ? "IP {$ip} was set as spam" : "IP {$ip} was marked";
        CHtmlPage::set_last_info($str);
        return true;
    }
}
?>