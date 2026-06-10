<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Ip Form */
class MarkSpamIpForm extends CForm
{
    /** Initialize form contents */
    protected function init_contents(array $args)
    {
        @extract($args);
        
        $this->add_inner(new CHtmlHidden('ip', $ip));
        $this->set_confirm("Confirm marking IP {$ip}");
        
        $ctrl = new CHtmlSubmit("Mark IP {$ip}");
        $ctrl->disable_on_click();
        $this->add_inner($ctrl);
    }
}
?>