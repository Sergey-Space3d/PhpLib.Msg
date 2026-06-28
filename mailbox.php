<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

class Mailbox extends CDbRecord
{
    private static $m_mfd = array(
        'mail_server'=>'varchar(128)',
        'username'=>'varchar(128)',
        'password'=>'varchar(128)',
        'port'=>'int',
        'service'=>'varchar(128)',
    );
    
    /** The constructor */
    public function __construct($id = 0)
    {
        parent::__construct(MsgDb::get_name().'.mailboxes', $id);
    }
    
    /** Returns field definitions for the table */
    public function get_field_def()
    {
        return self::$m_mfd;
    }
    
    /** Called when writing new record to the database */
    protected function on_write_new()
    {
        $this->m_fields['port'] = (int)$this->m_fields['port'];
    }
    
    /** Returns true if the record is valid */
    public function verify()
    {
        return 
        strlen($this->m_fields['mail_server']) > 0 &&
        strlen($this->m_fields['username']) > 0 &&
        strlen($this->m_fields['password']) > 0 &&
        $this->m_fields['port'] > 0 &&
        strlen($this->m_fields['service']) > 0;
    }
    
    public function get_mail_server() { return $this->m_fields['mail_server']; }
    public function get_username() { return $this->m_fields['username']; }
    public function get_password() { return $this->m_fields['password']; }
    public function get_port() { return $this->m_fields['port']; }
    public function get_service() { return $this->m_fields['service']; }
    
    public function set_mail_server($arg, $save = false) { $this->set_field('mail_server', $arg, $save); }
    public function set_username($arg, $save = false) { $this->set_field('username', $arg, $save); }
    public function set_password($arg, $save = false) { $this->set_field('password', $arg, $save); }
    public function set_port($arg, $save = false) { $this->set_field('port', (int)$arg, $save); }
    public function set_service($arg, $save = false) { $this->set_field('service', $arg, $save); }
}
?>