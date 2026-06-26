<?php
// Copyright 2005-2021 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Delete Mailbox Controller */
class DeleteMailboxController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
        $server = $this->get_value('server');
        $username = $this->get_value('username');
        $filename = $this->get_value('filename');
        
        $mailboxes = SpamMan::Instance()->get_mailboxes($filename);
        
        foreach ($mailboxes as $svr=>&$userboxes)
        {
            if ($svr == $server)
            {
                foreach ($userboxes as $un=>$password)
                {
                    if ($un == $username)
                    {
                        // User mailbox found
                        if (count($userboxes) == 1) unset($mailboxes[$svr]);
                        else unset($userboxes[$un]);
                        
                        // Save mailboxes
                        SpamMan::Instance()->set_mailboxes($filename, $mailboxes);
                        CHtmlPage::set_last_info("Mailbox '{$username}' ({$server}) was deleted");
                        return true;
                    }
                }
            }
        }
        
        CHtmlPage::set_last_error("Failed to delete Mailbox '{$username}' ({$server})");
        return false;
    }
}
?>