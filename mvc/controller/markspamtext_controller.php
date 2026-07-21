<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Text Controller */
class MarkSpamTextController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
        $is_spam = $this->get_arg('is_spam');
        
        if (!$is_spam)
        {
        	$scores = [];
        	for ($n = 1; $n <= SpamFilter::COUNT_THRESHOLD; $n++) $scores[$n] = $n;
        	$this->set_arg('scores', $scores);
        }
        
        $score = $is_spam ? SpamFilter::COUNT_THRESHOLD : 1;
        $this->set_arg('score', $score);
        
        $confirm = $is_spam ? "Confirm setting text as spam" : "Confirm scoring text";
        $submit = $is_spam ? "Set Text as Spam" : "Score Text";
        
        $this->set_arg('confirm', $confirm);
        $this->set_arg('submit', $submit);
        
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
        $value = trim($this->get_value('value'));
        $score = (int)$this->get_value('score');
        
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
            $value = implode('|\b', $arr);
            $value = "/\b{$value}/i";
        }
        
        SpamFilterMan::Instance()->mark_spam($value, SpamFilter::TYPE_TEXT, $score);
        
        $str = $score == SpamFilter::COUNT_THRESHOLD ? 
        "'{$value}' text was set as spam" : "'{$value}' text was scored {$score}";
        CHtmlPage::set_last_info($str);
        return true;
    }
}
?>