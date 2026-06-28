<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Sender Form */
class MarkSpamSenderForm extends CForm
{
    /** Initialize form contents */
    protected function init_contents(array $args)
    {
        @extract($args);
        
        $this->add_inner(new CHtmlHidden('type', $type));
        $this->add_inner(new CHtmlHidden('is_spam', $is_spam));
        
        $this->set_confirm($confirm);
        
        if (is_array($values))
        {
            $ctrl = new CHtmlCbox('value', $value);
            $ctrl->add_items($values);
            $this->add_inner($ctrl);
        }
        else
        {
            $this->add_inner(new CHtmlHidden('value', $value));
        }
        
        $attrs = $btn_width ? array('style'=>"width:{$btn_width}px;") : null;
        $ctrl = new CHtmlSubmit($submit, $attrs);
        $ctrl->disable_on_click();
        $this->add_inner($ctrl);
    }
}
?>