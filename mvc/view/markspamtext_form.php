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
        
        $tip_attrs = array('style'=>'width:350px;');
        $tip = 'Enter spam text, or regular expression<br/>
        for ex. /expression here/.<br/>
        You may use several phrases<br/>
        by separating them with | character, as in<br/>
        first|second|third';
        $ctrl = new CHtmlText('value', $value);
        $ctrl = new Tooltip($ctrl, $tip, $tip_attrs);
        $this->add_inner($ctrl);
        
        $attrs = $btn_width ? array('style'=>"width:{$btn_width}px;") : null;
        $ctrl = new CHtmlSubmit($submit, $attrs);
        $ctrl->disable_on_click();
        $this->add_inner($ctrl);
    }
}
?>