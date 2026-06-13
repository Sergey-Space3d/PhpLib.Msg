<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

class Email extends QueueMessage
{
	const BR = '<br/>';
	const CRLF = "\r\n";

	protected $mail_server = null;
	protected $msg_uid = null;
    protected $from_name = null;
    protected $cc = array();
    protected $sender_ip = null;
    protected $sender_domain = null;
    
    /** Test address */
    public static $TestTo =null;
    
    /** Enable/disable sending msgs */
    public static $Disabled = false;
    
	/** The constructor */
	public function __construct($subject=null, $from=null, $from_name=null, $to=null, $to_name=null)
	{
	    parent::__construct(MsgDb::get_name().'.mail_queue');
	    
	    $this->subject = $subject;
		$this->from = $from;
		$this->from_name = $from_name;
		$this->add_to($to, $to_name);
	}
	
	/** Returns true if the record is valid */
	public function verify() 
	{
	    return $this->validate($this->from) && $this->to;
	}
	
	/** Validate email address */
	public function validate($address)
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
	    
	    if (preg_match($regex, $address) == false)
	    {
	        return false;
	    }
	    
	    return true;
	}
	
	public function get_mail_server() { return $this->mail_server; }
	public function set_mail_server($val) { $this->mail_server = $val; }
	
	public function get_msg_uid() { return $this->msg_uid; }
	public function set_msg_uid($val) { $this->msg_uid = $val; }

	public function get_from_name() { return $this->from_name; }
	public function set_from_name($val) { $this->from_name = $val; }

	public function get_cc() { return $this->cc; }
	public function add_cc($cc, $cc_name=null) { if ($this->validate($cc)) $this->cc[$cc] = $cc_name; }
	
	public function get_sender_domain() { return $this->sender_domain; }
	public function set_sender_domain($val) { $this->sender_domain = $val; }
	
	public function get_sender_ip() { return $this->sender_ip; }
	public function set_sender_ip($val) { $this->sender_ip = $val; }
	
	public function get_sender_ip_group() 
	{ 
        $ip_group = null;
	   
        if ($this->sender_ip)
        {
            $arr = explode('.', $this->sender_ip);
            $ip_group = "{$arr[0]}.{$arr[1]}.{$arr[2]}";
        }
        
        return $ip_group; 
	}
	
	/** Append original email */
	public function append_original_email(Email $email)
	{
	    $arr = $email->get_content();
	    
	    if ($arr)
	    {
	        $content = implode(Email::BR, $arr);
	        
	        $this->add_newline();
	        $this->add_newline();
	        $this->add_content('----- Original Message -----');
	        $this->add_content($content);
	        $this->add_newline();
	        $this->add_content('-----------');
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
	    
	    $this->attached[] = $arr;
	    return $cur_cid;
	}
	
	/** Normalize header address (RFC 2822) */
	protected static function normalize_header_address($address, $name)
	{
	    return $name ? "{$name} <{$address}>" : $address;
	}

	/** Normalize header addresses (RFC 2822) */
	protected static function normalize_header_addresses(array $addresses)
	{
	    $arr = array();
	    
	    foreach ($addresses as $address=>$name)
	    {
	        $arr[] = self::normalize_header_address($address, $name);
	    }
	    
	    return implode(', ', $arr);
	}

	/** Send the message */
	public function send($queue = false)
	{
	    if (self::$Disabled) return true;
	    
	    if ($this->validate($this->from) && $this->to) 
	    {
	        // Make addresses and subject
	        
	        $from = self::normalize_header_address($this->from, $this->from_name);
	        $cc = self::normalize_header_addresses($this->cc);
	        $to = self::$TestTo ? array(self::$TestTo=>'Test') : $this->to;
	        $to = self::normalize_header_addresses($to);
	        
	        $subject = $this->subject ? $this->subject : 'No subject';
	        if (self::$TestTo) $subject .= ' ['.self::normalize_header_addresses($this->to).']';
	        
	        // Compose header
	        
	        $uniqid = md5(uniqid(time()));
	        $boundary = "--==_mimepart_".$uniqid;
	        
	        $headers = array();
    		$headers[] = "MIME-Version: 1.0";
    		
    		if ($this->attached)
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
    		$headers[] = "Reply-to: {$from}";
    		$headers[] = "Return-Path: {$from}";
    		$headers[] = "Cc: {$cc}";
    		
    		$headers = implode(Email::CRLF, $headers).Email::CRLF;
    		
    		// Make content
    		
    		$content = '';
    		
    		if ($this->attached)
    		{
    		    $content = array();
    		    $content[] = "--{$boundary}";
    		    $content[] = "Content-Type: text/html; charset=UTF-8";
    		    $content[] = "Content-Transfer-Encoding: 7-bit".Email::CRLF;
    		    $content[] = implode(Email::BR, $this->content).Email::CRLF;
    		    
    		    foreach ($this->attached as $attached)
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