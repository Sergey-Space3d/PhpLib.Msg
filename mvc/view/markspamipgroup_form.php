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
        
        $confirm = null;
        $submit = null;
        
        if (is_array($ip_groups))
        {
            $ctrl = new CHtmlCbox('ip_group', $ip_group);
            $ctrl->add_items($ip_groups);
            $this->add_inner($ctrl);
            
            $confirm = "Confirm setting IP group as spam";
            $submit = "Set IP Group as Spam";
        }
        else
        {
            $this->add_inner(new CHtmlHidden('ip_group', $ip_group));
            
            $confirm = "Confirm marking IP group {$ip_group}";
            $submit = "Mark IP Group {$ip_group}";
        }
        
        $this->add_inner(new CHtmlHidden('is_spam', is_array($ip_groups)));
        $this->set_confirm($confirm);
        
        $ctrl = new CHtmlSubmit($submit);
        $ctrl->disable_on_click();
        $this->add_inner($ctrl);
    }
}
?>