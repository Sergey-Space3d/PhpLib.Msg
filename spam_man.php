<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Spam Manager Class */
class SpamMan extends CDbRecordManSingleton
{
    /** The constructor */
    protected function __construct()
    {
        parent::__construct(MsgDb::get_name().'.spam', 'Spam');
    }
    
    /** Returns Spam instance if the email is a spam, returns null otherwise */
    public function find_spam(Email $email, &$mark_count) 
    {
        $mark_count = 0;
        $spams = $this->get();
        
        if ($spams) foreach ($spams as $spam)
        {
            $value = null;
            
            switch ($spam->get_type())
            {
                case Spam::TYPE_IP:
                    $value = $email->get_ip();
                    break;
                    
                case Spam::TYPE_IP_GROUP:
                    $value = $email->get_ip_group();
                    break;
                    
                case Spam::TYPE_FROM_ADDRESS: // TODO
                case Spam::TYPE_DOMAIN: // TODO
                default:
                    break;
            }
            
            if ($value == $spam->get_value())
            {
                // Update the mark count
                $mark_count = max($mark_count, $spam->get_mark_count());
                
                if ($spam->get_mark_count() >= Spam::COUNT_THRESHOLD)
                {
                    // The email is a spam
                    return $spam;
                }
            }
        }

        return null;
    }
    
    /** Mark the value as a spam of curtain type */
    public function mark_spam($value, $type) 
    {
        $value = strtoupper($value);
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
        $spam->set_mark_count($spam->get_mark_count() + 1, true);
    }
}
?>