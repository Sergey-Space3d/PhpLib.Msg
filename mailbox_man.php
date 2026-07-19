<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mailbox Manager Class */
class MailboxMan extends CDbRecordManSingleton
{
	/** The constructor */
	protected function __construct()
	{
		parent::__construct(MsgDb::get_name().'.mailboxes', 'Mailbox', null, 'mail_server ASC, username ASC');
	}
	
	/** Returns true if the user/address already exists */
	public function has($username)
	{
		return $this->m_table->has("username='{$username}'");
	}
}
?>