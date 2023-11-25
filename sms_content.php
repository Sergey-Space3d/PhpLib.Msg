<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** SMS Class */
class SmsContent extends QueueMessage
{
    /** Implementation of SMS engine */
    public static $iSms = null;
    
    /** Enable/disable sending msgs */
    public static $Disabled = false;
    
    /** The constructor */
    public function __construct($to=null)
    {
        parent::__construct(MsgDb::get_name().'.sms_queue');
        $this->set_to($to);
    }
    
    /** Returns true if the record is valid */
    public function verify()
    {
        return $this->to;
    }
    
    /** Validate address */
    public function validate($address)
    {
        // TODO
        return true;
    }
    
    /** Attach data to SMS */
    public function attach($data, $type, $name)
    {
        // NOTE: the data is URL
        $this->attached[$name] = $data;
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