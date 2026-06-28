<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

abstract class TemplateMessenger
{
    /** Don't allow cloning */
    final private function __clone() {}
    
    /** Get the instance of the class */
    final public static function Instance($class = null)
    {
        static $instance = null;
        
        if ($instance == null)
        {
            $instance = $class ? new $class : new static();
        }
        
        return $instance;
    }
    
    /** The constructor */
    protected function __construct()
    {
        $this->init_template_fields();
    }
    
    /** Initialize template fields */
    protected abstract function init_template_fields();
    
    /** Make template arglist */
    protected abstract function make_template_args($obj, array $extra = null);
    
    /** Make sample template arglist */
    public abstract function make_sample_template_args(array $extra = null);
    
    /** Send email and/or SMS to contact. Returns message body on success, false otherwise */
    public function send($contact_name, $email, $phone, $category, array $field_args = null, $preferences = null)
    {
        $result = false;
        if ($field_args === null) $field_args = array();
        
        $caps = array();
        $caps[MessageTemplate::SEND_MAIL] = !Email::$Disabled && $email;
        $caps[MessageTemplate::SEND_SMS] = !SmsContent::$Disabled && $phone;
        if (!$caps[MessageTemplate::SEND_MAIL] && !$caps[MessageTemplate::SEND_SMS]) return false;
        
        $title = null;
        $use_preferences = false;

        if ($preferences)
        {
            if (is_array($preferences))
            {
                $use_preferences = 
                ($caps[MessageTemplate::SEND_MAIL] && $preferences[MessageTemplate::SEND_MAIL]) || // send by email
                ($caps[MessageTemplate::SEND_SMS] && $preferences[MessageTemplate::SEND_SMS]); // send by SMS
            }
            else 
            {
                // This is legacy argument
                $title = $preferences; 
            }
        }
        
        $mts = array();
        $flags = array(MessageTemplate::SEND_SMS, MessageTemplate::SEND_MAIL);
        
        foreach ($flags as $flag)
        {
            if ($caps[$flag])
            {
                $mt = MessageTemplateMan::Instance()->get_by_category($category, $flag);
                if ($mt) $mts[$flag] = current($mt);
            }
        }
        
        if (!$mts)
        {
            // Use first available
            $mt = MessageTemplateMan::Instance()->get_by_category($category);
            
            if ($mt) 
            {
                $flag = $caps[MessageTemplate::SEND_MAIL] ? MessageTemplate::SEND_MAIL : MessageTemplate::SEND_SMS;
                $mts[$flag] = current($mt);
            }
        }
        
        if ($mts && $use_preferences)
        {
            $pref_mts = array();
            
            foreach ($flags as $flag)
            {
                if ($preferences[$flag])
                {
                    // Use existing, or first available
                    $pref_mts[$flag] = isset($mts[$flag]) ? $mts[$flag] : current($mts);
                }
            }
            
            $mts = $pref_mts;
        }
        
        foreach ($mts as $flag=>$mt)
        {
            $msg = ($flag == MessageTemplate::SEND_MAIL) ?
            new Email(null, MAIL_NOREPLY, null, $email, $contact_name) :
            new SmsContent($phone);
            
            $subject = $title ? $title :
            implode(' ', MessageTemplateMan::Instance()->populate_contents($mt->get_title(), $field_args, $msg));
            $msg->set_subject($subject);
            
            $contents = MessageTemplateMan::Instance()->populate_contents($mt->get_contents(), $field_args, $msg);
            $msg->set_content($contents);
            
            MessageTemplateMan::Instance()->process_attachments($mt, $field_args, $msg);
            
            $queue = $mt->has_flag(MessageTemplate::DELIVER_IN_QUEUE);
            if ($msg->send($queue)) $result = implode('<br/>', $contents);
        }
        
        return $result;
    }
}
?>