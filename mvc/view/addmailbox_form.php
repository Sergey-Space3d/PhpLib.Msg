<?php
// Copyright 2005-2022 Sergey Shustov.
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
        $ctrl1 = new CHtmlText('mail_server', $mail_server, $attrs);
        $ctrl2 = new CHtmlText('username', $username, $attrs);
        $ctrl3 = new CHtmlText('password', $password, $attrs);
        $table->add_row(array($ctrl1, $ctrl2, $ctrl3));
        
        $ctrl4 = new CHtmlText('port', $port, $attrs);
        $ctrl5 = new CHtmlText('service', $service, $attrs);
        $ctrl6 = new CHtmlSubmit('Add Mailbox', array('style'=>'float:right;width:90%;'));
        $ctrl6->disable_on_click();
        $table->add_row(array($ctrl4, $ctrl5, $ctrl6));
    }
}
?>