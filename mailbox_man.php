<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mailbox Manager Class */
class MailboxMan extends CDbRecordManSingleton
{
	/** The constructor */
	protected function __construct()
	{
		parent::__construct(MsgDb::get_name().'.mailboxes', 'Mailbox');
	}
	
	/** Returns true if the user/address is already defined for the mail server */
	public function has($mail_server, $username)
	{
		return $this->m_table->has("mail_server='{$mail_server}' AND username='{$username}'");
	}
}
?>