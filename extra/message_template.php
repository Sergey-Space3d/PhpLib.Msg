<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

class MessageTemplate extends CDbRecord
{
    private static $m_mtfd = array(
    'category'=>'varchar(64)', 
    'title'=>'varchar(64)', 
    'contents'=>'TEXT');
    
    const BR = "\n";
    const SEND_MAIL = 0x1;
    const SEND_SMS = 0x2;
    const DELIVER_IN_QUEUE = 0x4;
    
    /** The constructor */
    public function __construct($id = 0)
    {
        parent::__construct(MsgDb::get_name().'.message_templates', $id);
    }
    
    /** Returns field definitions for the table */
    public function get_field_def() { return self::$m_mtfd; }
    
    /** Called when writing new record to the database */
    protected function on_write_new() {}
    
    /** Returns true if the record is valid */
    public function verify()
    {
        return $this->m_fields['category'] && $this->m_fields['title'] && $this->m_fields['contents'];
    }

    public function get_category() { return $this->m_fields['category']; }
    public function get_title() { return $this->m_fields['title']; }
    public function get_contents() { return $this->m_fields['contents']; }
    
    public function set_category($arg, $save = false) { $this->set_field('category', $arg, $save); }
    public function set_title($arg, $save = false) { $this->set_field('title', $arg, $save); }
    
    public function set_contents($arg, $save = false) 
    {
        $arg = str_replace(array('<br>', '<br/>', "\r\n"), MessageTemplate::BR, $arg);
        $this->set_field('contents', $arg, $save); 
    }
}
?>