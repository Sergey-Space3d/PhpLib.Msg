<?php
// Copyright 2005-2026 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Email Manager Class */
class EmailMan extends CDbQueueRecordMan
{
    /** The constructor */
    protected function __construct()
    {
        parent::__construct(MsgDb::get_name().'.mail_queue', 'Email');
    }

    /** Process the item. Returns true on success */
    protected function process(CDbQueueRecord $item)
    {
        return $item->send();
    }
}
?>