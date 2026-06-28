<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** SMS Class */
class SmsContent extends QueueMessage
{
    /** Implementation of SMS engine */
    public static $iSms = null;
    
    /** Enable/disable sending msgs */
    public static $Disabled = false;
    
    protected $subject = null;
    protected $to = array();
    protected $from = null;
    protected $from_name = null;
    
    /** The constructor */
    public function __construct($to=null)
    {
        parent::__construct(MsgDb::get_name().'.sms_queue');
        $this->set_to($to);
    }
    
    /** Get subject line */
    public function get_subject() { return $this->subject; }
    
    /** Set subject line */
    public function set_subject($val) { $this->subject = $val; }
    
    /** Get FROM address (name is optional) */
    public function get_from(&$name = null)
    {
        if ($name !== null) $name = $this->from_name;
        return $this->from;
    }
    
    /** Set FROM address (name is optional). Returns true on success */
    public function set_from($val, $name = null)
    {
        if ($this->validate($val))
        {
            $this->from = $val;
            $this->from_name = $name;
            return true;
        }
        
        return false;
    }
    
    /** Get TO collection, as [address]=>[name] */
    public function get_to() 
    {
        return $this->to; 
    }
    
    /** Set TO address (name is optional). Returns true on success */
    public function set_to($val, $name = null) 
    {
        $this->to = array(); 
        return $this->add_to($val, $name); 
    }
    
    /** Add TO address (name is optional). Returns true on success */
    public function add_to($val, $name = null) 
    {
        if (!is_array($val)) $val = array($val=>$name);
        
        foreach ($val as $addr=>$_name)
        {
            if ($this->validate($addr)) 
            {
                $this->to[$addr] = $_name; 
            }
            else 
            {
                return false;
            }
        }
        
        return true;
    }
    
    /** Returns true if the record is valid */
    public function verify()
    {
        return $this->validate($this->get_to());
    }
    
    /** Validate address */
    public function validate($address)
    {
        // TODO
        return strlen($address) > 0;
    }
    
    /** Attach data to SMS */
    public function attach($data, $type, $name)
    {
        // NOTE: the data is URL
        $this->attachments[$name] = $data;
    }
    
    /** Send the message */
    public function send($queue = false)
    {
        if (!self::$Disabled && (self::$iSms instanceof iSmsEngine))
        {
            try
            {
                return $queue ? $this->write() : self::$iSms->send($this);
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