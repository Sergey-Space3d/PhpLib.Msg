<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Clear Spam Count Controller */
class ClearSpamCountController extends CController
{
    /** Initialize the form */
    protected function initialize()
    {
        $this->enable_post(true);
    }
    
    /** Process the form */
    protected function process()
    {
    	$spam_filters = SpamFilterMan::Instance()->get();
        
        if ($spam_filters)
        {
            foreach ($spam_filters as $sf)
            {
                $sf->set_spam_count(0, true);
            }
            
            CHtmlPage::set_last_info("Spam count was cleared for all filters");
            return true;
        }
        
        CHtmlPage::set_last_error("No spam filters found");
        return false;
    }
}
?>