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
            if (@imap_mail_move($this->m_imap_stream, $msg_uid, "INBOX.{$folder}", CP_UID))
            {
                @imap_expunge($this->m_imap_stream);
                return true;
            }
            
        }
        
        return false;
    }
    
    /** Read the mail. NOTE: no need to open IMAP. Returns collection of Email instances */
    public function read($filter = 'ALL', $max_len = 0)
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
                        $struct = @imap_fetchstructure($stream, $msg_id);
                        
                        if ($struct)
                        {
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
                                $email = new Email;
                                
                                // TODO: hard-coded for now
                                $to_charset = 'UTF-8';
                                $from_charset = null;
                                
                                // Remember to mailbox sequence number
                                $uid = @imap_uid($stream, $msg_id);
                                $email->set_msg_uid($uid);
                                
                                // Set TO info
                                $to = is_array($msg->to) ? array_shift($msg->to) : $msg->to;
                                //$to_address = $to->mailbox . "@" . $to->host;
                                $to_name = $to->personal;
                                $email->set_to($this->m_user, $to_name); // use real mailbox name
                                
                                // Set FROM info
                                $from = is_array($msg->from) ? array_shift($msg->from) : $msg->from;
                                $from_address = $from->mailbox . "@" . $from->host;
                                $from_name = $from->personal;
                                $email->set_from($from_address);
                                
                                // Detect encoding
                                $from_charset = self::charset_from_string(@imap_fetchheader($stream, $msg_id));
                                if (!$from_charset) $from_charset = self::charset_from_string($from_name);
                                if (!$from_charset) $from_charset = self::charset_from_string($msg->subject);
                                
                                $from_name = self::mime_header_decode($from_name, $to_charset, $from_charset);
                                $email->set_from_name($from_name);
                                
                                // Set CC info
                                if (is_array($msg->cc))
                                {
                                    foreach ($msg->cc as $cc)
                                    {
                                        $cc_address = $cc->mailbox . "@" . $cc->host;
                                        $cc_name = $cc->personal;
                                        $cc_name = self::mime_header_decode($cc_name, $to_charset, $from_charset);
                                        
                                        $email->add_cc($cc_address, $cc_name);
                                    }
                                }
                                
                                $domain_from = explode('.', $from->host);
                                $domain_from = $domain_from[count($domain_from) - 2].'.'.$domain_from[count($domain_from) - 1];
                                $email->set_ip(gethostbyname($domain_from));
                                
                                // Decode subject line
                                $title = self::mime_header_decode($msg->subject, $to_charset, $from_charset);
                                $email->set_subject($title);
                                
                                // Decode message's body
                                switch ($struct->encoding)
                                {
                                    case 0: // 7 BIT
                                    case 1: // 8BIT
                                        $body = quoted_printable_decode($body);
                                        
                                        switch ($from_charset)
                                        {
                                            case 'UTF-8':
                                                //break;
                                            case 'KOI8-R':
                                            case 'KOI8-U':
                                            case 'windows-1251':
                                            default:
                                                if (strlen($body) > 100 && !strstr(trim($body), ' '))
                                                {
                                                    $body = base64_decode($body);
                                                    
                                                    if (!$from_charset)
                                                        $from_charset = 'KOI8-R';
                                                }
                                                break;
                                        }
                                        break;
                                    case 2: // BINARY
                                        $body = imap_binary($body);
                                        if (strlen($body) > 100 && !strstr(trim($body), ' '))
                                            $body = base64_decode($body);
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
                                $body = strip_tags($body);
                                
                                // Remove extra newlines
                                $n = 1;
                                while ($n > 0)
                                    $body = str_replace("\r\n\r\n\r\n", "\r\n\r\n", $body, $n);
                                    
                                //$body = htmlspecialchars($body, ENT_NOQUOTES);
                                if ($max_len > 0) $body = substr($body, 0, $max_len);
                                $email->set_content(array($body));
                                
                                // Set the timestamp
                                $email->set_ts($msg->udate);
                                
                                // Add to array
                                $emails[] = $email;
                            }
                        }
                    }
                }
            }
            
            @imap_close($stream);
        }
        
        return $emails;
    }
    
    /** TODO */
    static protected function mime_header_decode($str, $to_charset, $from_charset)
    {
        $elements = imap_mime_header_decode($str);
        $str = null;
        
        foreach ($elements as $element)
            $str .= $element->text;
            
            if ($to_charset && $from_charset)
                return self::convert_charset($str, $to_charset, $from_charset);
                
                return $str;
    }
    
    /** TODO */
    static protected function convert_charset($str, $to_charset, $from_charset)
    {
        $to_charset = strtolower($to_charset);
        $from_charset = strtolower($from_charset);
        $to_charset_cyr = null;
        $from_charset_cyr = null;
        
        switch ($to_charset)
        {
            case 'koi8-r': $to_charset_cyr = 'k'; break;
            case 'koi8-u': $to_charset_cyr = 'k'; break;
            case 'windows-1251': $to_charset_cyr = 'w'; break;
            case 'iso8859-5': $to_charset_cyr = 'i'; break;
            case 'x-cp866': $to_charset_cyr = 'a'; break;
            case 'x-mac-cyrillic': $to_charset_cyr = 'm'; break;
        }
        
        switch ($from_charset)
        {
            case 'koi8-r': $from_charset_cyr = 'k'; break;
            case 'koi8-u': $from_charset_cyr = 'k'; break;
            case 'windows-1251': $from_charset_cyr = 'w'; break;
            case 'iso8859-5': $from_charset_cyr = 'i'; break;
            case 'x-cp866': $from_charset_cyr = 'a'; break;
            case 'x-mac-cyrillic': $from_charset_cyr = 'm'; break;
        }
        
        if ($to_charset_cyr && $from_charset_cyr && $to_charset_cyr != $from_charset_cyr)
            return convert_cyr_string($str, $from_charset_cyr, $to_charset_cyr);
            else if ($to_charset == 'windows-1251' && $from_charset == 'utf-8')
                return self::utf8_to_win($str);
                
                return $str;
    }
    
    /** TODO */
    static protected function charset_from_string($str)
    {
        if (stristr($str, 'KOI8-R')) return 'KOI8-R';
        if (stristr($str, 'KOI8-U')) return 'KOI8-U';
        if (stristr($str, 'UTF-8')) return 'UTF-8';
        if (stristr($str, 'UTF-7')) return 'UTF-7';
        if (stristr($str, 'windows-1251')) return 'windows-1251';
        
        return null;
    }
    
    /** TODO */
    static protected function utf8_to_win($fcontents)
    {
        $out = $c1 = '';
        $byte2 = false;
        for ($c = 0;$c < strlen($fcontents);$c++) {
            $i = ord($fcontents[$c]);
            if ($i <= 127) {
                $out .= $fcontents[$c];
            }
            if ($byte2) {
                $new_c2 = ($c1 & 3) * 64 + ($i & 63);
                $new_c1 = ($c1 >> 2) & 5;
                $new_i = $new_c1 * 256 + $new_c2;
                if ($new_i == 1025) {
                    $out_i = 168;
                } else {
                    if ($new_i == 1105) {
                        $out_i = 184;
                    } else {
                        $out_i = $new_i - 848;
                    }
                }
                // UKRAINIAN fix
                switch ($out_i){
                    case 262: $out_i=179;break;// пїЅ
                    case 182: $out_i=178;break;// пїЅ
                    case 260: $out_i=186;break;// пїЅ
                    case 180: $out_i=170;break;// пїЅ
                    case 263: $out_i=191;break;// пїЅ
                    case 183: $out_i=175;break;// пїЅ
                    case 321: $out_i=180;break;// пїЅ
                    case 320: $out_i=165;break;// пїЅ
                }
                $out .= chr($out_i);
                
                $byte2 = false;
            }
            if ( ( $i >> 5) == 6) {
                $c1 = $i;
                $byte2 = true;
            }
        }
        return $out;
    }
}
?>