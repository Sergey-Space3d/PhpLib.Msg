<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

abstract class QueueMessage extends CDbQueueRecord
{
    protected $from = null;
    protected $to = array();
    protected $subject = null;
    protected $content = array();
    protected $attached = array();
    private $err = null;
    
    public function get_from() { return $this->from; }
    public function set_from($val) { if ($this->validate($val)) $this->from = $val; }
    
    public function get_to() { return $this->to; }
    public function set_to($to, $to_name=null) { $this->to = array(); $this->add_to($to, $to_name); }
    public function add_to($to, $to_name=null) { if ($this->validate($to)) $this->to[$to] = $to_name; }
    
    public function get_subject() { return $this->subject; }
    public function set_subject($val) { $this->subject = $val; }
    
    public function get_content() { return $this->content; }
    public function set_content(array $content) { $this->content = $content; }
    public function add_content($line) { if ($line) $this->content[] = $line; }
    public function add_newline() { $this->content[] = ''; }
    
    public function get_attached() { return $this->attached; }
    
    // Error ----------------------------------------------------------------
    
    /** Get last error */
    public final function get_last_error() { return $this->err; }
    
    /** Set last error */
    protected final function set_last_error($error) { $this->err = $error; }
    
    // Abstracts ------------------------------------------------------------
    
    /** Validate address */
    abstract public function validate($address);
    
    /** Attach data to the message */
    abstract public function attach($data, $type, $name);
    
    /** Send the message */
    abstract public function send($queue = false);
}
?>