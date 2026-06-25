<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

class Spam extends CDbRecord
{
    private static $m_sfd = array(
    '_value'=>'varchar(255)',
    '_type'=>'int',
    'mark_count'=>'int',
    'spam_count'=>'int',
    );
    
    const TYPE_IP_GROUP = 2;
    const TYPE_IP = 1;
    const TYPE_DOMAIN = 3;
    const TYPE_FROM_ADDRESS = 4;
    const TYPE_TEXT = 5;
    
    const COUNT_THRESHOLD = 5;
    const FOLDER = 'Junk';
    
    const MULTIPLE_SENDER_DOMAINS  = 0x01;
    const INVALID_SENDER_DOMAIN_IP = 0x02;
    const DATA_FILE_ATTACHED       = 0x04;
    const CALENDAR_FILE_ATTACHED   = 0x08;
    
    /** The constructor */
    public function __construct($id = 0)
    {
        parent::__construct(MsgDb::get_name().'.spam', $id);
    }
    
    /** Returns field definitions for the table */
    public function get_field_def()
    {
        return self::$m_sfd;
    }
    
    /** Called when writing new record to the database */
    protected function on_write_new()
    {
        $this->m_fields['_type'] = (int)$this->m_fields['_type'];
        $this->m_fields['mark_count'] = (int)$this->m_fields['mark_count'];
        $this->m_fields['spam_count'] = (int)$this->m_fields['spam_count'];
    }
    
    /** Returns true if the record is valid */
    public function verify()
    {
        return (strlen($this->m_fields['_value']) > 1 && $this->m_fields['_type'] != 0);
    }
    
    public function get_value() { return $this->m_fields['_value']; }
    public function get_type() { return $this->m_fields['_type']; }
    public function get_mark_count() { return $this->m_fields['mark_count']; }
    public function get_spam_count() { return $this->m_fields['spam_count']; }
   
    public function set_value($arg, $save = false) { $this->set_field('_value', $arg, $save); }
    public function set_type($arg, $save = false) { $this->set_field('_type', (int)$arg, $save); }
    public function set_mark_count($arg, $save = false) { $this->set_field('mark_count', (int)$arg, $save); }
    public function set_spam_count($arg, $save = false) { $this->set_field('spam_count', (int)$arg, $save); }
}
?>