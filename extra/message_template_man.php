<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Message Template Manager Class */
class MessageTemplateMan extends CDbRecordManSingleton
{
    private $callbacks = array();
    private $descriptions = array();
    private $attachments = array();
    
    /** The constructor */
    protected function __construct()
    {
        parent::__construct(MsgDb::get_name().'.message_templates', 'MessageTemplate');
    }
    
    /** Set the field to be populated via callback */
    public function set_field($name, $callback, $desc = null, $is_attachment = false)
    {
        if (!is_string($name) || !is_callable($callback)) return false;
        $this->callbacks[$name] = $callback;
        
        if (!$desc)
        {
            $arr = explode('_', $name);
            
            foreach ($arr as &$str)
            {
                $str = ucwords(strtolower($str));
            }
            
            $desc = implode(' ', $arr);
        }
        
        $this->descriptions[$name] = $desc;
        
        if ($is_attachment) $this->attachments[$name] = true;
        else unset($this->attachments[$name]);
        
        return true;
    }
    
    /** Returns collection of field names */
    public function get_field_names()
    {
        $names = array_keys($this->descriptions);
        sort($names);
        return $names;
    }
    
    /** Returns field's decorated name (as it should appear in template) */
    public function get_field_decorated_name($name)
    {
        return '{{'.$name.'}}';
    }
    
    /** Returns field's description */
    public function get_field_description($name)
    {
        return $this->descriptions[$name];
    }
    
    /** Populates message contents, substituting field placeholders by real values. Returns array of text lines */
    public function populate_contents($contents, array $field_args, $target = null)
    {
        foreach ($field_args as $name=>$args)
        {
            $dname = $this->get_field_decorated_name($name);
            
            if (strpos($contents, $dname) !== false && 
                array_key_exists($name, $this->callbacks))
            {
                if (!array_key_exists($name, $this->attachments))
                {
                    $out = $this->callbacks[$name]($target, $args);
                    $contents = str_replace($dname, $out, $contents);
                }
                else 
                {
                    $contents = str_replace($dname, '', $contents);
                }
            }
        }
        
        return explode(MessageTemplate::BR, $contents);
    }
    
    /** Process message attachments. Retruns number of processed attachments */
    public function process_attachments(MessageTemplate $mt, array $field_args, $target)
    {
        $n = 0;
        $contents = $mt->get_contents();
        
        foreach ($field_args as $name=>$args)
        {
            $dname = $this->get_field_decorated_name($name);
            
            if (strpos($contents, $dname) !== false &&
                array_key_exists($name, $this->attachments) && 
                array_key_exists($name, $this->callbacks))
            {
                $this->callbacks[$name]($target, $args);
                $n++;
            }
        }
        
        return $n;
    }
    
    /** Returns collection of template messages by category */
    public function get_by_category($category, $flags = 0, $return_instances = true)
    {
        $condition = "category='{$category}'";
        if ($flags) $condition .= " AND (flags & {$flags})!=0";
        $result = $this->m_table->get($condition);
        return $return_instances ? $this->to_instances($result) : $this->to_ids($result);
    }
}
?>