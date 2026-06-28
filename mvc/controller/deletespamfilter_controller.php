<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Delete Spam Filter Controller */
class DeleteSpamFilterController extends CController
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
        $spam_filter = new SpamFilter($id);

        if (SpamFilterMan::Instance()->delete($id))
        {
            CHtmlPage::set_last_info("Spam Filter '{$spam_filter->get_value()}' was deleted");
            return true;
        }
        
        CHtmlPage::set_last_error("Failed to delete Spam Filter '{$spam_filter->get_value()}'");
        return false;
    }
}
?>