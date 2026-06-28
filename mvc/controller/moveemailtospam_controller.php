<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Move Email To Spam Controller */
class MoveEmailToSpamController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
    	$mailbox_id = $this->get_value('mailbox_id');
        $msg_uid = $this->get_value('msg_uid');
        $folder = $this->get_value('folder');
        
        $mail_server = new EmailServer(new Mailbox($mailbox_id));
        
        if ($mail_server->open())
        {
            if ($mail_server->move($msg_uid, $folder))
            {
                $mail_server->close();
                CHtmlPage::set_last_info("Email {$msg_uid} was moved to {$folder} Folder");
                return true;
            }
            
            $mail_server->close();
        }
        
        $errors = implode('<br/>', EmailServer::pop_errors());
        CHtmlPage::set_last_error("Failed to move email {$msg_uid} to {$folder} Folder<br/>{$errors}");
        return false;  
    }
}
?>