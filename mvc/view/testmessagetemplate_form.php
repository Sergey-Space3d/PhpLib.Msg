<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Test Message Template Form */
class TestMessageTemplateForm extends CForm
{
    /** Initialize form contents */
    protected function init_contents(array $args)
    {
        @extract($args);
        
        $this->add_inner(new CHtmlHidden('id', $id));
        
        $ctrl = new CHtmlSubmit('Send Test Message');
        $ctrl->disable_on_click();
        $this->add_inner($ctrl);
    }
}
?>