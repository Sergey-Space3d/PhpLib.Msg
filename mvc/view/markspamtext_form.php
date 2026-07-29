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
        
        $this->set_confirm($confirm);
        
        $attrs = array('align'=>'center', 'style'=>'margin-top:8px;margin-bottom:5px;');
        $table = new CHtmlTable($attrs);
        $this->add_inner($table);
        $ctrls = [];
        
        $tip = 'Use greedy match to allow<br/>any text between words';
        $ctrl = new CHtmlCheckbox('greedy_match', true, $greedy_match);
        $ctrls[] = new Tooltip($ctrl, $tip, array('style'=>'width:240px;'));
        $ctrls[] = 'Greedy match&nbsp;&nbsp;&nbsp;';
        
        $tip = 'Case sensitive match';
        $ctrl = new CHtmlCheckbox('case_sensitive', true, $case_sensitive);
        $ctrls[] = new Tooltip($ctrl, $tip, array('style'=>'width:220px;'));
        $ctrls[] = 'Case sensitive&nbsp;&nbsp;&nbsp;';
        
        $tip = 'Whole word only match';
        $ctrl = new CHtmlCheckbox('whole_word', true, $whole_word);
        $ctrls[] = new Tooltip($ctrl, $tip, array('style'=>'width:220px;'));
        $ctrls[] = 'Whole word&nbsp;&nbsp;';
        
        $table->add_row($ctrls);
        
        $attrs = array('style'=>'margin:0px 3px 8px 3px;');
        $table = new CHtmlTable($attrs);
        $this->add_inner($table);
        $ctrls = [];
        
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
        
        $tip = 'Enter spam text here.<br/>
        You may use several phrases<br/>
        by separating them with | character,<br/> 
		as in first|second|third';
        $ctrl = new CHtmlText('value', $value, array('style'=>'width:400px;', 'maxlength'=>200, 'autocomplete'=>'off'));
        $ctrls[] = new Tooltip($ctrl, $tip, array('style'=>'width:330px;'));
        
        $attrs = $btn_width ? array('style'=>"width:{$btn_width}px;") : null;
        $ctrl = new CHtmlSubmit($submit, $attrs);
        $ctrl->disable_on_click();
        $ctrls[] = $ctrl;
        
        $table->add_row($ctrls);
    }
}
?>