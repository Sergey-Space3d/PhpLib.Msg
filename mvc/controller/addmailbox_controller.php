<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Add Mailbox Controller */
class AddMailboxController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
    	$mail_server = self::get_value('mail_server'); //
    	$this->set_arg('mail_server', $mail_server);
    	
    	$port = self::get_value('port');
    	$this->set_arg('port', $port ? $port : '993');
    	
    	$service = self::get_value('service');
    	$this->set_arg('service', $service ? $service : '/imap/ssl');
    	
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
        $mail_server = strtolower(trim($this->get_value('mail_server')));
        $username = strtolower(trim($this->get_value('username')));
        $password = trim($this->get_value('password'));
        $port = (int)$this->get_value('port');
        $service = trim($this->get_value('service'));
        
        $validator = new FieldValidator();
        $validator->MinPassword = 3;
        $validator->MaxPassword = 100;
        $validator->PasswordWhitespaceAllowed = true;
        
        $is_domain = $validator->is_domain($mail_server);
        $is_email = $validator->is_email($username);
        $is_password = $validator->is_password($password);
        
        if (!$is_domain || !$is_email || !$is_password)
        {
            CHtmlPage::set_last_error($validator->get_errors());
            return false;
        }
        
        if ($port <= 0)
        {
        	CHtmlPage::set_last_error("Port Number shall be a positive number");
        	return false;
        }
        
        if (strlen($service) == 0)
        {
        	CHtmlPage::set_last_error("Invalid service string");
        	return false;
        }
        
        if (MailboxMan::Instance()->has($username))
        {
        	CHtmlPage::set_last_error("Mailbox '{$username}' ({$mail_server}) already exists");
        	return false;
        }
        
        $mailbox = new Mailbox();
        $mailbox->set_mail_server($mail_server);
        $mailbox->set_username($username);
        $mailbox->set_password($password);
        $mailbox->set_port($port);
        $mailbox->set_service($service);
        
        if ($mailbox->write())
        {
        	CHtmlPage::set_last_info("Mailbox '{$username}' ({$mail_server}) was added");
        	return true;
        }
        
        CHtmlPage::set_last_error("Failed to save mailbox '{$username}' ({$mail_server})");
        return false;
    }
}
?>