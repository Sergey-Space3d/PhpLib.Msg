<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Text Controller */
class MarkSpamTextController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
        $is_spam = $this->get_arg('is_spam');
        
        $confirm = $is_spam ? "Confirm setting text as spam" : "Confirm scoring up text";
        $submit = $is_spam ? "Set Text as Spam" : "Score up Text";
        
        $this->set_arg('confirm', $confirm);
        $this->set_arg('submit', $submit);
        
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
        $value = trim($this->get_value('value'));
        $is_spam = $this->get_value('is_spam');
        
        if (!strlen($value))
        {
            CHtmlPage::set_last_error('Enter valid text');
            return false;
        }
        
        if ($value[0] != '/')
        {
            // Compose search pattern
            $arr = explode('|', $value);
            foreach ($arr as &$el) 
            {
                $el = trim($el);
                $el = implode('.*?', explode(' ', $el));
            }
            $value = implode('|\\b', $arr);
            $value = "/\\b{$value}/i";
        }
        
        SpamFilterMan::Instance()->mark_spam($value, SpamFilter::TYPE_TEXT, $is_spam);
        
        $str = $is_spam ? "'{$value}' text was set as spam" : "'{$value}' text was marked";
        CHtmlPage::set_last_info($str);
        return true;
    }
}
?>