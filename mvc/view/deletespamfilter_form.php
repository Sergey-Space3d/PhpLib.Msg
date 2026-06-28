<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Delete Spam Filter Form */
class DeleteSpamFilterForm extends CForm
{
    /** Initialize form contents */
    protected function init_contents(array $args)
    {
        @extract($args);
        
        $this->set_confirm("Confirm deleting the spam filter");
        $this->add_inner(new CHtmlHidden('id', $id));
        
        $ctrl = new CHtmlSubmit('Delete', array('class'=>($class ? $class : 'deck')));
        $ctrl->disable_on_click();
        $this->add_inner($ctrl);
    }
}
?>