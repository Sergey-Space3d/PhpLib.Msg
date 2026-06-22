<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

class EmailServer
{
    protected $m_port = 993; // 110 for POP3
    protected $m_service = '/imap/ssl'; // '/pop3/notls' for POP3
    protected $m_server = null;
    protected $m_user = null;
    protected $m_password = null;
    protected $m_imap_stream = null;
    
    /** The constructor */
    public function __construct($server, $user, $password)
    {
        $this->m_server = $server;
        $this->m_user = $user;
        $this->m_password = $password;
    }
    
    /** Pop the last errors from the queue */
    static public function pop_errors()
    {
        return @imap_errors();
    }
    
    /** Open IMAP stream. Returns true on success */
    public function open()
    {
        if ($this->m_imap_stream) $this->close();
        
        $authhost = "{".$this->m_server.":".$this->m_port.$this->m_service."}INBOX";
        $this->m_imap_stream = @imap_open($authhost, $this->m_user, $this->m_password);
        
        return $this->m_imap_stream != null;
    }
    
    /** Close IMAP stream. Returns true on success */
    public function close()
    {
        if ($this->m_imap_stream)
        {
            @imap_close($this->m_imap_stream, CL_EXPUNGE);
            $this->m_imap_stream = null;
            return true;
        }
        
        return false;
    }
    
    /** Delete email by UID. Returns true on success */
    public function delete($msg_uid)
    {
        if ($this->m_imap_stream)
        {
            return @imap_delete($this->m_imap_stream, $msg_uid, FT_UID);
        }
        
        return false;
    }
    
    /** Move email to the folder. Returns true on success */
    public function move($msg_uid, $folder)
    {
        if ($this->m_imap_stream)
        {
            return @imap_mail_move($this->m_imap_stream, $msg_uid, "INBOX.{$folder}", CP_UID);
        }
        
        return false;
    }
    
    /** Read the mail. NOTE: no need to open IMAP. Returns collection of Email instances */
    public function read($filter = 'ALL', $max_len = 0, $header_only = false)
    {
        $emails = array(); // return
        
        // Open read-only stream
        $authhost = "{".$this->m_server.":".$this->m_port.$this->m_service."}INBOX";
        $stream = @imap_open($authhost, $this->m_user, $this->m_password, OP_READONLY);
        
        if ($stream)
        {
            $msg_ids = @imap_search($stream, $filter);
            
            if ($msg_ids)
            {
                foreach ($msg_ids as $msg_id)
                {
                    $msg = @imap_headerinfo($stream , $msg_id);
                    
                    if ($msg)
                    {
                        // Add new email to array
                        $email = new Email;
                        $emails[] = $email;
                        
                        // Setup headers
                        $email->Header->read($msg);
                        
                        // Remember the mail server
                        $email->set_mail_server($this->m_server);
                        
                        // Remember to mailbox sequence number
                        $uid = @imap_uid($stream, $msg_id);
                        $email->set_msg_uid($uid);
                        
                        // Set the timestamp
                        $email->set_ts($msg->udate);
                        
                        // Process the sender's IPs and domains
                        self::process_sender_domains($email, $msg);
                        
                        // Skip the body
                        if ($header_only) continue;
                        
                        // Read the message structure
                        $struct = @imap_fetchstructure($stream, $msg_id);
                        
                        if ($struct)
                        {
                            // Populate attachment list
                            self::setup_attachments($email, $struct);
                            
                            $body = null;
                            
                            switch ($struct->type)
                            {
                                case 0: // text
                                    $body = @imap_body($stream, $msg_id);
                                    break;
                                case 1: // multipart
                                    $body = @imap_fetchbody($stream, $msg_id, '1');
                                    break;
                                    
                                case 2: // message
                                case 3: // application
                                case 4: // audio
                                case 5: // image
                                case 6: // video
                                case 7: // other
                                default:
                                    break;
                            }
                            
                            if ($body)
                            {
                                // Decode message's body
                                switch ($struct->encoding)
                                {
                                    case 0: // 7 BIT
                                    case 1: // 8BIT
                                        $body = quoted_printable_decode($body);
                                        if (strlen($body) > 100 && !strstr(trim($body), ' '))
                                        {
                                            $body = base64_decode($body);
                                        }
                                        break;
                                    case 2: // BINARY
                                        $body = imap_binary($body);
                                        if (strlen($body) > 100 && !strstr(trim($body), ' '))
                                        {
                                            $body = base64_decode($body);
                                        }
                                        $body = quoted_printable_decode($body);
                                        break;
                                    case 3: // BASE64
                                        $body = base64_decode($body);
                                        $body = quoted_printable_decode($body);
                                        break;
                                    case 4: // QUOTED-PRINTABLE
                                    case 5: // OTHER
                                    default:
                                        $body = quoted_printable_decode($body);
                                        break;
                                }
                                
                                // Strip body tags, optionally limit to max. length chars
                                $body = self::extract_body($body);
                                
                                //$body = htmlspecialchars($body, ENT_NOQUOTES);
                                if ($max_len > 0 && strlen($body) > $max_len) $body = substr($body, 0, $max_len).'...';
                                $email->set_content(array($body));
                            }
                        }
                    }
                }
            }
            
            @imap_close($stream);
        }
        
        return $emails;
    }
    
    /** Process sender domains and IPs */
    static protected function process_sender_domains(Email $email, $msg)
    {
        $setup = function(Email $email, $sender)
        {
            // Determine sender's domain and ip
            $domain_by_host = function($host)
            {
                if (!$host) return null;
                $arr = explode('.', $host);
                
                $n = count($arr);
                if ($n < 2) return null;
                
                return $arr[$n - 2].'.'.$arr[$n - 1];
            };
            
            $ip_by_domain = function($domain)
            {
                $ip = gethostbyname($domain);
                return $ip == $domain ? null : $ip;
            };
            
            if (!$sender || !$sender->host) return false;
            
            $domain = $domain_by_host($sender->host);
            if (!$domain) return false;
            
            $email->set_sender_domain($domain);
            
            $ip = $ip_by_domain($domain);
            if (!$ip) return false;
            
            $email->set_sender_ip($ip);
            return true;
        };
        
        return 
        $setup($email, is_array($msg->sender) ? array_shift($msg->sender) : $msg->sender) ||
        $setup($email, is_array($msg->reply_to) ? array_shift($msg->reply_to) : $msg->reply_to) ||
        $setup($email, is_array($msg->from) ? array_shift($msg->from) : $msg->from);
    }
    
    /** Extract essential text from the message's body */
    static protected function extract_body($text)
    {
        $reduce_to_2 = function($find, $replace, $str)
        {
            $n = 1;
            while ($n > 0) $str = str_replace($find, $replace, $str, $n);
            return $str;
        };
        
        $remove_spaces_between_breaks = function($break, $str)
        {
            $arr = array();
            foreach (explode($break, $str) as $el)
            {
                $el2 = trim($el);
                if (strlen($el2) || !strlen($el)) $arr[] = $el2;
            }
            return implode($break, $arr);
        };
        
        $text = trim($text);
        
        $matches = array(); // try extracting <body> element
        if (preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $text, $matches)) 
        {
            $text = $matches[1];
        }
        
        $text = strip_tags($text);
        $text = str_replace(array('<br>', '<BR>', '<BR/>'), '<br/>', $text);
        $text = str_replace(array('&nbsp;', '&zwnj;'), '', $text);
        $is_html = strpos($text, '<br/>') !== false;
        
        if ($is_html)
        {
            // Remove all newlines
            $text = str_replace(array("\r", "\n"), '', $text);
            
            // Remove redundant spaces
            $text = $remove_spaces_between_breaks('<br/>', $text);
            
            // Allow 2 breaks max
            $text = $reduce_to_2('<br/><br/><br/>', '<br/><br/>', $text);
        }
        else 
        {
            // Remove CR characters
            $text = str_replace("\r\n", "\n", $text);
            
            // Remove redundant spaces
            $text = $remove_spaces_between_breaks("\n", $text);
            
            // Allow 2 lewlines max
            $text = $reduce_to_2("\n\n\n", "\n\n", $text);
        }
        
        return $text;
    }
    
    static protected function setup_attachments(Email $email, $struct)
    {
        if (isset($struct->parameters) && is_array($struct->parameters)) 
        {
            foreach ($struct->parameters as $param) 
            {
                if (strtoupper($param->attribute) == 'FILENAME') 
                {
                    $email->add_attachment($param->value);
                }
            }
        }
        
        if (isset($struct->dparameters) && is_array($struct->dparameters)) 
        {
            foreach ($struct->dparameters as $param) 
            {
                if (strtoupper($param->attribute) == 'FILENAME')
                {
                    $email->add_attachment($param->value);
                }
            }
        }
        
        // Go thru multipart sections
        if (isset($struct->parts) && is_array($struct->parts)) 
        {
            foreach ($struct->parts as $part) 
            {
                self::setup_attachments($email, $part);
            }
        }
    }
}
?>