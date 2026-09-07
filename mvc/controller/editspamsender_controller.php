<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Edit Spam Sender Controller */
class EditSpamSenderController extends CController
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
		
		$confirm = "Confirm scoring {$value}";
		$this->set_arg('confirm', $confirm);
		
		$submit = "Submit";
		$this->set_arg('submit', $submit);
		
		$this->enable_post(true);
	}
	
	/** Process the form */
	protected function process()
	{
		$id = (int)$this->get_value('id');
		$score = (int)$this->get_value('score');
		
		$sf = new SpamFilter($id);
		$sf->set_score($score);
		
		$value = $sf->get_value();
		$type = self::type_to_string($sf->get_type());
		
		if (!$sf->write())
		{
			CHtmlPage::set_last_error("Error saving {$type} {$value}");
			return false;
		}
		
		$str = $score == SpamFilter::COUNT_THRESHOLD ?
		"'{$value}' {$type} was set as spam" : "'{$value}' {$type} was scored {$score}";
		CHtmlPage::set_last_info($str);
		
		return true;
	}
	
	/** Get the type as a string */
	static protected function type_to_string($type)
	{
		$subject = null;
		
		switch ($type)
		{
			case SpamFilter::TYPE_IP: $subject = 'IP'; break;
			case SpamFilter::TYPE_IP_GROUP: $subject = 'IP Group'; break;
			case SpamFilter::TYPE_DOMAIN: $subject = 'Domain'; break;
			case SpamFilter::TYPE_FROM_ADDRESS: $subject = 'Address'; break;
			default: break;
		}
		
		return $subject;
	}
}
?>