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

		$l = new layout('', 'text', '202001031019a');
		$l->add_brick(new component_text('text', '202001031019b'));
		$fc->add_layout($l);

		$l = new layout('', 'intro', '202001031449a');
		$l->add_brick(new component_intro('intro', '202001031449b'));
		$fc->add_layout($l);

		$l = new layout('', 'subcategories', '202001131522a');
		$l->add_brick(new component_subcategories('subcategories', '202001131522b'));
		$fc->add_layout($l);

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
					'html' => acf_fields\flexible_content::get_sub_field_brick_instance()->get_html()
				)
			);
		}

		return $data;

	}

}
