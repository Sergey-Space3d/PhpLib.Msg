<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Sender Domain Selector Controller */
class SenderDomainSelectorController extends SelectorController
{
	/** The constructor */
	public function __construct($action, array $args = null)
	{
		$class = $args['label'] ? $args['label'] : 'Sender Domain';
		parent::__construct($action, $args, $class);
	}
	
	/** Returns selection name */
	public function get_sel_name() { return SELECTOR_SENDER_DOMAIN; }
	
	/** Returns array of instances */
	protected function get_items()
	{
		$items = $this->args['domains'];
		return is_array($items) ? $items : array();
	}
}
?>