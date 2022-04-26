<?php
/**
 * Twenty Nineteen functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

/**
 * Twenty Nineteen only works in WordPress 4.7 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.7', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
	return;
}

if ( ! function_exists( 'twentynineteen_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function twentynineteen_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Twenty Nineteen, use a find and replace
		 * to change 'twentynineteen' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'twentynineteen', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 1568, 9999 );

		// This theme uses wp_nav_menu() in two locations.
		register_nav_menus(
			array(
				'menu-1' => __( 'Primary', 'twentynineteen' ),
				'footer' => __( 'Footer Menu', 'twentynineteen' ),
				'social' => __( 'Social Links Menu', 'twentynineteen' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 190,
				'width'       => 190,
				'flex-width'  => false,
				'flex-height' => false,
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		// Add support for Block Styles.
		add_theme_support( 'wp-block-styles' );

		// Add support for full and wide align images.
		add_theme_support( 'align-wide' );

		// Add support for editor styles.
		add_theme_support( 'editor-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style-editor.css' );

		// Add custom editor font sizes.
		add_theme_support(
			'editor-font-sizes',
			array(
				array(
					'name'      => __( 'Small', 'twentynineteen' ),
					'shortName' => __( 'S', 'twentynineteen' ),
					'size'      => 19.5,
					'slug'      => 'small',
				),
				array(
					'name'      => __( 'Normal', 'twentynineteen' ),
					'shortName' => __( 'M', 'twentynineteen' ),
					'size'      => 22,
					'slug'      => 'normal',
				),
				array(
					'name'      => __( 'Large', 'twentynineteen' ),
					'shortName' => __( 'L', 'twentynineteen' ),
					'size'      => 36.5,
					'slug'      => 'large',
				),
				array(
					'name'      => __( 'Huge', 'twentynineteen' ),
					'shortName' => __( 'XL', 'twentynineteen' ),
					'size'      => 49.5,
					'slug'      => 'huge',
				),
			)
		);

		// Editor color palette.
		add_theme_support(
			'editor-color-palette',
			array(
				array(
					'name'  => __( 'Primary', 'twentynineteen' ),
					'slug'  => 'primary',
					'color' => twentynineteen_hsl_hex( 'default' === get_theme_mod( 'primary_color' ) ? 199 : get_theme_mod( 'primary_color_hue', 199 ), 100, 33 ),
				),
				array(
					'name'  => __( 'Secondary', 'twentynineteen' ),
					'slug'  => 'secondary',
					'color' => twentynineteen_hsl_hex( 'default' === get_theme_mod( 'primary_color' ) ? 199 : get_theme_mod( 'primary_color_hue', 199 ), 100, 23 ),
				),
				array(
					'name'  => __( 'Dark Gray', 'twentynineteen' ),
					'slug'  => 'dark-gray',
					'color' => '#111',
				),
				array(
					'name'  => __( 'Light Gray', 'twentynineteen' ),
					'slug'  => 'light-gray',
					'color' => '#767676',
				),
				array(
					'name'  => __( 'White', 'twentynineteen' ),
					'slug'  => 'white',
					'color' => '#FFF',
				),
			)
		);

		// Add support for responsive embedded content.
		add_theme_support( 'responsive-embeds' );
	}
endif;
add_action( 'after_setup_theme', 'twentynineteen_setup' );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function twentynineteen_widgets_init() {

	register_sidebar(
		array(
			'name'          => __( 'Footer', 'twentynineteen' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Add widgets here to appear in your footer.', 'twentynineteen' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

}
add_action( 'widgets_init', 'twentynineteen_widgets_init' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width Content width.
 */
function twentynineteen_content_width() {
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters( 'twentynineteen_content_width', 640 );
}
add_action( 'after_setup_theme', 'twentynineteen_content_width', 0 );

/**
 * Enqueue scripts and styles.
 */
function twentynineteen_scripts() {
	wp_enqueue_style( 'twentynineteen-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

	wp_style_add_data( 'twentynineteen-style', 'rtl', 'replace' );

	if ( has_nav_menu( 'menu-1' ) ) {
		wp_enqueue_script( 'twentynineteen-priority-menu', get_theme_file_uri( '/js/priority-menu.js' ), array(), '1.1', true );
		wp_enqueue_script( 'twentynineteen-touch-navigation', get_theme_file_uri( '/js/touch-keyboard-navigation.js' ), array(), '1.1', true );
	}

	wp_enqueue_style( 'twentynineteen-print-style', get_template_directory_uri() . '/print.css', array(), wp_get_theme()->get( 'Version' ), 'print' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'twentynineteen_scripts' );

/**
 * Fix skip link focus in IE11.
 *
 * This does not enqueue the script because it is tiny and because it is only for IE11,
 * thus it does not warrant having an entire dedicated blocking script being loaded.
 *
 * @link https://git.io/vWdr2
 */
function twentynineteen_skip_link_focus_fix() {
	// The following is minified via `terser --compress --mangle -- js/skip-link-focus-fix.js`.
	?>
	<script>
	/(trident|msie)/i.test(navigator.userAgent)&&document.getElementById&&window.addEventListener&&window.addEventListener("hashchange",function(){var t,e=location.hash.substring(1);/^[A-z0-9_-]+$/.test(e)&&(t=document.getElementById(e))&&(/^(?:a|select|input|button|textarea)$/i.test(t.tagName)||(t.tabIndex=-1),t.focus())},!1);
	</script>
	<?php
}
add_action( 'wp_print_footer_scripts', 'twentynineteen_skip_link_focus_fix' );

/**
 * Enqueue supplemental block editor styles.
 */
function twentynineteen_editor_customizer_styles() {

	wp_enqueue_style( 'twentynineteen-editor-customizer-styles', get_theme_file_uri( '/style-editor-customizer.css' ), false, '1.1', 'all' );

	if ( 'custom' === get_theme_mod( 'primary_color' ) ) {
		// Include color patterns.
		require_once get_parent_theme_file_path( '/inc/color-patterns.php' );
		wp_add_inline_style( 'twentynineteen-editor-customizer-styles', twentynineteen_custom_colors_css() );
	}
}
add_action( 'enqueue_block_editor_assets', 'twentynineteen_editor_customizer_styles' );

/**
 * Display custom color CSS in customizer and on frontend.
 */
function twentynineteen_colors_css_wrap() {

	// Only include custom colors in customizer or frontend.
	if ( ( ! is_customize_preview() && 'default' === get_theme_mod( 'primary_color', 'default' ) ) || is_admin() ) {
		return;
	}

	require_once get_parent_theme_file_path( '/inc/color-patterns.php' );

	$primary_color = 199;
	if ( 'default' !== get_theme_mod( 'primary_color', 'default' ) ) {
		$primary_color = get_theme_mod( 'primary_color_hue', 199 );
	}
	?>

	<style type="text/css" id="custom-theme-colors" <?php echo is_customize_preview() ? 'data-hue="' . absint( $primary_color ) . '"' : ''; ?>>
		<?php echo twentynineteen_custom_colors_css(); ?>
	</style>
	<?php
}
add_action( 'wp_head', 'twentynineteen_colors_css_wrap' );

/**
 * SVG Icons class.
 */
require get_template_directory() . '/classes/class-twentynineteen-svg-icons.php';

/**
 * Custom Comment Walker template.
 */
require get_template_directory() . '/classes/class-twentynineteen-walker-comment.php';

/**
 * Enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * SVG Icons related functions.
 */
require get_template_directory() . '/inc/icon-functions.php';

/**
 * Custom template tags for the theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

add_post_type_support( 'whitepaper', 'excerpt' );

add_post_type_support( 'digital_brochure', 'excerpt' );

add_post_type_support('downloadable', 'excerpt');

function removing_post_tag_from_taxonomy_list(){
    register_taxonomy('post_tag', array());
}

add_filter ('wp_insert_attachment_data','unattach_media_from_post', 10, 2);

function unattach_media_from_post ($data, $postarr) {
  $data['post_parent'] = 0;
  return $data;
};

// Add last modified column to frameworks
add_filter( 'manage_framework_posts_columns', 'framework_add_custom_column' );
function framework_add_custom_column( $columns ) {
    $columns['modified'] = 'Last Modified';

    return $columns;
}

// Add the data to the modified column
add_action( 'manage_framework_posts_custom_column' , 'framework_add_custom_column_data', 10, 2 );
function framework_add_custom_column_data( $column, $post_id ) {
    switch ( $column ) {
        case 'modified' :
			$date_format = 'Y/m/d';
			$post = get_post( $post_id );
			echo get_the_modified_date( $date_format, $post ); // the data that is displayed in the column
            break;
    }
}

// Make the modified column sortable
add_filter( 'manage_edit-framework_sortable_columns', 'framework_add_custom_column_make_sortable' );

function framework_add_custom_column_make_sortable( $columns ) {
	$columns['modified'] = 'modified';

	return $columns;
}

// Add sort request to framworks list page
add_action( 'load-edit.php', 'framework_add_custom_column_sort_request' );
function framework_add_custom_column_sort_request() {
	add_filter( 'request', 'framework_add_custom_column_do_sortable' );
}

// Handle the modified column sorting
function framework_add_custom_column_do_sortable( $vars ) {
	// check if sorting has been applied
	if ( isset( $vars['orderby'] ) && 'modified' == $vars['orderby'] ) {

		// apply the sorting to the frameworks list
		$vars = array_merge(
			$vars,
			array(
				'orderby' => 'post_modified'
			)
		);
	}

	return $vars;
}

add_action('save_post_framework', 'update_lot_data');
function update_lot_data ($post_id) {
		// get lot content from request method
		$lot_content = $_REQUEST['lotContent'];
		$lot_keys = array_keys($lot_content);

		// update each lot post
		if(lot_data_valid($lot_content, $lot_keys)) {
			foreach ($lot_keys as $lot_key) {
				update_lot_post($lot_key,$lot_content[$lot_key]);	
			}
		}
    	
}

function lot_data_valid ($lot_content, $lot_keys) {

	$lot_content_length = count($lot_content); 
	$lot_keys_length = count($lot_keys);

	if (!empty($lot_content_length) ) {
		// confirm that all lotdata is there by all lot arrays having the same length
		if ($lot_content_length == $lot_keys_length) {
			return true;
		}
	}

	return false;
}

function update_lot_post ($lot_id,$lot_content) {

	// update post args
	$update_lot_args = [
		'ID' => $lot_id,
		'post_content' => $lot_content,
	];

	// check if lot post type has no revision, this avoids infinite loop as save post is called again
	if ( ! wp_is_post_revision( $lot_id ) ){ 
        // update the post which calls save_post again
        wp_update_post( $update_lot_args );
    }		

}

add_filter( 'ajax_query_attachments_args', 'show_document_only_for_framework_author' );
function show_document_only_for_framework_author( $query ) {
	
	$user = wp_get_current_user();
	$filtered_mime_types = array();

	foreach( get_allowed_mime_types() as $key => $type ):
		if( false === strpos( $type, 'image' ) )
			$filtered_mime_types[] = $type;
	endforeach;

	if ( in_array( 'framework_author', (array) $user->roles) and (!array_key_exists('post_mime_type', $query) or $query['post_mime_type'] == "image")) {
		$query['post_mime_type'] = implode( ',', $filtered_mime_types );
	}

	return $query;
}

function wpse_20160421_get_author_meta($object, $field_name, $request) {

	$displayName = get_userdata( $object['author'] )->display_name;
    return $displayName;
}

function wpse_20160421_register_author_meta_rest_field() {
    register_rest_field('post', 'authorName', array(
        'get_callback'    => 'wpse_20160421_get_author_meta',
        'update_callback' => null,
        'schema'          => null,
    ));
}

add_action('rest_api_init', 'wpse_20160421_register_author_meta_rest_field');

add_filter( 'rest_prepare_post', 'post_featured_image_and_category_type_json', 10, 3 );

function post_featured_image_and_category_type_json( $data ) {
	$featured_image_id = $data->data['featured_media']; 
	$featured_image_url = wp_get_attachment_image_src( $featured_image_id, 'news-size-m' );
	
	$data->data['featured_image_url'] = $featured_image_url ? $featured_image_url[0] : false;
	$data->data['alt_text'] = get_post_meta($featured_image_id, '_wp_attachment_image_alt', true);;
	$data->data['category_type'] = get_the_category($data->data['id'])[0]->name;

	return $data;
  }

function getAllHiddenPosts()
{

	$args = array(
		'fields'          => 'ids',
		'numberposts'   => 100,
		'post_type'		=> 'post',
		'meta_query'	=> array(
			'relation'		=> 'AND',
			array(
				'key'	  	=> 'Hide_from_View_All',
				'value'	  	=> '1',
				'compare' 	=> '=',
			),
		),
	);

	return get_posts($args);
}

add_action('pre_get_posts', function ($query) {

	$taxArray = array();

	foreach ((array)$query->query["tax_query"] as $each) {
		array_push($taxArray, $each["taxonomy"]);
	}

	if ($query->query["post_type"] == "post" && $query->query["posts_per_page"] != 100 && !(in_array("products_services", $taxArray) || in_array("sectors", $taxArray))) {
		$hiddenPostsID = getAllHiddenPosts();
		$query->set('post__not_in', $hiddenPostsID);
	}
});