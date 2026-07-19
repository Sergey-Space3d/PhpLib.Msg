<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Show Headers Form */
class ShowHeadersForm extends CForm
{
	/** Initialize form contents */
	protected function init_contents(array $args)
	{
		@extract($args);
		
		$ctrl = new CHtmlCheckbox('show_headers', true, $show_headers);
		$ctrl->set_attr('onchange', 'this.form.submit();');
		
		$this->add_inner($ctrl);
		$this->add_inner('Show email headers');
	}
}
?>