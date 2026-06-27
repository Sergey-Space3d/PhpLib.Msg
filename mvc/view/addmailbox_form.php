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
        $this->add_inner(new CHtmlHidden('filename', $filename));
        
        $table = new CHtmlTable();
        $this->add_inner($table);
        
        $attrs = array('style'=>'width:130px;', 'maxlength'=>100, 'autocomplete'=>'off');
        $ctrl1 = new CHtmlText('server', $server, $attrs);
        $ctrl2 = new CHtmlText('username', $username, $attrs);
        $ctrl3 = new CHtmlText('password', $password, $attrs);
        
        $ctrl4 = new CHtmlSubmit('Add Mailbox');
        $ctrl4->disable_on_click();
        
        $table->add_row(array($ctrl1, $ctrl2, $ctrl3, $ctrl4));
    }
}
?>