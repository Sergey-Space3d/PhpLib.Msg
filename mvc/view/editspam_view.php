<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Edit Spam View */
class EditSpamView extends ListView
{
    /** Returns headline title */
    protected function get_headline_title() { return 'Edit Spam Filters'; }
    
    /** Returns array of listed items  */
    protected function get_items($obj, &$objs)
    {
        $items = array();
        $spamFilters = SpamFilterMan::Instance()->get();
        
        if ($spamFilters) foreach ($spamFilters as $sf)
        {
        	$score = $sf->get_score();
            if ($score >= SpamFilter::COUNT_THRESHOLD) $score = CHtmlElement::wrap($score, 'b');
            $items[] = array(
                $sf->get_value(),
                $this->type_to_string($sf->get_type()),
                $score,
                $sf->get_spam_count(),
                $this->make_button_deck($sf, true),
            );
        }
        
        return $items;
    }
    
    /** Convert spam's type to string */
    protected function type_to_string($type)
    {
        switch ($type)
        {
            case SpamFilter::TYPE_IP_GROUP: return 'IP Group';
            case SpamFilter::TYPE_IP: return 'IP';
            case SpamFilter::TYPE_DOMAIN: return 'Domain';
            case SpamFilter::TYPE_FROM_ADDRESS: return 'From Address';
            case SpamFilter::TYPE_TEXT: return 'Text';
            default: return 'Unknown';
        }
    }
    
    /** Returns toolbar items */
    protected function get_toolbar_items()
    {
        $items = array();
        
        $el = new CHtmlElement('div', array('style'=>'text-align:right;'));
        $items[] = $el;
        
        $form = CDispatcher::instance()->get_form("ClearSpamCount");
        $el->add_inner($form);
        
        return $items;
    }
    
    /** Returns list title */
    protected function get_title($obj) { return 'Spam Filters'; }
    
    /** Returns column names */
    protected function get_column_names() { return array('Value', 'Type', 'Score', 'Spam Count', 'Action'); }
    
    /** Set column attributes */
    protected function set_column_attrs(CHtmlTable $table)
    {
        $table->set_column_attrs(array(2, 3), array('style'=>'text-align:center;'));
    }
    
    /** Make button's deck */
    protected function make_button_deck(SpamFilter $spam_filter, $horizontal)
    {
        $form_view = new FormView($horizontal, array('style'=>'float:right;'));
        $forms = array();
        
        if ($spam_filter->get_type() == SpamFilter::TYPE_TEXT)
        {
        	$args = array('id'=>$spam_filter->get_id());
        	$form = CDispatcher::instance()->get_form("EditSpamText", $args);
        	$forms['Edit'] = new CollapsedForm($form, 'Edit', array('class'=>'deck'), false);
        	$form_view->add_form($forms['Edit']);
        }
        
        $args = array('id'=>$spam_filter->get_id());
        $forms['Delete'] = CDispatcher::instance()->get_form("DeleteSpamFilter", $args);
        $form_view->add_form($forms['Delete']);
        
        if (isset($forms['Edit']))
        {
        	CollapsedForm::init_display($forms['Edit'], array_diff_key($forms, array('Edit'=>'')), true);
        }
        
        return $form_view;
    }
}
?>