<?php

    function query() {
        global $wpdb;
        $db_results = $wpdb->get_results("SELECT * FROM ccs_wordpress.ccs_lots");
        echo "<pre>"; print_r($db_results); echo "</pre>";
    }

    function frameworksLotsQuery($frameworkID) {
        global $wpdb;
        $db_results = $wpdb->get_results(
            $wpdb->prepare("
                SELECT 
                    ccs_lots.id,
                    ccs_lots.title,
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
                ", $frameworkID)
            , ARRAY_A);
        // echo "Associated Lots: ";
        // echo "<pre>"; print_r($db_results); echo "</pre>";
        // echo "End of associated lots";
        return $db_results;
    }

    function frameworksQuery() {
        $frameworks = new WP_Query(array(
            'post_type' => 'framework',
            // 'meta_key' => 'post_status',
            // 'meta_query' => array(
            //     array(
            //         'key' => 'post_status',
            //         'compare' => '===',
            //         'value' => 'publish'
            //     )
            // ),
            // 's' => sanitize_text_field($data['term'])
        ));
        $results = array();
        while($frameworks->have_posts()) {
            $frameworks->the_post();
            $frameworksLots = array_map(
                function ($lot) {
                    return [
                        'lot_title' => $lot['title'],
                        'lot_id' => $lot['id']
                    ];
                },
                frameworksLotsQuery(get_the_id()) // lots array by framework id
            );
            // $frameworksDocuments = frameworksDocumentsQuery(get_the_id());
            array_push($results, array(
                'post_id' => get_the_id(),
                'title' => get_the_title(),
                'post_author' => get_the_author(),
                'author_ID' => get_the_author_ID(),
                'post_status' => get_post_status(),
                'post_written' => get_the_date(),
                'post_modified' => get_the_modified_date(),
                'associated_lots' => $frameworksLots,
                'permalink' => get_the_permalink(get_the_id())
            ));   
        }
        return $results;
    }

    function authorsQuery() {
        $args = array(
            'post_type' => 'framework', 
        );
        $allFrameworks = new WP_Query($args);
 
        $authors = array();
  
        while($allFrameworks->have_posts()) {
            $allFrameworks->the_post();
            $currentAuthorID = get_the_author_ID();

            // check if author details already added
            $arrColumn = array_map(function($element) {
                return $element['author_ID'];
              }, $authors);
            $alreadyAdded = array_search($currentAuthorID, $arrColumn);
            $frameworkDetails = array(
                'post_id' => get_the_id(),
                'title' => get_the_title(),
                'post_written' => get_the_date(),
                'post_modified' => get_the_modified_date(),
                'post_status' => get_post_status(),
                'permalink' => get_the_permalink(get_the_id())
            );
           
            // check if author details already added
            if($alreadyAdded == null && $alreadyAdded !== 0) {
                array_push($authors, array(
                    'author_name' => get_the_author(),
                    'author_ID' => get_the_author_ID(),
                    'last_login' => get_user_meta(get_the_author_ID(), 'last_login_time'),
                    'authored_frameworks' => array()
                ));
            }
            
            // locate the 'authored_frameworks' subarray for current author
            $arrColumn = array_map(function($element) {
                return $element['author_ID'];
              }, $authors);
            $key = array_search($currentAuthorID, $arrColumn);
            array_push($authors[$key]['authored_frameworks'], $frameworkDetails );
            // $frameworksSorted = sortFrameworks($authors[$key]);
            // array_push($authors[$key]['authored_frameworks'], $frameworksSorted );
            
        }
        return $authors;
    }

    function sortFrameworks($frameworks) {
        $sortedFrameworks = array();
            foreach ($frameworks as $index => $row){
                $sortedFrameworks[$index] = $row['authored_frameworks'];
            }
            array_multisort($sortedFrameworks, SORT_DESC, $frameworks);
    }

    function documentsQuery($post_type) {
        global $wpdb;

        $frameworksQuery = "
            SELECT 
                u.display_name,
                f.rm_number,
                f.title,
                p.post_author,
                p.post_date,
                p.post_name,
                p.post_title AS document_title,
                p.guid AS url,
                p.post_name AS document_name,
                p.post_mime_type
            FROM ccs_15423_posts p
            JOIN ccs_frameworks f
            JOIN ccs_15423_users u
            WHERE f.wordpress_id = p.post_parent AND post_type = 'attachment' AND p.post_author = u.ID
        ";
        

        if($post_type === "frameworks") {
            $frameworksResults = $wpdb->get_results($wpdb->prepare($frameworksQuery, null), ARRAY_A);
            return $frameworksResults;
        } 
    }

?>


