<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Mail Controller */
class MarkSpamMailController extends CController
{
	/** Initialize the form */
	protected function initialize()
	{
		$email = $this->get_arg('email');
		$is_spam = $this->get_arg('is_spam');
		
		$values = [];
		$types = [];
		
		foreach ($email->SenderIpGroups as $ip_group) 
		{
			$values[$ip_group] = "IP Group: {$ip_group}";
			$types[$ip_group] = SpamFilter::TYPE_IP_GROUP;
		}
		
		foreach ($email->SenderDomains as $domain=>$ip) 
		{
			if ($ip)
			{
				$values[$ip] = "IP: {$ip}";
				$types[$ip] = SpamFilter::TYPE_IP;
			}
		}
		
		foreach ($email->SenderDomains as $domain=>$ip) 
		{
			$values[$domain] = "Domain: {$domain}";
			$types[$domain] = SpamFilter::TYPE_DOMAIN;
		}
		
		foreach ($email->SenderAddresses as $address)
		{
			$values[$address] = "Address: {$address}";
			$types[$address] = SpamFilter::TYPE_FROM_ADDRESS;
		}
		
		$this->set_arg('values', $values);
		$this->set_arg('types', serialize($types));
		
		if (!$is_spam)
		{
			$scores = [];
			for ($n = 1; $n < SpamFilter::COUNT_THRESHOLD; $n++) $scores[$n] = $n;
			$scores[SpamFilter::COUNT_THRESHOLD] = SpamFilter::COUNT_THRESHOLD.' (spam)';
			$this->set_arg('scores', $scores);
		}
		
		$score = $is_spam ? SpamFilter::COUNT_THRESHOLD : 1;
		$this->set_arg('score', $score);
		
		$confirm = $is_spam ? "Confirm setting property as spam" : "Confirm scoring property";
		$submit = $is_spam ? "Set Property as Spam" : "Score Property";
		
		$this->set_arg('confirm', $confirm);
		$this->set_arg('submit', $submit);
		
		$this->enable_post(true);
	}
	
	/** Process the form */
	protected function process()
	{
		$value = $this->get_value('value');
		$types = unserialize($this->get_value('types'));
		$score = (int)$this->get_value('score');
		
		$type = $types[$value];
		
		if (!$value || !$type || !$score)
		{
			CHtmlPage::set_last_error('invalid value or spam type');
			return false;
		}
		
		SpamFilterMan::Instance()->mark_spam($value, $type, $score);
		
		$str = $score == SpamFilter::COUNT_THRESHOLD ?
		"{$value} was set as spam" : "{$value} was scored {$score}";
		CHtmlPage::set_last_info($str);

		return true;
	}
}
?>