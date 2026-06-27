<?php
// Copyright 2005-2021 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Add Mailbox Controller */
class AddMailboxController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
        $server = strtolower(trim($this->get_value('server')));
        $username = strtolower(trim($this->get_value('username')));
        $password = strtolower(trim($this->get_value('password')));
        $filename = $this->get_value('filename');
        
        $validator = new FieldValidator();
        $validator->MinPassword = 3;
        $validator->MaxPassword = 120;
        $validator->PasswordWhitespaceAllowed = true;
        
        $is_domain = $validator->is_domain($server);
        $is_email = $validator->is_email($username);
        $is_password = $validator->is_password($password);
        
        if (!$is_domain || !$is_email || !$is_password)
        {
            CHtmlPage::set_last_error($validator->get_errors());
            return false;
        }
        
        $mailboxes = SpamMan::Instance()->get_mailboxes($filename);
        if (!array_key_exists($server, $mailboxes)) $mailboxes[$server] = array();
        
        foreach ($mailboxes as $svr=>&$userboxes)
        {
            if ($svr == $server)
            {
                foreach ($userboxes as $un=>$pwd)
                {
                    if ($un == $username)
                    {
                        CHtmlPage::set_last_error("Mailbox '{$username}' ({$server}) already exists");
                        return false;
                    }
                }
                
                // Save mailboxes
                $userboxes[$username] = $password;
                SpamMan::Instance()->set_mailboxes($filename, $mailboxes);
                
                CHtmlPage::set_last_info("Mailbox '{$username}' ({$server}) was added");
                return true;
            }
        }
        
        // NOTE: should not reach this point
        CHtmlPage::set_last_error("Unknown error");
        return false;
    }
}
?>