<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Test Message Template Controller */
class TestMessageTemplateController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
        $id = (int)$this->get_value('id');
        $message_helper = $this->get_value('message_helper');
        
        if (!$message_helper)
        {
            CHtmlPage::set_last_error("Message Helper is not initialized");
            return false;
        }
        
        $field_args = TemplateMessenger::Instance($message_helper)->make_sample_template_args();
        
        $operator = LoggableAccess::Instance()->get_logged();
        $contact_name = $operator->get_fullname();
        $email = $operator->get_email();
        $phone = $operator->get_phone();
        
        $mt = new MessageTemplate($id);
        $category = $mt->get_category();
        
        $to = array();
        if ($mt->has_flag(MessageTemplate::SEND_MAIL)) $to[] = $email;
        if ($mt->has_flag(MessageTemplate::SEND_SMS)) $to[] = $phone;
        $to = implode(' and ', $to);
        
        if (TemplateMessenger::Instance($message_helper)->send($contact_name, $email, $phone, $category, $field_args) === false)
        {
            CHtmlPage::set_last_error("Failed to send message '{$category}' to {$to}");
            return false;
        }
        
        CHtmlPage::set_last_info("Message '{$category}' was sent to {$to}");
        return true;
    }
}
?>