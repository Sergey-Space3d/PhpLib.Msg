<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Move Email To Spam Form */
class MoveEmailToSpamForm extends CForm
{
    /** Initialize form contents */
    protected function init_contents(array $args)
    {
        @extract($args);
        
        $this->add_inner(new CHtmlHidden('mailbox_id', $mailbox_id));
        $this->add_inner(new CHtmlHidden('msg_uid', $msg_uid));
        $this->add_inner(new CHtmlHidden('folder', $folder));
        
        $this->set_confirm("Confirm moving email {$msg_uid}");
        
        $ctrl = new CHtmlSubmit("Move Email {$msg_uid} to {$folder} Folder");
        $ctrl->disable_on_click();
        $this->add_inner($ctrl);
    }
}
?>