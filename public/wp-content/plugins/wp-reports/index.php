<?php 
include 'reports-api.php';
/*
Plugin Name: WP Reports Plugin
Version: 2.0
Description: A plugin to display custom admin reports
*/


if ( ! defined('ABSPATH')) exit; // exit if accessed directly


class WpReportsPlugin {

    private $authorsAPI = 'http://ccs-agreements.cabinetoffice.localhost/wp-json/wp-reports-plugin/v2/authors';
    private $frameworksAPI = 'http://ccs-agreements.cabinetoffice.localhost/wp-json/wp-reports-plugin/v2/frameworks';
    private $documentsAPI = "http://ccs-agreements.cabinetoffice.localhost/wp-json/wp-reports-plugin/v2/documents/type=frameworks";

    function __construct() {
        add_action('admin_menu', array($this, 'reportsMenu'));
        add_action('admin_init', array($this, 'reportsSettings'));
    }
    
    function reportsMenu () {
        // Main Reports Page
        $page_browser_title = 'Reports'; // browser tab title
        $page_menu_title = 'Reports'; // title in admin sidebar
        $capability = 'manage_options'; // required user permissions
        $main_page_slug = 'wp-reports'; // menu slug
        $callback = array($this, 'frameworksReportsPage'); // the html output
        $icon = 'dashicons-clipboard'; // icon that will appear in the admin menu
        $position = 1; // position in the admin menu
        
        $main_page_hook = add_menu_page($page_browser_title, $page_menu_title, $capability, $main_page_slug, $callback, $icon, $position);
        

        // Main-Submenu Page
        // $submenu_browser_title = "Reports Overview";
        // $submenu_title = "Reports Overview";
        
        // $submenu_page_hook = add_submenu_page($main_page_slug, $submenu_browser_title, $submenu_title, $capability, $main_page_slug, $callback);


        // Frameworks Page – (Main-Submenu Page)
        $frameworks_page_title = "Frameworks Reports";
        $frameworks_menu_title = "Frameworks";
        $frameworks_page_slug = "frameworks-reports";
        $frameworks_page_HTML = array($this, "frameworksReportsPage");
    
        $frameworksPageHook = add_submenu_page($main_page_slug, $frameworks_page_title, $frameworks_menu_title, $capability, $main_page_slug, $frameworks_page_HTML);
        
        // Authors Page
        $authors_page_title = "Authors Reports";
        $authors_menu_title = "Authors";
        $authors_page_slug = "authors-reports";
        $authors_page_HTML = array($this, 'authorsReportsPage');

        $authorsPageHook = add_submenu_page($main_page_slug, $authors_page_title, $authors_menu_title, $capability, $authors_page_slug, $authors_page_HTML);
        
        // Documents Page
        $documents_page_title = "Documents Reports";
        $documents_menu_title = "Documents";
        $documents_page_slug = "documents-reports";
        $documents_page_HTML = array($this, 'documentsReportsPage');

        $documentsPageHook = add_submenu_page($main_page_slug, $documents_page_title, $documents_menu_title, $capability, $documents_page_slug, $documents_page_HTML);
    
        // Load CSS
        add_action("load-{$authorsPageHook}", array($this, 'reportsPluginAssets'));
        add_action("load-{$frameworksPageHook}", array($this, 'reportsPluginAssets'));
        add_action("load-{$documentsPageHook}", array($this, 'reportsPluginAssets'));
    }

    /**
     *  Load CSS
     */
    function reportsPluginAssets() {
        wp_enqueue_style('reportsCss', plugin_dir_url(__FILE__) . 'styles.css');
    }


    function reportsSettings() {

        // registerSettingsTest();
        $documents_settings_section_name = "reports-settings-documents";
        $documents_settings_section_title = "";
        $documents_settings_section_head = null;
        $documents_settins_slug = "documents-reports";
        add_settings_section($documents_settings_section_name, $documents_settings_section_title, $documents_settings_section_head, $documents_settins_slug);

        register_setting('documentsSettings', 'sortByMenu');
        add_settings_field('sort-by-menu', 'Sort By', array($this, 'sortByMenuHTML'), 'documents-reports', 'reports-settings-documents');

        // register_setting('documentsSettings', 'searchDocuments');
        // add_settings_field('search-documents', 'Search', array($this, 'searchDocumentsHTML'), 'documents-reports', 'reports-settings-documents');
        
    }

    /**
     * HTML methods
     */

    function toggleDocumentsSelectHTML() { ?>

        <label for="toggleDocumentsSelect">Choose Document Type:</label>
            <select name="toggleDocumentsSelect" id="toggleDocumentsSelect" >
            <option value="frameworks" <?php echo esc_attr(get_option('toggleDocumentsSelect', 1)) === "frameworks" ? "selected" : ""; ?>>Frameworks</option>
            <option value="all-posts" <?php echo esc_attr(get_option('toggleDocumentsSelect', 1)) === "all-posts" ? "selected" : ""; ?>>All Posts</option>
        </select>
        <?php
    }

    function searchDocumentsHTML() {
    ?>
        <!-- <label for="searchDocuments">Search for:</label> -->
        <input type="text" name="searchDocuments" value="<?php echo esc_attr(get_option('searchDocuemnts', "")) ?>">
    <?php
    }

    function sortByMenuHTML() {
        $selectedOption = !get_option('sortByMenu') ? "framework-title" : get_option('sortByMenu');
    ?>
        <!-- <label for="sortByMenu">Sort Documents By:</label> -->
        <select name="sortByMenu" id="sortByMenu" >
            <option value="title" <?php echo esc_attr($selectedOption) === "title" ? "selected" : ""; ?>>Document Title</option>
            <option value="post_mime_type" <?php echo esc_attr($selectedOption) === "post_mime_type" ? "selected" : ""; ?>>File Type</option>
            <option value="post_date" <?php echo esc_attr($selectedOption) === "post_date" ? "selected" : ""; ?>>Date Uploaded</option>
            <option value="title" <?php echo esc_attr($selectedOption) === "title" ? "selected" : ""; ?>>Framework Title</option>
            <option value="display_name" <?php echo esc_attr($selectedOption) === "display_name" ? "selected" : ""; ?>>Author</option>
        </select>
     <?php
    }

    /**
     *  FRAMEWORKS PAGE HTML METHOD
     */
    
    function frameworksReportsPage() {
        $json = file_get_contents($this->frameworksAPI);
        $frameworks = json_decode($json, TRUE);
        // echo '$frameworks: ';
        // var_dump($frameworks);
        ?>
        <div>
        <h1>Frameworks Report</h1>
        <table class="reports-table">
            <tr>
                <th>Framework RM No</th>
                <th>Framework Name</th>
                <th>Status</th>
                <th>Author</th>
                <th>Date Created</th>
                <th>Last Published</th>
                <th>Associated Lot Pages</th>
                <!-- <th>Linked Documents</th> -->
            </tr>
        <?php
        foreach ($frameworks as $framework) { 
            // echo "Author ID: ";
            // var_dump($framework['author_ID']);
            $noAndTitle = explode(':', $framework['title']);
            $permalink = $framework['permalink'];
            ?>
            <tr> 
                <td><?php echo $noAndTitle[0]?></td>
                <td><?php echo $noAndTitle[1]?></td>
                <td><?php echo $framework['post_status']?></td>
                <td><?php echo $framework['post_author']?></td>
                <td><?php echo $framework['post_written']?></td>
                <td><?php echo $framework['post_modified']?></td>
                <td>
                    <?php 
                        foreach($framework['associated_lots'] as $lot_index => $lot) {
                            // $keys = implode(" ", array_keys($lot));
                            // echo $keys;
                            // echo "\r\n";
                            // $values = implode("\r\n ", $lot);
                            // echo "<p>";
                            echo "Lot ID: ".$lot['lot_id'].",\r\n";
                            echo "Lot title: ".$lot['lot_title'].";";
                            // echo "</p>";
                            if ($lot_index != 0 && $lot_index < (sizeof($framework['associated_lots'])) - 1 ) {
                                echo "<hr>";
                            }
                        }
                    ?>
                </td>
            </tr>
        
        <?php 
        } ?>
        </table>
        </div>
    <?php } 
    

    /**
     *  AUTHORS PAGE HTML METHOD
     */

    function authorsReportsPage() {
        $json = file_get_contents($this->authorsAPI);
        $authors = json_decode($json, TRUE);
        //var_dump($authors);
        ?>
        <div>
        <h1>Frameworks' Authors Report</h1>
        <table class="reports-table">
            <tr>
                <th>Author Name</th>
                <th>Last Accessed Wordpress</th>
                <th>Last Updated Framework</th>
                <th>Last Update Date</th>
            </tr>
                <?php
                    foreach($authors as $author) { 
                    // var_dump($author['authored_frameworks']);
                    usort($author['authored_frameworks'], 'compareDate');
                    $permalink = $author['authored_frameworks'][0]['permalink'];
                    ?>
                     <tr>
                        <td><?php echo $author['author_name'] ?></td>
                        <td><?php echo implode(" ", $author['last_login']) ?></td>
                        <td><?php echo $author['authored_frameworks'][0]['title'] ?></td>
                        <td><?php echo $author['authored_frameworks'][0]['post_modified'] ?></td>
                    </tr>
                <?php
                    }
                ?>
        </table>
        <?php
    }

    /**
     *  DOCUMENTS PAGE HTML METHOD
     */

    function documentsReportsPage() {
        // $documentType = esc_attr(get_option('toggleDocumentsSelect'));
        $restURL = $this->documentsAPI;
        // var_dump($documentType);
        $json = file_get_contents($restURL);
        $documents = json_decode($json, TRUE);
        //echo '$documents: ';
        //var_dump($documents);
        echo $this->frameworksDocsHTML($documents);
        ?>
    <?php
    } 

   
    function frameworksDocsHTML($documents) { 
        $sortBy = esc_attr(get_option('sortByMenu'));
        $sortedDocuments = sortData($documents, $sortBy);
        // var_dump($sortBy);
        ob_start(); ?>
        <div class="wrap">
            <!-- <h1>Reports Options</h1> -->
            <form action="options.php" method="POST">
                <?php
                settings_errors();
                do_settings_sections('documents-reports');
                settings_fields('documentsSettings');
                submit_button();
                ?>
            </form>
        </div>
        <div>
        <table class="reports-table">
            <tr>
                <th>Document Title</th>
                <th class="file-type_column">File Type</th>
                <th class="upload-date_column">Date of Upload</th>
                <th>Associated Framework Title</th>
                <th class="author_column">Author</th>
                <!-- <th>Associated Pages</th> -->
            </tr>
            <?php 
            foreach($sortedDocuments as $doc) {
                $permalink = "/news/frameworks/".$doc['post_name'];
                ?>
                <tr>
                    <td><?php echo $doc['document_name'] ?></td>
                    <td><?php echo $doc['post_mime_type'] ?></td>
                    <td class="upload-date_column"><?php echo $doc['post_date'] ?></td>
                    <!-- <td><?php echo $doc['title'] ?></td> -->
                    <td><?php echo $doc['title'] ?></td>
                    <td><?php echo $doc['display_name'] ?></td>
                    <!-- <td></td> -->
                </tr>
            <?php
            } ?>

        </table>
        </div> 
        <?php
        return ob_get_clean();
    }
}

$WpReportsPlugin = new WpReportsPlugin();


/**
 * HELPER METHODS
 */
function compareDate($a, $b) {
    return strcmp($a['post_modified'],$b['post_modified']);
}

function sortData($dataArray) {
    usort($dataArray, 'compareSubarrays');
    return $dataArray;
}

function compareSubarrays($a, $b) {
    if(get_option('sortByMenu') === "post_date") {
        return strcmp($b[get_option('sortByMenu')], $a[get_option('sortByMenu')]);     
    }
    return strcmp($a[get_option('sortByMenu')], $b[get_option('sortByMenu')]); 
}