<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Sender Form */
class ClearSpamCountForm extends CForm
{
    /** Initialize form contents */
    protected function init_contents(array $args)
    {
        $this->set_confirm('Confirm clearing spam count for all filters');
        
        $ctrl = new CHtmlSubmit('Clear Spam Count');
        $ctrl->disable_on_click();
        $this->add_inner($ctrl);
    }
}
?>