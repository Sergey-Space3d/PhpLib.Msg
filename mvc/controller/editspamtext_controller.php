<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Edit Spam Text Controller */
class EditSpamTextController extends CController
{
	/** Initialize the form */
	protected function initialize()
	{
		$sf = new SpamFilter($this->get_arg('id'));
		
		$scores = [];
		for ($n = 1; $n <= SpamFilter::COUNT_THRESHOLD; $n++) $scores[$n] = $n;
		$this->set_arg('scores', $scores);
		
		$score = $sf->get_score();
		$this->set_arg('score', $score);
		
		$value = $sf->get_value();
		$this->set_arg('value', $value);
		
		$confirm = "Confirm regular expression";
		$this->set_arg('confirm', $confirm);
		
		$submit = "Submit";
		$this->set_arg('submit', $submit);
		
		$this->enable_post(true);
	}
	
	/** Process the form */
	protected function process()
	{
		$id = (int)$this->get_value('id');
		$value = trim($this->get_value('value'));
		$score = (int)$this->get_value('score');
		
		$sf = new SpamFilter($id);
		$sf->set_value($value);
		$sf->set_score($score);
		
		if (!$sf->write())
		{
			CHtmlPage::set_last_error('Error saving spam text');
			return false;
		}
		
		$str = $score == SpamFilter::COUNT_THRESHOLD ?
		"'{$value}' text was set as spam" : "'{$value}' text was scored {$score}";
		CHtmlPage::set_last_info($str);
		
		return true;
	}
}
?>