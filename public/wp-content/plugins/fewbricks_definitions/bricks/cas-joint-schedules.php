<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class cas_joint_schedules
 * @package fewbricks\bricks
 */
class cas_joint_schedules extends project_brick {

    /**
     * @var string This will be the default label showing up in the editor area for the administrator.
     * It can be overridden by passing an item with the key "label" in the array that is the second argument when
     * creating a brick.
     */
    protected $label = 'Joint schedules';

    /**
     * This is where all the fields for the brick will be set-
     */
    public function set_fields() {

        $this->add_field( (new acf_fields\repeater("Documents - {$this->label}", 'joint_schedule', '202205171420a', [
            'button_label' => 'Add Document'
        ]))
            ->add_sub_field( new acf_fields\text( 'Document Name', 'document_name', '202205171420b', [
                'required' => 1,
                ] ) )
        
            ->add_sub_field( new acf_fields\file( 'Document', 'document', '202205171420c', [
                'wrapper' => array (
                    'width' => '33',
                    'class' => '',
                    'id' => ''),
                'required' => 1,
            ] ) )
        
            ->add_sub_field( new acf_fields\radio( 'Document type', 'document_type', '202205171420d', [
                'wrapper' => array (
                    'width' => '33',
                    'class' => '',
                    'id' => ''),
                'choices' => array(
                    'essential'	=> 'Essential document',
                    'optional'	=> 'Optional document',
                ),
                'required' => 1,
            ] ) )
        
            ->add_sub_field( new acf_fields\radio( 'Document Usage', 'document_usage', '202205171420e', [
                'wrapper' => array (
                    'width' => '33',
                    'class' => '',
                    'id' => ''),
                'choices' => array(
                    'read_only'	=> 'Read only',
                    'enter_detail'	=> 'You will need to enter details in this document',
                    'enter_detail_optional'	=> 'If you use this schedule, you will need to enter details in this document',
                ),
                'required' => 1,
            ] ) )
        
            ->add_sub_field( new acf_fields\wysiwyg( 'Document Description', 'document_description', '202205171420f',[
                'required' => 1,
            ] ) )
        );

    }

}
