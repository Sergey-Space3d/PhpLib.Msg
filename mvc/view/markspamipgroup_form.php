<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Mark Spam Ip Group Form */
class MarkSpamIpGroupForm extends CForm
{
    /** Initialize form contents */
    protected function init_contents(array $args)
    {
        @extract($args);
        
        $this->add_inner(new CHtmlHidden('ip_group', $ip_group));
        $this->set_confirm("Confirm marking IP group {$ip_group} as spam");
        
        $ctrl = new CHtmlSubmit("Mark IP Group {$ip_group} as Spam");
        $ctrl->disable_on_click();
        $this->add_inner($ctrl);
    }
}
?>