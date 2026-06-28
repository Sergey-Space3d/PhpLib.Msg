<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

interface iSmsEngine
{
    /** Send SMS. Returns true on success */
    public function send(SmsContent $sms);
}
?>