<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

class EmailHeader
{
    protected $Header = array();
    
    /** Read email's header (convert objects to arrays where keys are uppercase member names) */
    public function read($header)
    {
        $this->Header = self::parse_obj($header);
    }
    
    /** Get the element identified by one or more keys */
    public function get($keys)
    {
        if (is_array($keys))
        {
            $ret = $this->Header;
            foreach ($keys as $key) 
            {
                if (!is_array($ret)) break;
                if (!array_key_exists($key, $ret)) break;
                $ret = $ret[$key];
            }
            return $ret;
        }

        return $this->Header[$keys];
    }
    
    /** Set value for the element identified by one or more keys */
    public function set($keys, $val)
    {
        $keys = is_array($keys) ? $keys : array($keys);
        $el = &$this->Header;
        
        foreach ($keys as $key)
        {
            if (!isset($el[$key])) $el[$key] = '';
            $el = &$el[$key];
        }
        
        $el = $val;
    }
    
    /** Convert object to array */
    protected static function parse_obj($obj)
    {
        if (is_array($obj) || is_object($obj))
        {
            $arr = array();
            
            foreach ($obj as $key=>$val)
            {
                $key = strtoupper(trim($key));
                $arr[$key] = self::parse_obj($val);
            }
            
            $obj = $arr;
        }
        else
        {
            $obj = self::decode_value($obj);
        }
        
        return $obj;
    }
    
    /** Decode original header's value */
    protected static function decode_value($obj)
    {
        $out = '';
        foreach (@imap_mime_header_decode(trim($obj)) as $el) $out .= $el->text;
        return trim(htmlspecialchars($out));
    }
    
    /** Convert header to readable string (called recursively) */
    protected static function to_str($obj, &$str, $iter = null)
    {
        if (is_array($obj) || is_object($obj))
        {
            $iter = ($iter === null) ? 0 : $iter + 4;
            
            foreach ($obj as $key=>$val)
            {
                if (is_array($val) && !is_numeric($key))
                {
                    if ($iter > 0) $str .= str_repeat('&nbsp;', $iter);
                    $str .= "{$key}:<br/>";
                }
                
                self::to_str($val, $str, $iter);
                
                if (!is_array($val)) 
                {
                    if ($iter > 0) $str .= str_repeat('&nbsp;', $iter);
                    //$val = htmlspecialchars($val);
                    $str .= "{$key}: {$val}<br/>";
                }
            }
        }
    }
    
    /** Convert header to string */
    public function __toString() 
    {
        $out = '';
        self::to_str($this->Header, $out);
        return $out; 
    }
}
?>