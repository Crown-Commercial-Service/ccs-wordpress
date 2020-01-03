<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;
use fewbricks\acf\layout;

/**
 * Class group_page_content_default
 * @package fewbricks\bricks
 */
class group_page_content_default extends project_brick
{

	/**
	 * @var string
	 */
	protected $label = 'Page components';

	/**
	 *
	 */
	public function set_fields()
	{

		$fc = new acf_fields\flexible_content('Components', 'rows', '202001031018a', [
			'button_label'  =>  'Add component',
			'layout' => 'row'
		]);

//		$l = new layout('', 'list_collapsibles', '201905011151b');
//		$l->add_brick(new component_list_collapsibles('list_collapsibles', '201905011151c'));
//		$fc->add_layout($l);
//
//		$l = new layout('', 'images', '201905241604a');
//		$l->add_brick(new component_list_images('images', '201905241604b'));
//		$fc->add_layout($l);

		$l = new layout('', 'text', '202001031019a');
		$l->add_brick(new component_text('text', '202001031019b'));
		$fc->add_layout($l);

//		$l = new layout('', 'sections', '201905241034a');
//		$l->add_brick(new component_list_sections('sections', '201905241034b'));
//		$fc->add_layout($l);
//
//		$l = new layout('', 'video', '201905241050a');
//		$l->add_brick(new component_video('video', '201905241050b'));
//		$fc->add_layout($l);

//        $l = new layout('', 'cta', '201905241059a');
//        $l->add_brick(new component_cta('cta', '201905241059b'));
//        $fc->add_layout($l);

//		$l = new layout('', 'Block List', '201905241104a');
//		$l->add_brick(new component_block_list('block_list', '201905241104b'));
//		$fc->add_layout($l);

//        $l = new layout('', 'links', '201905241123a');
//        $l->add_brick(new component_list_links('links', '201905241123b'));
//        $fc->add_layout($l);

		$this->add_flexible_content($fc);

	}

	/**
	 * @return array
	 */
	protected function get_brick_html()
	{
		$data = array( 'components' => array() );

		while ( $this->have_rows('rows') ) {
			$this->the_row();

			array_push( $data['components'] ,
				array(
					'html' => acf_fields\flexible_content::get_sub_field_brick_instance()->get_html(),
					'type' => acf_fields\flexible_content::get_sub_field_brick_class_name()
				)
			);
		}

		return $data;

	}

}
