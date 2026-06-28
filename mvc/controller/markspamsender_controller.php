<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Sender Controller */
class MarkSpamSenderController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
        $value = $this->get_arg('value');
        $is_spam = $this->get_arg('is_spam');
        $select = is_array($this->get_arg('values'));
        
        $subject = self::type_to_string((int)$this->get_arg('type'));
        
        $confirm = $select ?
        ($is_spam ? "Confirm setting {$subject} as spam" : "Confirm scoring up {$subject}") :
        ($is_spam ? "Confirm setting {$subject} {$value} as spam" : "Confirm scoring up {$subject} {$value}");
        $submit = $select ?
        ($is_spam ? "Set {$subject} as Spam" : "Score up {$subject}") :
        ($is_spam ? "Set {$subject} {$value} as Spam" : "Score up {$subject} {$value}");
        
        $this->set_arg('confirm', $confirm);
        $this->set_arg('submit', $submit);
        
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
        $value = $this->get_value('value');
        $type = (int)$this->get_value('type');
        $is_spam = $this->get_value('is_spam');
        
        if (!$value || !$type)
        {
            CHtmlPage::set_last_error('invalid value or spam type');
            return false;
        }
        
        SpamFilterMan::Instance()->mark_spam($value, $type, $is_spam);
        
        $subject = self::type_to_string($type);
        $str = $is_spam ? "{$subject} {$value} was set as spam" : "{$subject} {$value} was marked";
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