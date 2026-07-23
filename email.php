<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

class Email extends QueueMessage
{
	const BR = '<br/>';
	const CRLF = "\r\n";
	
	/** Test address */
	public static $TestTo = null;
	
	/** Enable/disable sending msgs */
	public static $Disabled = false;
	
	/** Email headers */
	public $Header = null;
	
	/** Collection of attachment filenames */
	public $Filenames = array();
	
	/** Collection of sender domains, as [domain]=>[IP] */
	public $SenderDomains = array();
	
	/** Collection of sender IP groups */
	public $SenderIpGroups = array();
	
	/** Collection of sender addresses */
	public $SenderAddresses = array();
	
	/** Collection of sender names */
	public $SenderNames = array();
	
	protected $mailbox_id = 0;
	protected $msg_uid = null;
    
	/** The constructor */
	public function __construct($subject=null, $from=null, $from_name=null, $to=null, $to_name=null)
	{
	    parent::__construct(MsgDb::get_name().'.mail_queue');
	    
	    $this->Header = new EmailHeader();
	    if ($subject) $this->set_subject($subject);
	    if ($from) $this->set_from($from, $from_name);
	    if ($to) $this->set_to($to, $to_name);
	}
	
	public function get_mailbox_id() { return $this->mailbox_id; }
	public function set_mailbox_id($val) { $this->mailbox_id = (int)$val; }
	
	public function get_msg_uid() { return $this->msg_uid; }
	public function set_msg_uid($val) { $this->msg_uid = $val; }
	
	/** Get subject line */
	public function get_subject() { return $this->Header->get(['SUBJECT']); }
	
	/** Set subject line */
	public function set_subject($val) { $this->Header->set(['SUBJECT'], $val); }
	
	// Sender mailboxes -----------------------------------------------------
	
	/** Get FROM address (name is optional) */
	public function get_from(&$name = null) { return $this->get_address('FROM', false, $name); }
	
	/** Set FROM address (name is optional). Returns true on success */
	public function set_from($val, $name = null) { return $this->set_address('FROM', $val, $name); }
	
	/** Get REPLY_TO address (name is optional) */
	public function get_reply_to(&$name = null) { return $this->get_address('REPLY_TO', false, $name); }
	
	/** Set REPLY_TO address (name is optional). Returns true on success */
	public function set_reply_to($val, $name = null) { return $this->set_address('REPLY_TO', $val, $name); }
	
	/** Get SENDER address (name is optional) */
	public function get_sender(&$name = null) { return $this->get_address('SENDER', false, $name); }
	
	/** Set SENDER address (name is optional). Returns true on success */
	public function set_sender($val, $name = null) { return $this->set_address('SENDER', $val, $name); }
	
	/** Get RETURN_PATH address (name is optional) */
	public function get_return_path(&$name = null) { return $this->get_address('RETURN_PATH', false, $name); }
	
	/** Set RETURN_PATH address (name is optional). Returns true on success */
	public function set_return_path($val, $name = null) { return $this->set_address('RETURN_PATH', $val, $name); }
	
	// Receiver mailboxes ---------------------------------------------------
	
	/** Get TO collection, as [address]=>[name] */
	public function get_to() { return $this->get_address('TO'); }
	
	/** Set TO address (name is optional). Returns true on success */
	public function set_to($val, $name = null) { return $this->set_address('TO', $val, $name); }
	
	/** Add TO address (name is optional). Returns true on success */
	public function add_to($val, $name = null) { return $this->add_address('TO', $val, $name); }
	
	/** Get CC collection, as [address]=>[name] */
	public function get_cc() { return $this->get_address('CC'); }
	
	/** Set CC address (name is optional). Returns true on success */
	public function set_cc($val, $name = null) { return $this->set_address('CC', $val, $name); }
	
	/** Add CC address (name is optional). Returns true on success */
	public function add_cc($val, $name = null) { return $this->add_address('CC', $val, $name); }
	
	/** Get BCC collection, as [address]=>[name] */
	public function get_bcc() { return $this->get_address('BCC'); }
	
	/** Set BCC address (name is optional). Returns true on success */
	public function set_bcc($val, $name = null) { return $this->set_address('BCC', $val, $name); }
	
	/** Add BCC address (name is optional). Returns true on success */
	public function add_bcc($val, $name = null) { return $this->add_address('BCC', $val, $name); }
	
	// Header processing ----------------------------------------------------
	
	/** Get mailing address from header */
	protected function get_address($node, $return_array = true, &$name = null)
	{
	    $arr = $this->Header->get([$node]);
	    
	    if (is_array($arr))
	    {
	        $ret = array();
	        
	        foreach ($arr as $el)
	        {
	            if (!is_array($el)) continue;
	            $mailbox = $el['MAILBOX'];
	            $host = $el['HOST'];
	            $_name = $el['PERSONAL'];
	            
	            if ($mailbox && $host)
	            {
	                $addr = "{$mailbox}@{$host}";
	                
	                if ($return_array)
	                {
	                    // Add address to collection
	                    $ret[$addr] = $_name;
	                }
	                else
	                {
	                    // Return first address
	                    if ($name !== null) $name = $_name;
	                    return $addr;
	                }
	            }
	        }
	        
	        return $return_array ? $ret : null;
	    }
	    
	    return null;
	}
	
	/** Set mailing address to header */
	protected function set_address($node, $val, $name)
	{
	    // Reset the mailbox
	    $this->Header->set("{$node}ADDRESS", '');
	    $this->Header->set([$node], array());
	    
	    return $this->add_address($node, $val, $name);
	}
	
	/** Set mailing address to header */
	protected function add_address($node, $val, $name)
	{
	    if (!is_array($val)) $val = array($val=>$name);
	    $set_node = true;
	    $addrs = array();
	    
	    foreach ($val as $addr=>$_name)
	    {
	        if ($this->validate($addr))
	        {
	            if ($set_node)
	            {
	                // Set the address node once
	                $set_node = false;
	                $this->Header->set("{$node}ADDRESS", self::normalize_header_address($addr, $_name));
	            }
	            
	            $arr = explode('@', $addr);
	            $arr = array('MAILBOX'=>$arr[0], 'HOST'=>$arr[1]);
	            if ($_name) $arr = array('PERSONAL'=>$_name) + $arr;
	            $addrs[] = $arr;
	        }
	    }
	    
	    if (!$addrs) return false;
	    
	    $this->Header->set([$node], $addrs);
	    return true;
	}
	
	// Validation -----------------------------------------------------------
	
	/** Returns true if the record is valid */
	public function verify() 
	{
	    return $this->validate($this->get_from()) && $this->validate($this->get_to());
	}
	
	/** Validate email address */
	public function validate($address)
	{
	    if (!$address) return false;
	    $addresses = is_array($address) ? $address : array($address);
	    
	    foreach ($addresses as $address)
	    {
    	    $address = trim(strtolower($address));
    	    $n_chars = strlen($address);
    	    
    	    if ($n_chars < 6 || // consider minimum 6 characters (a@b.tv)
    	        ctype_alpha($address[0]) == false || // first char to be alpha
    	        ctype_alnum($address[$n_chars - 1]) == false) // last char to be alphanum
    	    {
    	        return false;
    	    }
    	    
    	    // Check the pattern
    	    $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i';
    	    
    	    if (@preg_match($regex, $address) == false)
    	    {
    	        return false;
    	    }
	    }
	    
	    return true;
	}
	
	// Composing/sending email ----------------------------------------------
	
	/** Append original email */
	public function append_original_email(Email $email)
	{
	    $arr = $email->get_content();
	    
	    if ($arr)
	    {
	        $content = implode(Email::BR, $arr);
	        
	        $this->add_newline();
	        $this->add_newline();
	        $this->add_line('----- Original Message -----');
	        $this->add_line($content);
	        $this->add_newline();
	        $this->add_line('-----------');
	    }
	}
	
	/** Attach data to email. Returns content's id, to be referenced in HTML */
	public function attach($data, $type, $name)
	{
	    static $cid = 1;
	    $cur_cid = "cid{$cid}";
	    $cid++;
	    
	    $arr = array();
	    $arr['data'] = base64_encode($data);
	    $arr['type'] = $type;
	    $arr['name'] = $name;
	    $arr['cid'] = $cur_cid;
	    
	    $this->attachments[] = $arr;
	    return $cur_cid;
	}
	
	/** Normalize header address (RFC 2822) */
	protected static function normalize_header_address($address, $name)
	{
	    if (!$address) return null;
	    return $name ? "{$name} <{$address}>" : $address;
	}

	/** Send the message */
	public function send($queue = false)
	{
	    if (self::$Disabled) return true;
	    
	    if ($this->validate($this->get_from()) && $this->validate($this->get_to()))
	    {
	        // Normalize header addresses (RFC 2822)
	        $normalize_header_addresses = function(array $addresses)
	        {
	            $arr = array();
	            foreach ($addresses as $address=>$name)
	            {
	                $arr[] = self::normalize_header_address($address, $name);
	            }
	            return implode(', ', $arr);
	        };
	        
	        // Make addresses and subject
	        
	        $_name = '';
	        $from = $this->get_from($_name);
	        $from = self::normalize_header_address($from, $_name);
	        
	        $reply_to = $this->get_reply_to($_name);
	        $reply_to = self::normalize_header_address($reply_to, $_name);
	        if (!$reply_to) $reply_to = $from;
	        
	        $return_path = $this->get_return_path($_name);
	        $return_path = self::normalize_header_address($return_path, $_name);
	        if (!$return_path) $return_path = $from;
	        
	        $sender = $this->get_sender($_name);
	        $sender = self::normalize_header_address($sender, $_name);
	        
	        $to = self::$TestTo ? array(self::$TestTo=>'Test') : $this->get_to();
	        $to = $normalize_header_addresses($to);
	        
	        $cc = $normalize_header_addresses($this->get_cc());
	        $bcc = $normalize_header_addresses($this->get_bcc());
	        
	        $subject = $this->get_subject();
	        if (!$subject) $subject = 'No subject';
	        if (self::$TestTo) $subject .= " [{$to}]";
	        
	        // Compose header
	        
	        $uniqid = md5(uniqid(time()));
	        $boundary = "--==_mimepart_".$uniqid;
	        
	        $headers = array();
    		$headers[] = "MIME-Version: 1.0";
    		
    		if ($this->attachments)
    		{
    		    $headers[] = "Content-type: multipart/related;";
    		    $headers[] = "  boundary={$boundary};";
    		    $headers[] = "  charset=UTF-8";
    		}
    		else
    		{
    		    $headers[] = "Content-type: text/html; charset=UTF-8";
    		}
    		
    		$headers[] = "Message-ID: <{$uniqid}>";
    		$headers[] = "Date: ".gmdate('D, d M Y H:i:s', time());
    		$headers[] = "X-Mailer: PHP/".phpversion();
    		
    		$headers[] = "From: {$from}";
    		$headers[] = "Reply-to: {$reply_to}";
    		$headers[] = "Return-Path: {$return_path}";
    		if ($sender) $headers[] = "Sender: {$sender}";
    		if ($to) $headers[] = "To: {$to}";
    		if ($cc) $headers[] = "Cc: {$cc}";
    		if ($bcc) $headers[] = "Bcc: {$bcc}";
    		
    		$headers = implode(Email::CRLF, $headers).Email::CRLF;
    		
    		// Make content
    		
    		$content = '';
    		
    		if ($this->attachments)
    		{
    		    $content = array();
    		    $content[] = "--{$boundary}";
    		    $content[] = "Content-Type: text/html; charset=UTF-8";
    		    $content[] = "Content-Transfer-Encoding: 7-bit".Email::CRLF;
    		    $content[] = implode(Email::BR, $this->content).Email::CRLF;
    		    
    		    foreach ($this->attachments as $attached)
    		    {
    		        $content[] = "--{$boundary}";
    		        $content[] = "Content-Type: {$attached['type']}";
    		        $content[] = "Content-ID: <{$attached['cid']}>";
    		        $content[] = "Content-Transfer-Encoding: base64";
    		        $content[] = "Content-Disposition: inline; filename=\"{$attached['name']}\"".Email::CRLF;
    		        $content[] = chunk_split($attached['data']);
    		    }
    		    
    		    $content[] = "--{$boundary}--";
    		    $content = implode(Email::CRLF, $content).Email::CRLF;
    		}
    		else
    		{
    		    $content = implode(Email::BR, $this->content);
    		}
    		
    		try 
    		{
                return $queue ? $this->write() : @mail($to, $subject, $content, $headers);
    		} 
    		catch (Exception $e) 
    		{ 
    		    $this->set_last_error($e->getMessage());
    		    return false; 
    		}
	    }
	    
	    return false;
	}
}
?>