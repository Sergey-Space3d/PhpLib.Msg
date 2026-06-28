<?php
// Copyright 2005-2026 Sergey Shustov.
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
    	$id = (int)$this->get_value('id');
    	$mailbox = new Mailbox($id);
    	
    	if (MailboxMan::Instance()->delete($id))
    	{
    		CHtmlPage::set_last_info("Mailbox '{$mailbox->get_username()}' ({$mailbox->get_mail_server()}) was deleted");
    		return true;
    	}
    	
        CHtmlPage::set_last_error("Failed to delete mailbox '{$mailbox->get_username()}' ({$mailbox->get_mail_server()})");
        return false;
    }
}
?>