<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Text Form */
class MarkSpamTextForm extends CForm
{
    /** Initialize form contents */
    protected function init_contents(array $args)
    {
        @extract($args);
        
        $this->add_inner(new CHtmlHidden('is_spam', $is_spam));
        $this->set_confirm($confirm);
        
        $ctrl = new CHtmlText('value', $value);
        $this->add_inner($ctrl);
        
        $attrs = $btn_width ? array('style'=>"width:{$btn_width}px;") : null;
        $ctrl = new CHtmlSubmit($submit, $attrs);
        $ctrl->disable_on_click();
        $this->add_inner($ctrl);
    }
}
?>