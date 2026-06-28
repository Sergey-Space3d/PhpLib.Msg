<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** AddMailbox Form */
class AddMailboxForm extends CForm
{
    /** Initialize form contents */
    protected function init_contents(array $args)
    {
        @extract($args);
        
        $this->set_confirm("Confirm adding mailbox");
        
        $table = new CHtmlTable();
        $this->add_inner($table);
        
        $attrs = array('style'=>'width:180px;', 'maxlength'=>100, 'autocomplete'=>'off');
        $tip_attrs = array('style'=>'width:290px;');
        
        $ctrl1 = new CHtmlText('mail_server', $mail_server, $attrs);
        $ctrl1 = new Tooltip($ctrl1, 'Enter mail server, for ex. mail.yourserver.com', $tip_attrs);
        $ctrl2 = new CHtmlText('username', $username, $attrs);
        $ctrl2 = new Tooltip($ctrl2, 'Enter email address, for ex. yourname@yourdomain.com', $tip_attrs);
        $ctrl3 = new CHtmlText('password', $password, $attrs);
        $ctrl3 = new Tooltip($ctrl3, 'Enter email password', $tip_attrs);
        $table->add_row(array($ctrl1, $ctrl2, $ctrl3));
        
        $ctrl4 = new CHtmlText('port', $port, $attrs);
        $ctrl4 = new Tooltip($ctrl4, 'Enter port number<br/>(993 for IMAP,<br/>110 for POP3)', $tip_attrs);
        $ctrl5 = new CHtmlText('service', $service, $attrs);
        $ctrl5 = new Tooltip($ctrl5, 'Enter service<br/>(/imap/ssl for IMAP,<br/>/pop3/notls for POP3)', $tip_attrs);
        $ctrl6 = new CHtmlSubmit('Add Mailbox', array('style'=>'float:right;width:90%;'));
        $ctrl6->disable_on_click();
        $table->add_row(array($ctrl4, $ctrl5, $ctrl6));
    }
}
?>