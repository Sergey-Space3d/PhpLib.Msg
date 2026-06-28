<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Setup Spam Mailboxes View */
class SetupSpamMailboxesView extends ListView
{
    /** Returns headline title */
    protected function get_headline_title() { return 'Setup Spam Mailboxes'; }
    
    /** Returns array of listed items  */
    protected function get_items($obj, &$objs)
    {
        $items = array();
        $mailboxes = MailboxMan::Instance()->get('mail_server ASC, username ASC');
        
        if ($mailboxes) foreach ($mailboxes as $mailbox)
        {
        	$items[] = array(
        		$mailbox->get_mail_server(),
        		$mailbox->get_username(),
        		$mailbox->get_port(),
        		$mailbox->get_service(),
        		$this->make_button_deck($mailbox, true),
        	);
        }

        return $items;
    }
    
    /** Returns toolbar items */
    protected function get_toolbar_items()
    {
        $items = array();
        
        $el = new CHtmlElement('div', array('style'=>'float:right;'));
        $items[] = $el;
        
        $form = CDispatcher::instance()->get_form("AddMailbox");
        $el->add_inner($form);
        
        return $items;
    }
    
    /** Returns list title */
    protected function get_title($obj) { return 'Spam Mailboxes'; }
    
    /** Returns column names */
    protected function get_column_names() { return array('Mail Server', 'Address', 'Port', 'Service', 'Action'); }
    
    /** Make button's deck */
    protected function make_button_deck(Mailbox $mailbox, $horizontal)
    {
        $form_view = new FormView($horizontal, array('style'=>'float:right;'));
        $forms = array();
        
        $form = CDispatcher::instance()->get_form("DeleteMailbox", array('id'=>$mailbox->get_id()));
        $form_view->add_form($form);
        
        return $form_view;
    }
}
?>