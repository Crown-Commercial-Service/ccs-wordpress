<?php

    // function query() {
    //     global $wpdb;
    //     $db_results = $wpdb->get_results("SELECT * FROM ccs_wordpress.ccs_lots");
    //     echo "<pre>"; print_r($db_results); echo "</pre>";
    // }

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
        global $wpdb;
        $frameworks = $wpdb->get_results(
            $wpdb->prepare("
                SELECT 
                    wordpress_id,
                    rm_number,
                    title,
                    display_name,
                    post_author,
                    published_status,
                    date_created,
                    date_updated
                FROM ccs_frameworks f
                JOIN ccs_15423_posts p
                JOIN ccs_15423_users u
                WHERE f.wordpress_id = p.post_parent AND p.post_author = u.ID
            ", null)
            , ARRAY_A);

        $results = array();
        foreach($frameworks as $framework) {
            $frameworksLots = array_map(
                function ($lot) {
                    return [
                        'lot_title' => $lot['title'],
                        'lot_id' => $lot['id']
                    ];
                },
                frameworksLotsQuery($framework['wordpress_id']) // lots array by framework id
            );
            // $frameworksDocuments = frameworksDocumentsQuery($framework['wordpress_id']);
            array_push($results, array(
                'post_id' => $framework['wordpress_id'],
                'rm_number' => $framework['rm_number'],
                'title' => $framework['title'],
                'post_author' => $framework['display_name'],
                'author_ID' => $framework['post_author'],
                'post_status' => $framework['published_status'], 
                'post_written' => $framework['date_created'],
                'post_modified' => $framework['date_updated'],
                'associated_lots' => $frameworksLots
            ));   
        }
        return $results;
    }


    function authorsQuery() {
        global $wpdb;
        $frameworks = $wpdb->get_results(
            $wpdb->prepare("
            SELECT 
                post_title,
                post_modified,
                post_author,
                post_parent,
                post_status,
                login_date,
                display_name
            FROM(
                SELECT 
                    max(login_date) AS login_date,
                    a.user_id
                FROM ccs_wordpress.ccs_15423_aiowps_login_activity a
                GROUP BY a.user_id
            ) temp
            JOIN ccs_wordpress.ccs_15423_posts p
                ON temp.user_id = p.post_author
            JOIN ccs_wordpress.ccs_15423_users u
                ON temp.user_id = u.ID
            WHERE 
                post_type = 'framework' AND post_status = 'publish'
            GROUP BY post_modified, u.ID, post_title
            ORDER BY u.ID, post_modified DESC;    
            ", null)
        , ARRAY_A);

        // $loginDetails = $wpdb->get_results(
        //     $wpdb->prepare("
        //         SELECT 
        //             display_name,
        //             login_date,
        //             post_author,
        //         FROM ccs_frameworks f
        //         JOIN ccs_15423_posts p
        //             ON f.wordpress_id = p.post_parent 
        //         JOIN ccs_15423_users u
        //             ON p.post_author = u.ID
        //         JOIN ccs_15423_aiowps_login_activity a
        //             ON a.user_id = u.ID
        //     ", null)
        // , ARRAY_A);

        $authors = array();
        foreach($frameworks as $framework) {
            $currentAuthorID = $framework['post_author'];
            // check if author details already added
            $arrColumn = array_map(function($element) {
                return $element['author_ID'];
            }, $authors);
            $alreadyAdded = array_search($currentAuthorID, $arrColumn);

            if($alreadyAdded == null && $alreadyAdded !== 0) {
                $frameworkDetails = array(
                    'post_id' => $framework['post_parent'],
                    'title' => $framework['post_title'],
                    // 'post_written' => $framework['date_created'],
                    'post_modified' => $framework['post_modified'],
                    'post_status' => $framework['post_status'],
                    // 'permalink' => 'permalink'
                );
                array_push($authors, array(
                    'author_name' => $framework['display_name'],
                    'author_ID' => $framework['post_author'],
                    'last_login' => $framework['login_date'], //$framework['login_date'],
                    'authored_frameworks' => array()
                ));

                // locate the 'authored_frameworks' subarray for current author
                $arrColumn = array_map(function($element) {
                    return $element['author_ID'];
                }, $authors);
                $key = array_search($currentAuthorID, $arrColumn);
                array_push($authors[$key]['authored_frameworks'], $frameworkDetails );
                // $frameworksSorted = sortFrameworks($authors[$key]);
                // array_push($authors[$key]['authored_frameworks'], $frameworksSorted );
                
            }
        }   
        return $authors;
    }

    // function authorsQuery() {
    //     $args = array(
    //         'post_type' => 'framework', 
    //     );
    //     $allFrameworks = new WP_Query($args);
 
    //     $authors = array();
  
    //     while($allFrameworks->have_posts()) {
    //         $allFrameworks->the_post();
    //         $currentAuthorID = get_the_author_ID();

    //         // check if author details already added
    //         $arrColumn = array_map(function($element) {
    //             return $element['author_ID'];
    //           }, $authors);
    //         $alreadyAdded = array_search($currentAuthorID, $arrColumn);
    //         $frameworkDetails = array(
    //             'post_id' => get_the_id(),
    //             'title' => get_the_title(),
    //             'post_written' => get_the_date(),
    //             'post_modified' => get_the_modified_date(),
    //             'post_status' => get_post_status(),
    //             'permalink' => get_the_permalink(get_the_id())
    //         );
           
    //         // check if author details already added
    //         if($alreadyAdded == null && $alreadyAdded !== 0) {
    //             array_push($authors, array(
    //                 'author_name' => get_the_author(),
    //                 'author_ID' => get_the_author_ID(),
    //                 'last_login' => get_user_meta(get_the_author_ID(), 'last_login_time'),
    //                 'authored_frameworks' => array()
    //             ));
    //         }
            
    //         // locate the 'authored_frameworks' subarray for current author
    //         $arrColumn = array_map(function($element) {
    //             return $element['author_ID'];
    //           }, $authors);
    //         $key = array_search($currentAuthorID, $arrColumn);
    //         array_push($authors[$key]['authored_frameworks'], $frameworkDetails );
    //         // $frameworksSorted = sortFrameworks($authors[$key]);
    //         // array_push($authors[$key]['authored_frameworks'], $frameworksSorted );
            
    //     }
    //     return $authors;
    // }

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


