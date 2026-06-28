<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Edit Message Template Controller */
class EditMessageTemplateController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
        $id = (int)$this->get_arg('id');
        $tm = new MessageTemplate($id);
        
        $this->set_arg('category', $tm->get_category());
        $this->set_arg('title', $tm->get_title());
        $this->set_arg('contents', $tm->get_contents());
        
        $this->set_arg('send_mail', $tm->has_flag(MessageTemplate::SEND_MAIL));
        $this->set_arg('send_sms', $tm->has_flag(MessageTemplate::SEND_SMS));
        $this->set_arg('queue', $tm->has_flag(MessageTemplate::DELIVER_IN_QUEUE));
        $this->set_arg('maxlength', 10000);
        
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
        $id = (int)$this->get_value('id');
        $title = $this->get_value('title');
        $contents = $this->get_value('contents');
        $send_mail = (bool)$this->get_value('send_mail');
        $send_sms = (bool)$this->get_value('send_sms');
        $queue = (bool)$this->get_value('queue');
        
        if (!$title || strlen($title) > 64)
        {
            CHtmlForm::set_error('Title should be between 1 and 64 characters', 'title');
        }
        
        if (!$contents || strlen($contents) > 10000)
        {
            CHtmlForm::set_error('Contents should be between 1 and 10000 characters', 'contents');
        }
        
        $errors = CHtmlForm::get_errors();
        
        if ($errors)
        {
            CHtmlPage::set_last_error(implode('<br/>', $errors));
            return false;
        }

        $tm = new MessageTemplate($id);
        $tm->set_title($title);
        $tm->set_contents($contents);
        
        $tm->set_flag(MessageTemplate::SEND_MAIL, $send_mail);
        $tm->set_flag(MessageTemplate::SEND_SMS, $send_sms);
        $tm->set_flag(MessageTemplate::DELIVER_IN_QUEUE, $queue);
        
        if (!$tm->write())
        {
            CHtmlPage::set_last_error("Failed to save notification '{$tm->get_category()}'");
            return false;
        }
        
        CHtmlPage::set_last_info("Notification '{$tm->get_category()}' was updated");
        return true;
    }
}
?>