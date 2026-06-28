<?php
define('SELECTOR_NOTIFICATION_ID', 'sel_id_notification');

class MsgDb
{
    private static $name = null;
    public static function get_name(){ return self::$name; }
    public static function set_name($name){ self::$name = $name; }
}

$__dir = dirname(__FILE__);

// Setup JIT class autoload
$__sudir = $__dir.'/extra';
$__classes = array(
    'EmailServer'=>$__dir.'/email_server.php',
    'EmailMan'=>$__dir.'/email_man.php',
    'EmailHeader'=>$__dir.'/email_header.php',
    'Email'=>$__dir.'/email.php',
    'SpamFilterMan'=>$__dir.'/spam_filter_man.php',
    'SpamFilter'=>$__dir.'/spam_filter.php',
    'iSmsEngine'=>$__dir.'/isms_engine.php',
    'QueueMessage'=>$__dir.'/queue_message.php',
    'SmsContentMan'=>$__dir.'/sms_content_man.php',
    'SmsContent'=>$__dir.'/sms_content.php',
	'MailboxMan'=>$__dir.'/mailbox_man.php',
	'Mailbox'=>$__dir.'/mailbox.php',
	'MessageTemplateMan'=>$__sudir.'/message_template_man.php',
    'MessageTemplate'=>$__sudir.'/message_template.php',
    'TemplateMessenger'=>$__sudir.'/template_messenger.php',
);
require_once($__dir.'/../db/extra/c_autoloader.php');
CAutoloader::register($__classes);
?>