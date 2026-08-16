<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Identify Spam View */
class IdentifySpamView extends ListView
{
    protected $m_emails = array();
    protected $m_errors = array();
    
    protected $m_ip_groups = array();
    protected $m_ips = array();
    protected $m_domains = array();
    protected $m_addresses = array();
    
    protected $m_show_headers = false;
    
    /** Initialize view contents */
    protected function init_contents(array $args)
    {
        $filter = 'UNSEEN';
        $max_len = 2000;
        Mailbox::label(new CDbLabel('Mailbox', 'Mailboxes'));
        
        $this->m_show_headers = (bool)CHtmlForm::get_value(SHOW_MAILBOX_HEADERS);
        
        $id = (int)CHtmlForm::get_value(SELECTOR_MAILBOX_ID);
        $mailboxes = $id ? array(new Mailbox($id)) : MailboxMan::Instance()->get();
        
        foreach ($mailboxes as $mailbox)
        {
        	$mail_server = new EmailServer($mailbox);
        	$this->m_emails = array_merge($this->m_emails, array_reverse($mail_server->read($filter, $max_len)));
        }
        
        foreach ($this->m_emails as $email) 
        {
            foreach ($email->SenderIpGroups as $ip_group)
            {
                $this->m_ip_groups[$ip_group]++;
            }
            
            foreach ($email->SenderDomains as $domain=>$ip)
            {
                $this->m_domains[$domain]++;
                if ($ip) $this->m_ips[$ip]++;
            }

            foreach ($email->SenderAddresses as $address)
            {
            	$this->m_addresses[$address]++;
            }
        }
        
        arsort($this->m_ip_groups);
        foreach ($this->m_ip_groups as $key=>&$val) $val = "{$key} ({$val})";
        
        arsort($this->m_ips);
        foreach ($this->m_ips as $key=>&$val) $val = "{$key} ({$val})";
        
        arsort($this->m_domains);
        foreach ($this->m_domains as $key=>&$val) $val = "{$key} ({$val})";
        
        arsort($this->m_addresses);
        foreach ($this->m_addresses as $key=>&$val) $val = "{$key} ({$val})";
        
        $sel_domip = CHtmlForm::get_value(SELECTOR_SENDER_DOMAIN);
        $is_domain = $sel_domip ? !is_numeric(substr($sel_domip, -2)) : false;
        
        if ($sel_domip)
        {
        	// Filter emails - after harvesting domains and IPs
        	$emails = array();
        	
        	foreach ($this->m_emails as $email)
        	{
        		if ($is_domain && !array_key_exists($sel_domip, $email->SenderDomains)) continue;
        		if (!$is_domain && !in_array($sel_domip, $email->SenderIpGroups)) continue;
        		$emails[] = $email;
        	}
        	
        	$this->m_emails = $emails;
        }

        $this->m_errors = EmailServer::pop_errors();
        
        parent::init_contents($args);
    }
    
    /** Returns headline title */
    protected function get_headline_title() { return 'Identify Spam'; }
    
    /** Returns array of listed items  */
    protected function get_items($obj, &$objs)
    {
        $items = array();
        
        if ($this->m_emails)
        {
            foreach ($this->m_emails as $email)
            {
                $items[] = array($this->make_email_el($email), $this->make_button_deck($email));
            }
        }
        else 
        {
            foreach ($this->m_errors as $error)
            {
                $items[] = array($error, '&nbsp;');
            }
        }
        
        return $items;
    }
    
    /** Returns theme items */
    protected function get_theme_items() { return 1; }
    
    /** Returns toolbar items */
    protected function get_toolbar_items()
    {
        $items = array();
        
        $attrs = array('style'=>'text-align:right;float:right;');
        $table = new CHtmlTable($attrs);
        $items[] = $table;
        
        $div = new CHtmlElement('div');
        $attrs = array('colspan'=>'100%', 'style'=>'background-color:#99CCCC;padding:6px 100px 6px 50px;');
        $table->add_row($div, $attrs);
        
        $args = array('all'=>true, 'max_width'=>210);
        $sel_form = CDispatcher::instance()->get_form("MailboxSelector", $args, array('style'=>'float:left;'));
        $div->add_inner($sel_form);
        
        $args = array('all'=>true, 'max_width'=>210, 'domains'=>$this->m_domains + $this->m_ip_groups, 'label'=>'Domains & IP Group');
        $sel_form = CDispatcher::instance()->get_form("SenderDomainSelector", $args, array('style'=>'float:left;'));
        $div->add_inner($sel_form);
        
        $args = array('show_headers'=>$this->m_show_headers);
        $headers_form = CDispatcher::instance()->get_form("ShowHeaders", $args, array('style'=>'float:right;'));
        $div->add_inner($headers_form);
        
        $btn_width = 170;
        
        if ($this->m_domains)
        {
        	$table->add_row('', array('colspan'=>'100%', 'height'=>'7px'));
        	
        	$attrs = array('style'=>'text-align:right;float:right;');
        	$args = array('values'=>$this->m_ip_groups, 'type'=>SpamFilter::TYPE_IP_GROUP, 'is_spam'=>true, 'btn_width'=>$btn_width);
            $ipgroup_form = CDispatcher::instance()->get_form("MarkSpamSender", $args, $attrs);
            
            $args = array('values'=>$this->m_ips, 'type'=>SpamFilter::TYPE_IP, 'is_spam'=>true, 'btn_width'=>$btn_width);
            $ip_form = CDispatcher::instance()->get_form("MarkSpamSender", $args, $attrs);
            
            $args = array('values'=>$this->m_domains, 'type'=>SpamFilter::TYPE_DOMAIN, 'is_spam'=>true, 'btn_width'=>$btn_width);
            $domain_form = CDispatcher::instance()->get_form("MarkSpamSender", $args, $attrs);
            
            $args = array('values'=>$this->m_addresses, 'type'=>SpamFilter::TYPE_FROM_ADDRESS, 'is_spam'=>true, 'btn_width'=>$btn_width);
            $address_form = CDispatcher::instance()->get_form("MarkSpamSender", $args, $attrs);
            
            $table->add_row(array($ipgroup_form, $domain_form));
            $table->add_row(array($ip_form, $address_form));
        }
        
        $args = array('is_spam'=>true, 'btn_width'=>$btn_width);
        $attrs = array('style'=>'text-align:right;float:right;background-color:#D2D6D0;margin:5px 0 5px 0;');
        $form = CDispatcher::instance()->get_form("MarkSpamText", $args, $attrs);
        $table->add_row($form, array('colspan'=>'100%'));

        return $items;
    }
    
    /** Returns list title */
    protected function get_title($obj) 
    { 
        $count = count($this->m_emails);
        $title = "{$count} NEW MESSAGE";
        if ($count == 0 || $count > 1) $title .= "S";
        
        return $title; 
    }
    
    /** Returns column names */
    protected function get_column_names() { return array('Email', 'Action'); }
    
    /** Set column attributes */
    protected function set_column_attrs(CHtmlTable $table)
    {
        $table->set_column_attrs(0, array('style'=>'text-align:left;vertical-align:top;padding:5px;width:500px;'));
        $table->set_column_attrs(1, array('style'=>'vertical-align:top;'));
    }
    
    protected function make_email_el(Email $email)
    {
        $el = new CHtmlElement('div', array('style'=>'text-wrap:wrap;word-break:break-all;width:490px;'));
        
        $div = new CHtmlElement('div', array('style'=>'padding-top:10px;font-weight:bold;'));
        $el->add_inner($div);
        
        $div->add_inner("UID: {$email->get_msg_uid()}<br/>");
        $str = implode(';', array_keys($email->get_to()));
        if (!$str) $str = 'n/a';
        $div->add_inner("TO: {$str}<br/>");
        $div->add_inner('<br/>');
        
        if ($this->m_show_headers)
        {
            $div->add_inner($email->Header);
            $div->add_inner('<br/><br/>');
        }
        else 
        {
            $str = $email->get_from();
            if (!$str) $str = 'n/a';
            $div->add_inner("FROM: {$str}<br/>");
            
            $str = $email->get_reply_to();
            if (!$str) $str = 'n/a';
            $div->add_inner("REPLY-TO: {$str}<br/>");
            
            $str = $email->get_sender();
            if (!$str) $str = 'n/a';
            $div->add_inner("SENDER: {$str}<br/>");
            
            $str = $email->get_return_path();
            if (!$str) $str = 'n/a';
            $div->add_inner("RETURN-PATH: {$str}<br/>");
            
            $div->add_inner('<br/>');
        }
        
        $glue = '<br/>&nbsp;&nbsp;';
        
        if ($email->SenderDomains)
        {
            $domains = array_keys($email->SenderDomains);
            
            if (count($domains) == 1)
            {
                $div->add_inner("DOMAIN: ".current($domains));
            }
            else
            {
                // Show list of domains
                $div->add_inner("DOMAINS:{$glue}");
                $div->add_inner(implode($glue, $domains));
            }
            
            $div->add_inner('<br/>');
            $ips = array_unique(array_filter($email->SenderDomains));
            
            if ($ips)
            {
                if (count($ips) == 1)
                {
                    $div->add_inner("IP: ".current($ips));
                }
                else
                {
                    // Show list of IPs
                    $div->add_inner("IPs:{$glue}");
                    $div->add_inner(implode($glue, $ips));
                }
                
                $div->add_inner('<br/>');
            }
        }
        
        if ($email->Filenames)
        {
            if (count($email->Filenames) == 1)
            {
                $div->add_inner("ATTACHED: ".current($email->Filenames));
            }
            else
            {
                // Show list of attachment filenames
                $div->add_inner("ATTACHED:{$glue}");
                $div->add_inner(implode($glue, $email->Filenames));
            }
            
            $div->add_inner('<br/>');
        }
        
        $div->add_inner('<br/>');
        $div->add_inner(new CHtmlElement('span', array('style'=>'color:green;'), $email->get_subject()));
        $div->add_inner('<br/><br/>');
        
        // Normalize message
        $content = nl2br($email->get_content('<br/>'));
        $el->add_inner($content);
        
        return $el;
    }
    
    /** Make button's deck */
    protected function make_button_deck(Email $email)
    {
        $table = new CHtmlTable(array('style'=>'float:right;'));

        $mark_count = 0;
        $spam = SpamFilterMan::Instance()->find_spam($email, $mark_count);
        
        $flags_to_desc = function($flags, $title, $value = null)
        {
            $desc = array($title);
            if ($value) $desc[] = $value;
            if ($flags & SpamFilter::MULTIPLE_SENDERS) $desc[] = 'Multiple senders';
            if ($flags & SpamFilter::MULTIPLE_SENDER_DOMAINS) $desc[] = 'Multiple sender domains';
            if ($flags & SpamFilter::INVALID_SENDER_DOMAIN_IP) $desc[] = 'Invalid sender domain\'s IP';
            if ($flags & SpamFilter::DATA_FILE_ATTACHED) $desc[] = 'Data file attached';
            if ($flags & SpamFilter::CALENDAR_FILE_ATTACHED) $desc[] = 'Calendar file attached';
            return implode('<br/>', $desc);
        };
        
        $style = 'margin:4px;padding:4px;';
        $info_style = 'text-wrap:wrap;word-break:break-all;max-width:350px;';
        
        if ($spam)
        {
            // Display the spam count, no actions nesessary
        	$attrs = array('style'=>$style.$info_style.'font-weight:bold;border:1px solid black;background-color:#FF5555;');
            $desc = $flags_to_desc($email->get_flags(), 'SPAM', $spam->get_value());
            $el = new CHtmlElement('div', $attrs, $desc);
            $table->add_row($el);
        }
        else
        {
        	$attrs = array('style'=>$style.$info_style.'font-weight:bold;border:1px solid grey;');
        	$desc = $flags_to_desc($email->get_flags(), "Spam score: {$mark_count} of ".SpamFilter::COUNT_THRESHOLD);
        	$el = new CHtmlElement('div', $attrs, $desc);
        	$table->add_row($el);
        	
        	if ($this->m_domains)
        	{
        		$args = array('email'=>$email);
        		$attrs = array('style'=>$style.'background-color:#99CCCC;');
        		$form = CDispatcher::instance()->get_form("MarkSpamMail", $args, $attrs);
        		$table->add_row($form);
        	}
            
            $args = array('mailbox_id'=>$email->get_mailbox_id(), 'msg_uid'=>$email->get_msg_uid(), 'folder'=>SpamFilter::FOLDER);
            $attrs = array('style'=>$style.'text-align:center;');
            $form = CDispatcher::instance()->get_form("MoveEmailToSpam", $args, $attrs);
            $table->add_row($form);
        }
        
        return $table;
    }
}
?>