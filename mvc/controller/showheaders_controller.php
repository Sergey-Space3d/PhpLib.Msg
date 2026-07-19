<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Show Headers Controller */
class ShowHeadersController extends CController
{
	/** Initialize the form */
	protected function initialize()
	{
		$this->enable_post(false);
	}
	
	/** Process the form */
	protected function process()
	{
		$show_headers = (bool)$this->get_value('show_headers');
		CHtmlForm::set_value(SHOW_MAILBOX_HEADERS, $show_headers, true);
		return true;
	}
}
?>