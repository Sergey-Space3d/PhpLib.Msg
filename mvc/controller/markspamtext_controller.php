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
        
        $this->set_arg('greedy_match', CHtmlForm::get_value('greedy_match'));
        $this->set_arg('case_sensitive', CHtmlForm::get_value('case_sensitive'));
        $this->set_arg('whole_word', CHtmlForm::get_value('whole_word'));
        
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
        $value = trim($this->get_value('value'));
        $score = (int)$this->get_value('score');
        
        $greedy_match = $this->get_value('greedy_match');
        $case_sensitive = $this->get_value('case_sensitive');
        $whole_word = $this->get_value('whole_word');
        
        if (strlen($value) < 2)
        {
            CHtmlPage::set_last_error('Enter valid text');
            return false;
        }
        
        // Compose search pattern
        $b = $whole_word ? '\b' : '';
        $i = $case_sensitive ? '' : 'i';
        $s = $greedy_match ? '.*?' : '\s+';
        
        // Break value by OR elements
        $arr = explode('|', $value);
        
        foreach ($arr as &$el)
        {
        	$el = trim($el);
        	
        	for ($n = 0, $el2 = '', $num_chars = strlen($el); $n < $num_chars; $n++)
        	{
        		// Add escape char for every non-alphanumberic char
        		$el2 .= ($el[$n] == ' ' || ctype_alnum($el[$n])) ? $el[$n] : "\\{$el[$n]}";
        	}
        	
        	$el = $el2;
        	
        	// For greedy match, allow text between original words
        	$el = implode($s, array_filter(explode(' ', $el)));
        	
        	if ($el && $b)
        	{
        		// Don't append word boundary if the last char is not alphanumberic
        		$el = ctype_alnum(substr($el, -1)) ? "{$b}{$el}{$b}" : "{$b}{$el}";
        	}
        }
        
        $arr = array_filter($arr);
        
        if (!$arr)
        {
        	CHtmlPage::set_last_error('Enter valid text');
        	return false;
        }
        
        // Re-assemble OR elements
        $value = implode("|", $arr);
        $value = "/{$value}/{$i}";
        
        SpamFilterMan::Instance()->mark_spam($value, SpamFilter::TYPE_TEXT, $score);
        
        $str = $score == SpamFilter::COUNT_THRESHOLD ? 
        "'{$value}' text was set as spam" : "'{$value}' text was scored {$score}";
        CHtmlPage::set_last_info($str);
        return true;
    }
}
?>