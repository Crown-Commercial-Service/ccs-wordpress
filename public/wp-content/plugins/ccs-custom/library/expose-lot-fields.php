<?php

function ccs_register_meta_boxes() {
    add_meta_box('framework_lot_information', 'Framework Lots', 'ccs_expose_lots_for_framework', 'framework');
}
add_action( 'add_meta_boxes', 'ccs_register_meta_boxes' );

/**
 * Output lot information on the framework edit page in WordPress
 */
function ccs_expose_lots_for_framework() {
    global $post;
    global $wpdb;

    $framework_lots = $wpdb->get_results(
        $wpdb->prepare("
        SELECT 
          ccs_lots.id,
          ccs_lots.wordpress_id,
          ccs_lots.status,
          ccs_lots.expiry_date,
          ccs_lots.lot_number
        FROM 
            ccs_lots 
        INNER JOIN 
            ccs_frameworks 
        ON 
            ccs_lots.framework_id = ccs_frameworks.salesforce_id
        WHERE
            ccs_frameworks.wordpress_id = %d
        ORDER BY
          ccs_lots.id ASC
        ", $post->ID)
        , ARRAY_A);


    if(!empty($framework_lots)) {
        $lot_post_ids = [];
        $lot_custom_data = [];

        foreach($framework_lots as $lot) {
            if($lot['wordpress_id'] != 0) {
                $lot_post_ids[] = $lot['wordpress_id'];
                $lot_custom_data[$lot['wordpress_id']] = $lot;
            }
        }

        $lots_query = new WP_Query(array('post__in' => $lot_post_ids, 'post_type' => 'lot', 'orderby' => 'ID', 'order' => 'ASC'));

        

        if ( $lots_query->have_posts() ) {
            while ( $lots_query->have_posts() ) {
                $lots_query->the_post();
                $lot_id = get_the_ID();
                $lot_title = get_the_title();
                $editLink = get_edit_post_link($lot_id);
                // variables for tinymce editor
                $content   = get_the_content($lot_id);
                $editor_id = 'lots-editor' . $lot_id; 
                $editor_settings = [
                    'textarea_name' => 'lotContent[' . $lot_id . ']',
                ]?>
                
                <div>
                    <input type="text" name="lotTitles[<?php echo $lot_id ?>]" value="<?php echo $lot_title; ?>" style="width:100%" />
                    <div class="custom-lots-editor" style="margin-top : 20px;">
                        <?php echo wp_editor( $content, $editor_id, $editor_settings); ?>
                    </div>     
                </div>

                <?php
            }
        }
        wp_reset_postdata();
    }
}
