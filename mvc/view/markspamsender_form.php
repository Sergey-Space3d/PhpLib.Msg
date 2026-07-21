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
        
        $table = new CHtmlTable();
        $this->add_inner($table);
        $ctrls = [];
        
        $this->add_inner(new CHtmlHidden('type', $type));
        $this->set_confirm($confirm);
        
        if (is_array($scores))
        {
        	$ctrl = new CHtmlCbox('score', $score);
        	$ctrl->add_items($scores);
        	$ctrls[] = $ctrl;
        }
        else
        {
        	$this->add_inner(new CHtmlHidden('score', $score));
        }
        
        if (is_array($values))
        {
        	$attrs = array('style'=>'min-width:165px;max-width:300px;');
        	$ctrl = new CHtmlCbox('value', $value, $attrs);
            $ctrl->add_items($values);
            $ctrls[] = $ctrl;
        }
        else
        {
            $this->add_inner(new CHtmlHidden('value', $value));
        }
        
        $attrs = $btn_width ? array('style'=>"width:{$btn_width}px;") : null;
        $ctrl = new CHtmlSubmit($submit, $attrs);
        $ctrl->disable_on_click();
        $ctrls[] = $ctrl;
        
        $table->add_row($ctrls);
    }
}
?>