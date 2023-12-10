<?php

/** Edit Message Template Form */
class EditMessageTemplateForm extends FormLayout
{
    /** Initialize form contents */
    protected function init_contents(array $args)
    {
        @extract($args);

        $this->add_inner(new CHtmlHidden('id', $id));
        
        $this->set_title('<b>'.$category.'</b>');
        
        $ctrl = new CHtmlCheckbox('send_mail', true, $send_mail);
        $ctrl->add_inner('Send Mail');
        $this->add_control('', $ctrl);
        
        $ctrl = new CHtmlCheckbox('send_sms', true, $send_sms);
        $ctrl->add_inner('Send SMS');
        $this->add_control('', $ctrl);
        
        $ctrl = new CHtmlCheckbox('queue', true, $queue);
        $ctrl->add_inner('Deliver in Queue');
        $this->add_control('', $ctrl);
        
        $this->add_hr();
        
        $ctrl = new CHtmlText('title', $title, array('size'=>32, 'maxlength'=>64));
        $this->add_control('Title', $ctrl, true, true);
        
        $ctrl = new CHtmlTextArea('contents', $contents, 10, 60, array('maxlength'=>$maxlength));
        $this->add_control('Contents', $ctrl, true, true);
        
        $ctrl = new CHtmlSubmit('Submit');
        $ctrl->disable_on_click();
        $this->add_control_deck($ctrl);
    }
}
?>