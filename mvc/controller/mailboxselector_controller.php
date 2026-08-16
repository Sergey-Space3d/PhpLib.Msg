<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mailbox Selector Controller */
class MailboxSelectorController extends SelectorController
{
	/** The constructor */
	public function __construct($action, array $args = null)
	{
		parent::__construct($action, $args, 'Mailbox');
	}
	
	/** Returns selection name */
	public function get_sel_name() { return SELECTOR_MAILBOX_ID; }
	
	/** Returns array of instances */
	protected function get_items()
	{
		$items = [];
		foreach (MailboxMan::Instance()->get() as $mb) $items[$mb->get_id()] = $mb->get_username();
		return $items;
	}
	
	/** Called after the item is selected */
	protected function on_selected()
	{
		CHtmlForm::set_value(SELECTOR_SENDER_DOMAIN, null, true);
	}
}
?>