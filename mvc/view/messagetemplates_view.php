<?php
// Copyright 2005-2022 Sergey Shustov.
// License: GPL v2.  See License.txt.

/** Messages Templates View */
class MessageTemplatesView extends CView
{
    protected static $classes = array();
    protected $permissions;
    
    /** Initialize view contents */
    protected function init_contents(array $args)
    {
        @extract($args);
        
        $this->permissions = $permissions;

        if (!CHtmlStyle::has_selector('.mt_tbar'))
        {
            $style_el = new CHtmlStyle();
            $this->add_inner($style_el);
            
            $style_el->add_selector('.mt_tbar', 'padding:4px;');
            $style_el->add_selector('.mt_tb_selector', 'display:inline;margin:0px 4px;');
            $style_el->add_selector('.mt_tb_button', 'display:inline;margin:0px 10px;float:right;');
            $style_el->add_selector('table.mt_preview', 'width:100%;background-color:#e2e8e2;');
            $style_el->add_selector('td.mt_preview', 'border-bottom:1px solid grey;');
            $style_el->add_selector('.mt_preview_title', 'color:grey;text-align:center;font-weight:bold;border-bottom:1px solid grey;');
            $style_el->add_selector('.mt_legend', 'background-color:#F2F2F2;padding:4px;');
            $style_el->add_selector('.mt_legend_button', 'width:160px;padding:4px 15px;border:1px solid black;cursor:pointer;');
            $style_el->add_selector('.mt_legend_footer', 'padding:10px 40px;margin:0px;text-align:justify;font-style:italic;');
        
            self::$classes['toolbar'] = $this->get_css_class('toolbar', 'mt_tbar');
        }
        
        $table = new CHtmlTable(array('width'=>'100%'));
        $this->add_inner($table);

        // Make the toolbar
        $td_attrs = array('style'=>'padding-bottom:16px;');
        $toolbar = $this->make_toolbar();
        $table->add_row($toolbar, $td_attrs);
        
        $id = CHtmlForm::get_value(SELECTOR_NOTIFICATION_ID);
        
        if ($id)
        {
            $table->add_row($this->make_preview($id), $td_attrs);
            
            if ($this->permissions['edit'])
            {
                $table->add_row($this->make_legend(), $td_attrs);
                $form = CDispatcher::instance()->get_form("EditMessageTemplate", array('id'=>$id));
                $table->add_row($form, $td_attrs);
            }
        }
    }
    
    /** Make the toolbar */
    protected function make_toolbar()
    {
        // Create the toolbar (on top)
        $table = new CHtmlTable(array('class'=>self::$classes['toolbar']));
        $items = array();

        $args = array('title'=>'Notifications');
        $sel = CDispatcher::instance()->get_form("MessageTemplateSelector", $args, array('class'=>'mt_tb_selector'));
        $items[] = $sel;
        
        $args = array('id'=>CHtmlForm::get_value(SELECTOR_NOTIFICATION_ID));
        $form = CDispatcher::instance()->get_form("TestMessageTemplate", $args, array('class'=>'mt_tb_button'));
        $items[] = $form;
        
        $table->add_row($items);
        return $table;
    }
    
    /** Make the preview */
    protected function make_preview($id)
    {
        $table = new CHtmlTable(array('cellpadding'=>6, 'class'=>'mt_preview'));
        $table->add_row('MESSAGE PREVIEW', array('colspan'=>'100%', 'class'=>'mt_preview_title'));
        
        $message_helper = CHtmlForm::get_common_field('message_helper');
        $field_args = TemplateMessenger::Instance($message_helper)->make_sample_template_args();
        
        $mt = new MessageTemplate($id);
        $contents = MessageTemplateMan::Instance()->populate_contents($mt->get_title(), $field_args);
        $table->add_row(implode('<br/>', $contents), array('class'=>'mt_preview'));
        
        $contents = MessageTemplateMan::Instance()->populate_contents($mt->get_contents(), $field_args);
        $div = new CHtmlElement('div', null, implode('<br/>', $contents));
        MessageTemplateMan::Instance()->process_attachments($mt, $field_args, $div);
        $table->add_row($div);
        
        return $table;
    }
    
    /** Make the legend */
    protected function make_legend()
    {
        $div = new CHtmlElement('div', array('class'=>'mt_legend'));
        $button = new CHtmlElement('div', array('class'=>'mt_legend_button'), 'Show Special Fields');
        $div->add_inner(CHtmlElement::wrap($button, 'center'));
        
        $table = new CHtmlTable(array('cellpadding'=>6, 'style'=>'display:none;'));
        $div->add_inner(CHtmlElement::wrap($table, 'center'));
        
        foreach (MessageTemplateMan::Instance()->get_field_names() as $name)
        {
            $desc = MessageTemplateMan::Instance()->get_field_description($name);
            $name = MessageTemplateMan::Instance()->get_field_decorated_name($name);
            $table->add_row(array($name, $desc));
        }
        
        $js = new CHtmlJavaScript();
        $div->add_inner($js);
        $lines = array(
            "var button = document.getElementById(button_id);",
            "var table = document.getElementById(table_id);",
            "if (table.style.display == 'inline-block') {",
            "table.style.display = 'none';",
            "button.innerHTML = 'Show Special Fields';",
            "}",
            "else {",
            "table.style.display = 'inline-block';",
            "button.innerHTML = 'Hide Special Fields';",
            "}",
        );
        $js->add_function('ToggleLegendVisibility', $lines, array('button_id', 'table_id'));
        
        $button_id = $button->get_id(true);
        $table_id = $table->get_id(true);
        $button->set_attr('onclick', "ToggleLegendVisibility(\"{$button_id}\", \"{$table_id}\")");
        
        $p = new CHtmlElement('p', array('class'=>'mt_legend_footer'));
        $div->add_inner($p);
        $p->add_inner('The message body may contain text, HTML tags, and the special fields (text inside the {{}} braces, see above). SMS messages are limited by 142 characters');
        
        return $div;
    }
}
?>