<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Spam Manager Class */
class SpamMan extends CDbRecordManSingleton
{
    protected $Scores = array( 
    Spam::MULTIPLE_SENDER_DOMAINS =>2,
    Spam::INVALID_SENDER_DOMAIN_IP=>2,
    Spam::DATA_FILE_ATTACHED=>3,
    Spam::CALENDAR_FILE_ATTACHED=>3,
    );
    
    /** The constructor */
    protected function __construct()
    {
        parent::__construct(MsgDb::get_name().'.spam', 'Spam');
    }
    
    /** Find common spam issues */
    protected function find_common_spam_issues(Email $email, &$mark_count)
    {
        $flags = 0;
        
        if (count($email->SenderDomains) > 1) 
        {
            $flags = Spam::MULTIPLE_SENDER_DOMAINS;
            $mark_count += $this->Scores[Spam::MULTIPLE_SENDER_DOMAINS];
        }
        
        if (array_search('', $email->SenderDomains))
        {
            $flags |= Spam::INVALID_SENDER_DOMAIN_IP;
            $mark_count += $this->Scores[Spam::INVALID_SENDER_DOMAIN_IP];
        }
        
        if ($email->Filenames)
        {
            $files = implode(' ', $email->Filenames);
            
            if (stripos($files, '.dat') !== false)
            {
                $flags |= Spam::DATA_FILE_ATTACHED;
                $mark_count += $this->Scores[Spam::DATA_FILE_ATTACHED];
            }
            
            if (stripos($files, '.ics') !== false)
            {
                $flags |= Spam::CALENDAR_FILE_ATTACHED;
                $mark_count += $this->Scores[Spam::CALENDAR_FILE_ATTACHED];
            }
        }
        
        return $flags;
    }
    
    /** Returns Spam instance if the email is a spam, returns null otherwise */
    public function find_spam(Email $email, &$mark_count) 
    {
        $mark_count = 0;
        $flags = $this->find_common_spam_issues($email, $mark_count);
        $email->set_flags($flags);
        
        if ($mark_count >= Spam::COUNT_THRESHOLD)
        {
            // The email is a spam
            return new Spam();
        }
        
        $spams = $this->get('_type ASC');
        
        if ($spams) foreach ($spams as $spam)
        {
            $value = null;
            $type = $spam->get_type();
            
            switch ($type)
            {
                case Spam::TYPE_IP:
                    $value = $email->SenderDomains;
                    break;
                    
                case Spam::TYPE_IP_GROUP:
                    $value = $email->SenderIpGroups;
                    break;
                    
                case Spam::TYPE_DOMAIN:
                    $value = array_unique(array_keys($email->SenderDomains));
                    break;
                    
                case Spam::TYPE_FROM_ADDRESS:
                    $value = $email->get_from();
                    break;
                    
                case Spam::TYPE_TEXT:
                    $value = implode(' ', $email->get_content())." {$email->get_subject()}";
                    $value = str_replace(array("\n", "\t", "<br/>"), ' ', $value);
                    break;
                    
                default: // Unknown type
                    continue 2;
            }
            
            $values = is_array($value) ? $value : array($value);
            foreach ($values as $value)
            {
                $match = ($type == Spam::TYPE_TEXT) ?
                preg_match($spam->get_value(), $value) :
                strtoupper($value) == $spam->get_value();
                
                if ($match)
                {
                    // Update the mark count
                    $mark_count += $spam->get_mark_count();
                    
                    if ($mark_count >= Spam::COUNT_THRESHOLD)
                    {
                        // The email is a spam
                        return $spam;
                    }
                }
            }
        }

        return null;
    }
    
    /** Mark the value as a spam of curtain type.Returns true if spam */
    public function mark_spam($value, $type, $is_spam = false) 
    {
        $value = trim($value);
        if (!strlen($value)) return false;
        if ($type != Spam::TYPE_TEXT) $value = strtoupper($value);
        
        $spam = $this->get_instance("_value='{$value}' AND _type={$type}");
        
        if (!$spam)
        {
            // Create new spam
            $spam = new Spam();
            $spam->set_value($value);
            $spam->set_type($type);
            $spam->write();
        }
        
        // Increase the mark count
        $count = $is_spam ? Spam::COUNT_THRESHOLD : $spam->get_mark_count() + 1;
        $spam->set_mark_count($count, true);
        
        return $count >= Spam::COUNT_THRESHOLD;
    }
}
?>