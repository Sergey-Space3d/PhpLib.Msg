<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Text Form */
class MarkSpamTextForm extends CForm
{
    /** Initialize form contents */
    protected function init_contents(array $args)
    {
        @extract($args);
        
        $table = new CHtmlTable();
        $this->add_inner($table);
        $ctrls = [];
        
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
        
        $tip_attrs = array('style'=>'width:350px;');
        $tip = 'Enter spam text, or regular expression<br/>
        for ex. /expression here/.<br/>
        You may use several phrases<br/>
        by separating them with | character, as in<br/>
        first|second|third';
        $ctrl = new CHtmlText('value', $value, array('style'=>'width:400px;', 'maxlength'=>200, 'autocomplete'=>'off'));
        $ctrl = new Tooltip($ctrl, $tip, $tip_attrs);
        $ctrls[] = $ctrl;
        
        $attrs = $btn_width ? array('style'=>"width:{$btn_width}px;") : null;
        $ctrl = new CHtmlSubmit($submit, $attrs);
        $ctrl->disable_on_click();
        $ctrls[] = $ctrl;
        
        $table->add_row($ctrls);
    }
}
?>