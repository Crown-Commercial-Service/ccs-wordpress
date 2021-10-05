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

        $frameworks_db = $wpdb->get_results(
            $wpdb->prepare("
            SELECT DISTINCT
                f.wordpress_id,
                f.rm_number,
                f.wordpress_id,
                p.post_parent,
                title,
                u.display_name,
                p.post_author,
                f.published_status,
                p.post_modified_gmt AS post_modified,
                p.post_date,
                p.post_name AS doc_name,
                p.post_mime_type AS doc_type,
                f.date_created,
                f.date_updated
            FROM ccs_wordpress.ccs_frameworks f
            JOIN ccs_wordpress.ccs_15423_posts p
            JOIN ccs_wordpress.ccs_15423_users u
            WHERE f.wordpress_id = p.post_parent AND p.post_author = u.ID
            GROUP BY post_modified
            ORDER BY post_modified DESC;
            ", null)
            , ARRAY_A);

        $frameworks_list = array();
        foreach($frameworks_db as $framework) {
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
            if (!in_array($framework['rm_number'], array_column($frameworks_list, 'rm_number'))) {
                array_push($frameworks_list, array(
                  //  'post_id' => $framework['wordpress_id'],
                    'rm_number' => $framework['rm_number'],
                    'title' => $framework['title'],
                    'post_author' => $framework['display_name'],
                    'author_ID' => $framework['post_author'],
                    'post_status' => $framework['published_status'], 
                    'last_published' => $framework['date_updated'],
                    'post_modified' => $framework['post_modified'],
                    'doc_name' => $framework['doc_name'] ? $framework['doc_name'] : 'N/A',
                    'doc_type' => $framework['doc_type'] ? $framework['doc_type'] : 'N/A' ,
                    'associated_lots' => $frameworksLots
                ));  
            }
        }
        return $frameworks_list;
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
                SELECT user_id, meta_key, meta_value AS login_date
                FROM ccs_wordpress.ccs_15423_usermeta 
                WHERE meta_key='last_login_time'
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


    function documentsQuery($post_type) {
        global $wpdb;

        $frameworksDocsQuery = "
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
            $frameworksResults = $wpdb->get_results($wpdb->prepare($frameworksDocsQuery, null), ARRAY_A);
            return $frameworksResults;
        } 
    }

?>


