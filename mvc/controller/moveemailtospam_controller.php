<?php
// Copyright 2005-2022 Sergey Shustov.
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
        $msg_uid = $this->get_value('msg_uid');
        $folder = $this->get_value('folder');
        $si = unserialize($this->get_value('server_info'));
        
        $mail_server = new EmailServer($si['server'], $si['port'], $si['service'], $si['mailbox'], $si['password']);
        
        if ($mail_server->open())
        {
            if ($mail_server->move($msg_uid, $folder))
            {
                $mail_server->close(true);
                CHtmlPage::set_last_info("Email {$msg_uid} was moved to {$folder} Folder");
                return true;
            }
        }
        
        $errors = implode('<br/>', $mail_server->pop_errors());
        CHtmlPage::set_last_error("Failed to move email {$msg_uid} to {$folder} Folder<br/>{$errors}");
        return false;  
    }
}
?>