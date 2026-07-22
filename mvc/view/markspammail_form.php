<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Mail Form */
class MarkSpamMailForm extends CForm
{
	/** Initialize form contents */
	protected function init_contents(array $args)
	{
		@extract($args);
		
		$this->add_inner(new CHtmlHidden('types', $types));
		$this->set_confirm($confirm);
		
		if (is_array($values))
		{
			$attrs = array('style'=>'min-width:165px;max-width:250px;');
			$val_ctrl = new CHtmlCbox('value', $value, $attrs);
			$val_ctrl->add_items($values);
			
			$div = new CHtmlElement('div', array('style'=>'margin:6px;text-align:right;'), $val_ctrl);
			$this->add_inner($div);
		}
		else
		{
			$this->add_inner(new CHtmlHidden('value', $value));
		}
		
		$div = new CHtmlElement('div', array('style'=>'margin:6px;text-align:right;'));
		$this->add_inner($div);
		
		if (is_array($scores))
		{
			$attrs = array('style'=>'max-width:60px;');
			$score_ctrl = new CHtmlCbox('score', $score, $attrs);
			$score_ctrl->add_items($scores);
			$div->add_inner($score_ctrl);
			$div->add_inner('&nbsp;&nbsp;');
		}
		else
		{
			$this->add_inner(new CHtmlHidden('score', $score));
		}
		
		$attrs = array('style'=>'float:right;');
		$submit = new CHtmlSubmit($submit, $attrs);
		$submit->disable_on_click();
		
		$div->add_inner($submit);
	}
}
?>