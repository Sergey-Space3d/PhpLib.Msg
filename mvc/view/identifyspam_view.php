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
    
    protected $m_use_headers = false;
    
    /** Initialize view contents */
    protected function init_contents(array $args)
    {
        $filter = 'UNSEEN';
        $max_len = 2000;
        
        $mailboxes = MailboxMan::Instance()->get('mail_server ASC, username ASC');
        
        foreach ($mailboxes as $mailbox)
        {
        	$mail_server = new EmailServer($mailbox);
        	$this->m_emails += array_reverse($mail_server->read($filter, $max_len));
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

            $address = $email->get_from();
            if ($address) $this->m_addresses[$address]++;
        }
        
        arsort($this->m_ip_groups);
        foreach ($this->m_ip_groups as $key=>&$val) $val = "{$key} ({$val})";
        
        arsort($this->m_ips);
        foreach ($this->m_ips as $key=>&$val) $val = "{$key} ({$val})";
        
        arsort($this->m_domains);
        foreach ($this->m_domains as $key=>&$val) $val = "{$key} ({$val})";
        
        arsort($this->m_addresses);
        foreach ($this->m_addresses as $key=>&$val) $val = "{$key} ({$val})";
        
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
                $items[] = array($this->make_email_el($email), $this->make_button_deck($email, false));
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
        
        $el = new CHtmlElement('div', array('style'=>'text-align:right;'));
        $items[] = $el;
        
        $btn_width = 220;
        
        if ($this->m_ip_groups)
        {
            $args = array('values'=>$this->m_ip_groups, 'type'=>SpamFilter::TYPE_IP_GROUP, 'is_spam'=>true, 'btn_width'=>$btn_width);
            $form = CDispatcher::instance()->get_form("MarkSpamSender", $args);
            $el->add_inner($form);
        }
        
        if ($this->m_ips)
        {
            $args = array('values'=>$this->m_ips, 'type'=>SpamFilter::TYPE_IP, 'is_spam'=>true, 'btn_width'=>$btn_width);
            $form = CDispatcher::instance()->get_form("MarkSpamSender", $args);
            $el->add_inner($form);
        }
        
        if ($this->m_domains)
        {
            $args = array('values'=>$this->m_domains, 'type'=>SpamFilter::TYPE_DOMAIN, 'is_spam'=>true, 'btn_width'=>$btn_width);
            $form = CDispatcher::instance()->get_form("MarkSpamSender", $args);
            $el->add_inner($form);
        }
        
        if ($this->m_addresses)
        {
            $args = array('values'=>$this->m_addresses, 'type'=>SpamFilter::TYPE_FROM_ADDRESS, 'is_spam'=>true, 'btn_width'=>$btn_width);
            $form = CDispatcher::instance()->get_form("MarkSpamSender", $args);
            $el->add_inner($form);
        }
        
        $args = array('is_spam'=>true, 'btn_width'=>$btn_width);
        $form = CDispatcher::instance()->get_form("MarkSpamText", $args);
        $el->add_inner($form);
        
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
        
        if ($this->m_use_headers)
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
    protected function make_button_deck(Email $email, $horizontal)
    {
        $form_view = new FormView($horizontal, array('style'=>'float:right;'));
        $forms = array();
        
        $arr = array_keys($email->get_to());
        $to = array_shift($arr);
        
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
        
        if ($spam)
        {
            // Display the spam count, no actions nesessary
            $attrs = array('style'=>'margin:5px;padding:10px;font-weight:bold;border:1px solid black;background-color:#FF5555;');
            $desc = $flags_to_desc($email->get_flags(), 'SPAM', $spam->get_value());
            $el = new CHtmlElement('div', $attrs, $desc);
            $form_view->add_row($el);
        }
        else
        {
            foreach (array_unique($email->SenderDomains) as $domain=>$ip)
            {
                if ($ip)
                {
                    $key = "MarkSpamIp{$ip}";
                    $args = array('value'=>$ip, 'type'=>SpamFilter::TYPE_IP);
                    $forms[$key] = CDispatcher::instance()->get_form("MarkSpamSender", $args);
                    $form_view->add_form($forms[$key]);
                }
            }
            
            foreach ($email->SenderIpGroups as $ip_group)
            {
                $key = "MarkSpamIpGroup{$ip_group}";
                $args = array('value'=>$ip_group, 'type'=>SpamFilter::TYPE_IP_GROUP);
                $forms[$key] = CDispatcher::instance()->get_form("MarkSpamSender", $args);
                $form_view->add_form($forms[$key]);
            }
            
            $args = array('mailbox_id'=>$email->get_mailbox_id(), 'msg_uid'=>$email->get_msg_uid(), 'folder'=>SpamFilter::FOLDER);
            $forms['MoveEmailToSpam'] = CDispatcher::instance()->get_form("MoveEmailToSpam", $args);
            $form_view->add_form($forms['MoveEmailToSpam']);
            
            $attrs = array('style'=>'margin:5px;padding:10px;font-weight:bold;border:1px solid grey;');
            $desc = $flags_to_desc($email->get_flags(), "Spam score: {$mark_count} of ".SpamFilter::COUNT_THRESHOLD);
            $el = new CHtmlElement('div', $attrs, $desc);
            $form_view->add_row($el);
        }
        
        return $form_view;
    }
}
?>