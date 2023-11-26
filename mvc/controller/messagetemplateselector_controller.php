<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Message Template Selector Controller */
class MessageTemplateSelectorController extends SelectorController
{
    /** The constructor */
    public function __construct($action, array $args = null)
    {
        parent::__construct($action, $args, 'MessageTemplate');
    }
    
    /** Returns selection name */
    public function get_sel_name() { return SELECTOR_NOTIFICATION_ID; }
    
    /** Returns array of instances */
    protected function get_items()
    {
        $items = array();
        $templates = MessageTemplateMan::Instance()->get();
        
        if ($templates) foreach ($templates as $template)
        {
            $items[$template->get_id()] = $template->get_category();
        }
        
        asort($items);
        return $items;
    }
}
?>