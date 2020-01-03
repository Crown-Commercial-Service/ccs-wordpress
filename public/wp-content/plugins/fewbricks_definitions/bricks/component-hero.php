<?php

namespace fewbricks\bricks;

use fewbricks\acf\fields as acf_fields;

/**
 * Class text_and_content
 * @package fewbricks\bricks
 */
class component_hero extends project_brick {

	/**
	 * @var string This will be the default label showing up in the editor area for the administrator.
	 * It can be overridden by passing an item with the key "label" in the array that is the second argument when
	 * creating a brick.
	 */
	protected $label = 'Hero';

	/**
	 * This is where all the fields for the brick will be set-
	 */
	public function set_fields() {

		//$this->add_brick( new component_image( 'hero_object', '201701311324t' ));
		$this->add_field( new acf_fields\image( 'Hero Image', 'hero_image', '202001031023a', [
			'instructions' => 'Leave blank if no hero required.',
			'min_width' => 1042,
			'min_height' => 480,
			'return_format' => 'array'
		] ) );

		// REVIEW MICROCOPY
		// Minimum size required is 1042 pixels wide and 480 pixels tall, however the recommended dimensions are 2084 pixels wide and 960 pixels tall (uploading at these larger dimensions will ensure that retina displays receive a high resolution image). Please note that these hero images are vertically cropped on larger screen sizes - so please try and ensure the focal point of the image is vertically in the middle (as the image is cropped vertically on the center)

		$this->add_field( new acf_fields\text( 'Heading', 'hero_heading', '202001031023b' ));

	}

	/**
	 * Function to show what Twig could do for you
	 * @return array
	 */
	protected function get_brick_html() {

		// We can't use Timber to create an image object anymore so let's set return_format to `array` instead
//		$imageId = $this->get_field( 'hero_image' );
//		$data['hero_image'] = '';
//		if(!empty($imageId)) {
//			$data['hero_image'] = new \Timber\Image($imageId);
//			$data['hero_image_width'] = $data['hero_image']->width();
//		}

		$data = [
			'hero_heading'  => $this->get_field( 'hero_heading' ),
			'hero_image'  => $this->get_field( 'hero_image' )
		];

		return $data;

	}

}
