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
        SpamMan::Instance()->mark_spam($ip, Spam::TYPE_IP);
        
        CHtmlPage::set_last_info("IP {$ip} was marked as spam");
        return true;
    }
}
?>