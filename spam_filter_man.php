<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Spam Manager Class */
class SpamFilterMan extends CDbRecordManSingleton
{
    protected $Scores = array( 
    SpamFilter::MULTIPLE_SENDERS=>1,
    SpamFilter::MULTIPLE_SENDER_DOMAINS=>2,
    SpamFilter::INVALID_SENDER_DOMAIN_IP=>2,
    SpamFilter::DATA_FILE_ATTACHED=>3,
    SpamFilter::CALENDAR_FILE_ATTACHED=>3,
    );
    
    /** The constructor */
    protected function __construct()
    {
        parent::__construct(MsgDb::get_name().'.spam_filters', 'SpamFilter', null, 'id DESC');
    }
    
    /** Find common spam issues */
    protected function find_common_spam_issues(Email $email, &$score)
    {
        $flags = 0;
        
        if (count($email->SenderDomains) > 1) 
        {
            $flags = SpamFilter::MULTIPLE_SENDER_DOMAINS;
            $score += $this->Scores[SpamFilter::MULTIPLE_SENDER_DOMAINS];
        }
        else
        {
        	if (count($email->SenderAddresses) > 1)
        	{
	        	$flags = SpamFilter::MULTIPLE_SENDERS;
	        	$score += $this->Scores[SpamFilter::MULTIPLE_SENDERS];
        	}
        }
        
        if (array_search('', $email->SenderDomains))
        {
            $flags |= SpamFilter::INVALID_SENDER_DOMAIN_IP;
            $score += $this->Scores[SpamFilter::INVALID_SENDER_DOMAIN_IP];
        }
        
        if ($email->Filenames)
        {
            $files = implode(' ', $email->Filenames);
            
            if (stripos($files, '.dat') !== false)
            {
                $flags |= SpamFilter::DATA_FILE_ATTACHED;
                $score += $this->Scores[SpamFilter::DATA_FILE_ATTACHED];
            }
            
            if (stripos($files, '.ics') !== false)
            {
                $flags |= SpamFilter::CALENDAR_FILE_ATTACHED;
                $score += $this->Scores[SpamFilter::CALENDAR_FILE_ATTACHED];
            }
        }
        
        return $flags;
    }
    
    /** Returns SpamFilter instance if the email is a spam, returns null otherwise */
    public function find_spam(Email $email, &$score) 
    {
        $score = 0;
        $flags = $this->find_common_spam_issues($email, $score);
        $email->set_flags($flags);
        
        if ($score >= SpamFilter::COUNT_THRESHOLD)
        {
            // The email is a spam
        	return new SpamFilter();
        }
        
        $spam_filters = $this->get('_type ASC');
        
        if ($spam_filters) foreach ($spam_filters as $sf)
        {
            $value = null;
            $type = $sf->get_type();
            
            switch ($type)
            {
                case SpamFilter::TYPE_IP:
                    $value = $email->SenderDomains;
                    break;
                    
                case SpamFilter::TYPE_IP_GROUP:
                    $value = $email->SenderIpGroups;
                    break;
                    
                case SpamFilter::TYPE_DOMAIN:
                    $value = array_unique(array_keys($email->SenderDomains));
                    break;
                    
                case SpamFilter::TYPE_FROM_ADDRESS:
                    $value = $email->SenderAddresses;
                    break;
                    
                case SpamFilter::TYPE_TEXT:
                	$arr = array_merge($email->SenderNames, array($email->get_subject()), $email->get_content());
                	$value = implode(' ', $arr);
                    $value = str_replace(array("\n", "\t", "<br/>"), ' ', $value);
                    break;
                    
                default: // Unknown type
                    continue 2;
            }
            
            $values = is_array($value) ? $value : array($value);
            foreach ($values as $value)
            {
                $match = ($type == SpamFilter::TYPE_TEXT) ?
                @preg_match($sf->get_value(), $value) :
                strtoupper($value) == $sf->get_value();
                
                if ($match)
                {
                    // Update the score
                	$score += $sf->get_score();
                    
                    if ($score >= SpamFilter::COUNT_THRESHOLD)
                    {
                        // The email is a spam
                        return $sf;
                    }
                }
            }
        }

        return null;
    }
    
    /** Mark the value as a spam of curtain type.Returns true if spam */
    public function mark_spam($value, $type, $score) 
    {
        $value = trim($value);
        if (!strlen($value)) return false;
        if ($type != SpamFilter::TYPE_TEXT) $value = strtoupper($value);
        
        $spam_filter = $this->get_instance("_value='{$value}' AND _type={$type}");
        
        if (!$spam_filter)
        {
            // Create new spam
        	$spam_filter = new SpamFilter();
            $spam_filter->set_value($value);
            $spam_filter->set_type($type);
            $spam_filter->write();
        }
        
        $spam_filter->set_score($score, true);
        return $count >= SpamFilter::COUNT_THRESHOLD;
    }
}
?>