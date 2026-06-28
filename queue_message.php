<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

abstract class QueueMessage extends CDbQueueRecord
{
    protected $content = array();
    protected $attachments = array();
    private $err = null;
    
    public function get_content($glue = null) { return $glue == null ? $this->content : implode($glue, $this->content); }
    public function set_content(array $content) { $this->content = $content; }
    public function add_line($line) { if ($line) $this->content[] = $line; }
    public function add_newline() { $this->content[] = ''; }
    
    public function get_attachments() { return $this->attachments; }
    public function set_attachments(array $attachments) { $this->attachments = $attachments; }
    public function add_attachment($attachment) { $this->attachments[] = $attachment; }
    
    // Error ----------------------------------------------------------------
    
    /** Get last error */
    public final function get_last_error() { return $this->err; }
    
    /** Set last error */
    protected final function set_last_error($error) { $this->err = $error; }
    
    // Abstracts ------------------------------------------------------------
    
    /** Get subject line */
    abstract public function get_subject();
    
    /** Set subject line */
    abstract public function set_subject($val);
    
    /** Get FROM address (name is optional) */
    abstract public function get_from(&$name = null);
    
    /** Set FROM address (name is optional). Returns true on success */
    abstract public function set_from($val, $name = null);
    
    /** Get TO collection, as [address]=>[name] */
    abstract public function get_to();
    
    /** Set TO address (name is optional). Returns true on success */
    abstract public function set_to($val, $name=null);
    
    /** Add TO address (name is optional). Returns true on success */
    abstract public function add_to($val, $name=null);
    
    /** Validate address */
    abstract public function validate($address);
    
    /** Attach data to the message */
    abstract public function attach($data, $type, $name);
    
    /** Send the message */
    abstract public function send($queue = false);
}
?>