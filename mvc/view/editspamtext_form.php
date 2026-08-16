<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Edit Spam Text Form */
class EditSpamTextForm extends CForm
{
	/** Initialize form contents */
	protected function init_contents(array $args)
	{
		@extract($args);
		
		$this->set_confirm($confirm);
		$this->add_inner(new CHtmlHidden('id', $id));
		
		$attrs = array('align'=>'right', 'style'=>'background-color:#99CCCC;padding:5px;');
		$table = new CHtmlTable($attrs);
		$this->add_inner($table);
		$ctrls = [];
		
		$tip = 'Select spam score';
		$ctrl = new CHtmlCbox('score', $score);
		$ctrl->add_items($scores);
		$ctrls[] = new Tooltip($ctrl, $tip, array('style'=>'width:210px;margin-left:-100px;'));
		
		$tip = 'Edit regular expression';
		$attrs = array('style'=>'width:200px;', 'maxlength'=>255, 'autocomplete'=>'off', 'spellcheck'=>'false');
		$ctrl = new CHtmlText('value', $value, $attrs);
		$ctrls[] = new Tooltip($ctrl, $tip, array('style'=>'width:210px;margin-left:-100px;'));
		
		$style = 'color:white;margin:-5px 0px 5px 10px;width:22px;height:22px;float:right;cursor:pointer;';
		$el = new CHtmlElement('div', array('style'=>$style), 'x');
		$ctrls[] = $this->hotspot = $el;
		
		$table->add_row($ctrls);
		$ctrls = [];

		$attrs = array('style'=>"margin:5px;");
		$ctrl = new CHtmlSubmit($submit, $attrs);
		$ctrl->disable_on_click();
		$ctrls[] = $ctrl;
		
		$table->add_row($ctrls, array('colspan'=>'100%', 'align'=>'center'));
	}
}
?>