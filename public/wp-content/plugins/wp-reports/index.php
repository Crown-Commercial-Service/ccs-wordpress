<?php 
include 'reports-api.php';
/*
Plugin Name: WP Reports Plugin
Version: 2.0
Description: A plugin to display custom admin reports
*/

$authorsPath = 'http://' . getenv('WP_SITEURL') . '/wp-json/wp-reports-plugin/v2/authors';
$frameworksPath = 'http://' . getenv('WP_SITEURL') . '/wp-json/wp-reports-plugin/v2/frameworks';
$documentsPath = 'http://' . getenv('WP_SITEURL') . '/wp-json/wp-reports-plugin/v2/documents/type=frameworks';

$WpReportsPlugin = new WpReportsPlugin($authorsPath, $frameworksPath,$documentsPath);

if ( ! defined('ABSPATH')) exit; // exit if accessed directly

class WpReportsPlugin {

    private $authorsAPI;
    private $frameworksAPI;
    private $documentsAPI;

    function __construct($authorsPath, $frameworksPath, $documentsPath) {

        $this->authorsAPI = $authorsPath;
        $this->frameworksAPI = $frameworksPath;
        $this->documentsAPI = $documentsPath;
        add_action('admin_menu', array($this, 'reportsMenu'));
        add_action('admin_init', array($this, 'registerReportsSettings'));
        add_action('admin_post_authors_form', array($this, 'downloadAuthorsReport'));
        add_action('admin_post_frameworks_form', array($this, 'downloadFrameworksReport'));
        add_action('admin_post_documents_form', array($this, 'downloadDocumentsReport'));
    }

    function reportsMenu () {
        // Main Reports Page
        $page_browser_title = 'Reports'; // browser tab title
        $page_menu_title = 'Reports'; // title in admin sidebar
        $capability = 'manage_options'; // required user permissions
        $main_page_slug = 'download-reports'; // make Authors page the main page
        $callback = array($this, 'downloadReportsPage'); // make Authors page the main page
        $icon = 'dashicons-clipboard'; // icon that will appear in the admin menu
        $position = 1; // position in the admin menu
        
        $main_page_hook = add_menu_page($page_browser_title, $page_menu_title, $capability, $main_page_slug, $callback, $icon, $position);
    
        // Download Page
        $download_page_title = "Download Reports";
        $download_menu_title = "Download";
        $download_page_slug = "download-reports";
        $download_page_HTML = array($this, 'downloadReportsPage');
        $downloadPageHook = add_submenu_page($main_page_slug, $download_page_title, $download_menu_title, $capability, $download_page_slug, $download_page_HTML);

        // Authors Page
        $authors_page_title = "Authors Reports";
        $authors_menu_title = "Authors";
        $authors_page_slug = "authors-reports";
        $authors_page_HTML = array($this, 'authorsReportsPage');

        $authorsPageHook = add_submenu_page($main_page_slug, $authors_page_title, $authors_menu_title, $capability, $authors_page_slug, $authors_page_HTML);


        // Frameworks Page – (Main-Submenu Page)
        $frameworks_page_title = "Frameworks Reports";
        $frameworks_menu_title = "Frameworks";
        $frameworks_page_slug = "frameworks-reports";
        $frameworks_page_HTML = array($this, "frameworksReportsPage");
    
        $frameworksPageHook = add_submenu_page($main_page_slug, $frameworks_page_title, $frameworks_menu_title, $capability, $frameworks_page_slug, $frameworks_page_HTML);
        
 
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


    function registerReportsSettings() {


        // AUTHORS SETTINGS

        $authors_settings_section_name = "reports-settings-authors";
        $authors_settings_section_title = "";
        $authors_settings_section_head = null;
        $authors_settins_slug = "authors-reports";
        add_settings_section($authors_settings_section_name, $authors_settings_section_title, $authors_settings_section_head, $authors_settins_slug);

        register_setting('authorsSettings', 'sortByMenu_3');
        add_settings_field('sort-by-menu-3', 'Sort By', array($this, 'sortByAuthorsMenuHTML'), $authors_settins_slug, $authors_settings_section_name);


        // FRAMEWORKS SETTINGS

        $frameworks_settings_section_name = "reports-settings-frameworks";
        $frameworks_settings_section_title = "";
        $frameworks_settings_section_head = null;
        $frameworks_settins_slug = "wp-reports";
        add_settings_section($frameworks_settings_section_name, $frameworks_settings_section_title, $frameworks_settings_section_head, $frameworks_settins_slug);

        register_setting('frameworksSettings', 'sortByMenu_2');
        add_settings_field('sort-by-menu-2', 'Sort By', array($this, 'sortByFrameworksMenuHTML'), $frameworks_settins_slug, $frameworks_settings_section_name);


        // DOCUMENTS SETTINGS

        $documents_settings_section_name = "reports-settings-documents";
        $documents_settings_section_title = "";
        $documents_settings_section_head = null;
        $documents_settins_slug = "documents-reports";
        add_settings_section($documents_settings_section_name, $documents_settings_section_title, $documents_settings_section_head, $documents_settins_slug);

        register_setting('documentsSettings', 'sortByMenu');
        add_settings_field('sort-by-menu', 'Sort By', array($this, 'sortByDocsMenuHTML'), $documents_settins_slug, $documents_settings_section_name);
        
    }

    /**
     * HTML OPTION MENUS
     */

    function sortByAuthorsMenuHTML() {
        $selectedOption = !esc_attr(get_option('sortByMenu_3')) ? "author_name" : esc_attr(get_option('sortByMenu_3'));?>
        <select name="sortByMenu_3" id="sortByMenu_3" >
            <option value="author_name" <?php echo esc_attr($selectedOption) === "author_name" ? "selected" : ""; ?>>Author Name – ascending</option>
            <option value="last_login" <?php echo esc_attr($selectedOption) === "last_login" ? "selected" : ""; ?>>Last Accessed Wordpress – descending</option>
            <option value="post_modified" <?php echo esc_attr($selectedOption) === "post_modified" ? "selected" : ""; ?>>Last Updated Date – descending</option>
            <option value="title" <?php echo esc_attr($selectedOption) === "title" ? "selected" : ""; ?>>Last Updated Framework – ascending</option>
        </select>
     <?php
    }

    function sortByFrameworksMenuHTML() {
        $selectedOption = !esc_attr(get_option('sortByMenu_2')) ? "post_modified" : esc_attr(get_option('sortByMenu_2'));?>
        <select name="sortByMenu_2" id="sortByMenu_2" >
            <option value="post_modified" <?php echo esc_attr($selectedOption) === "post_modified" ? "selected" : ""; ?>>Last Modified – descending</option>
            <option value="post_author" <?php echo esc_attr($selectedOption) === "post_author" ? "selected" : ""; ?>>Modified By – ascending</option>
            <option value="last_published" <?php echo esc_attr($selectedOption) === "last_published" ? "selected" : ""; ?>>Last Published Date – descending</option>
            <option value="title" <?php echo esc_attr($selectedOption) === "title" ? "selected" : ""; ?>>Framework Title – ascending</option>
            <option value="rm_number" <?php echo esc_attr($selectedOption) === "rm_number" ? "selected" : ""; ?>>RM Number – ascending</option>
            <option value="doc_name" <?php echo esc_attr($selectedOption) === "doc_name" ? "selected" : ""; ?>>Linked Document – ascending</option>
            <option value="doc_type" <?php echo esc_attr($selectedOption) === "doc_type" ? "selected" : ""; ?>>Document Type – ascending</option>
        </select>
     <?php
    }

    function sortByDocsMenuHTML() {
        $selectedOption = !esc_attr(get_option('sortByMenu')) ? "framework-title" : esc_attr(get_option('sortByMenu'));?>
        <select name="sortByMenu" id="sortByMenu" >
            <option value="document_name" <?php echo esc_attr($selectedOption) === "title" ? "selected" : ""; ?>>Document Title – ascending</option>
            <option value="post_mime_type" <?php echo esc_attr($selectedOption) === "post_mime_type" ? "selected" : ""; ?>>File Type – ascending</option>
            <option value="post_date" <?php echo esc_attr($selectedOption) === "post_date" ? "selected" : ""; ?>>Date Uploaded – descending</option>
            <option value="title" <?php echo esc_attr($selectedOption) === "title" ? "selected" : ""; ?>>Associated Framework – ascending</option>
            <option value="display_name" <?php echo esc_attr($selectedOption) === "display_name" ? "selected" : ""; ?>>Author – ascending</option>
        </select>
     <?php
    }

    /**
     *  AUTHORS PAGE HTML
     */

    function authorsReportsPage() {
        $json = file_get_contents($this->authorsAPI);
        $authors = json_decode($json, TRUE);
        echo $this->authorsPageHTML($authors);
    }

    function authorsPageHTML($authors) {
        $sortedAuthors = sortAuthors($authors);
        ob_start();
        ?>
        <div>
        <h1>Frameworks Authors Report</h1>
        <!-- <div>
            <span><b>Author Name:</b> author name per each published framework</span><br>
            <span><b>Last Accessed Wordpress:</b> last login date of this author</span><br>
            <span><b>Last Updated Framework:</b> title of the most recently updated framework by this author</span><br>
            <span><b>Last Update Date:</b> date of this update</span><br><br>
        </div> -->
        <form action="options.php" method="POST">
            <?php
                settings_errors();
                do_settings_sections('authors-reports');
                settings_fields('authorsSettings');
                submit_button();
            ?>
        </form>
        <table class="reports-table">
            <tr>
                <th>Author Name</th>
                <th>Last Accessed Wordpress</th>
                <th>Last Update Date</th>
                <th style="width: 30rem;">Last Updated Framework</th>
            </tr>
                <?php
                    foreach($sortedAuthors as $author) { 
                    // var_dump($author['authored_frameworks']);
                    usort($author['authored_frameworks'], 'compareDate');
                    // $permalink = $author['authored_frameworks'][0]['permalink'];
                    ?>
                     <tr>
                        <td><?php echo $author['author_name'] ?></td>
                        <td><?php echo $author['last_login'] ?></td>
                        <td><?php echo $author['authored_frameworks'][0]['post_modified'] ?></td>
                        <td styles="max-width: 20rem;"><?php echo $author['authored_frameworks'][0]['title'] ?></td>
                    </tr>
                <?php
                    }
                ?>
        </table>
        <?php
        return ob_get_clean();
    }


    /**
     *  FRAMEWORKS PAGE HTML
     */
    
    function frameworksReportsPage() {
        $json = file_get_contents($this->frameworksAPI);
        $frameworks = json_decode($json, TRUE);
        echo $this->frameworksPageHTML($frameworks);
    } 

    function frameworksPageHTML($frameworks) {
        $sortedFrameworks = sortFrameworks($frameworks);
        ob_start();
        // echo '$frameworks: ';
        //var_dump($frameworks);
        ?>
        <div>
        <h1>Frameworks Report</h1>
        <!-- <div>
            <span><b>Last Modified:</b>date of most recent modification of the framework post</span><br>
            <span><b>Modified By:</b> name of the person who made this modification </span><br>
            <span><b>Published Date:</b> date when the framework was published</span><br>
            <span><b>Framework Name:</b> framework name</span><br>
            <span><b>Framework RM No:</b> framework RM number</span><br>
            <span><b>Associated Lot Pages:</b> lot pages associated to this framework</span><br>
            <span><b>Linked Document:</b> details of document linked to this framework</span><br>
            <span><b>Doc Type:</b> document type</span><br><br>
        </div> -->
        <form action="options.php" method="POST">
            <?php
                settings_errors();
                do_settings_sections('wp-reports');
                settings_fields('frameworksSettings');
                submit_button();
            ?>
        </form>
        <table class="reports-table">
            <tr>
                <th style="width: 5rem;">Last Modified</th>
                <th style="width: 7rem;">Modified By</th>
                <th style="width: 5rem;">Last Published Date</th>
                <th style="width: 12rem;">Framework Title</th>
                <th>Framework RM Number</th>
                <!-- <th>Status</th> -->
                <th style="width: 18rem;">Associated Lot Pages</th>
                <th style="width: 7rem;">Linked Document</th>
                <th style="width: 4rem;">Document Type</th>
                <!-- <th>Linked Documents</th> -->
            </tr>
        <?php
        foreach ($sortedFrameworks as $framework) { 
            ?>
            <tr> 
                <td valign="top"><?php echo $framework['post_modified']?></td>
                <td valign="top"><?php echo $framework['post_author']?></td>
                <td valign="top"><?php echo $framework['last_published']?></td>
                <td valign="top"><?php echo $framework['title']?></td>
                <td valign="top"><?php echo $framework['rm_number'] ?></td>
                <!-- <td valign="top"><?php echo $framework['post_status']?></td> -->
                <td valign="top" >
                    <?php 
                        if (count($framework['associated_lots']) > 0) {
                            foreach($framework['associated_lots'] as $lot_index => $lot) {
                                echo "Lot ID: ".$lot['lot_id']; ?>
                                <br>
                                <?php
                                echo "Lot Title: ".$lot['lot_title']; ?>
                                <br>
                                <?php
                                if ($lot_index < (sizeof($framework['associated_lots'])) - 1 ) {
                                    echo "<hr>";
                                }
                            }
                        }
                        else {
                            echo "N/A";
                        }
                    ?>
                </td>
                <td style="width: 8rem;" valign="top"><?php echo $framework['doc_name'] ?></td>
                <td style="width: 8rem;" valign="top"><?php echo $framework['doc_type'] ?></td>
            </tr>
        <?php 
        } ?>
        </table>
        </div>
        <?php 
        return ob_get_clean();
    }
    

    /**
     *  DOCUMENTS PAGE HTML
     */

    function documentsReportsPage() {
        $restURL = $this->documentsAPI;
        $json = file_get_contents($restURL);
        $documents = json_decode($json, TRUE);
        echo $this->frameworksDocsHTML($documents);
        ?>
    <?php
    } 

   
    function frameworksDocsHTML($documents) { 
        $sortedDocuments = sortDocuments($documents);
        ob_start(); ?>
        <div>
        <h1>Frameworks Documents Report</h1>
        <!-- <span><b>Document Title:</b> document title</span><br>
        <span><b>File Type:</b> file type of this document </span><br>
        <span><b>Date of Upload:</b> upload date of this document </span><br>
        <span><b>Associated Framework Title:</b> framework title this document is linked to</span><br>
        <span><b>Author:</b> author of this document attachment</span><br><br> -->
        <form action="options.php" method="POST">
            <?php
                settings_errors();
                do_settings_sections('documents-reports');
                settings_fields('documentsSettings');
                submit_button();
            ?>
        </form>
        <table class="reports-table">
            <tr>
                <th style="max-width: 15rem;">Document Title</th>
                <th style="max-width: 15rem;">File Type</th>
                <th style="width: 5rem;">Date of Upload</th>
                <th style="max-width: 18rem;" >Associated Framework Title</th>
                <th style="width: 12rem;">Author</th>
                <!-- <th>Associated Pages</th> -->
            </tr>
            <?php 
            foreach($sortedDocuments as $doc) {
                // $permalink = "/news/frameworks/".$doc['post_name'];
                $fileType = $doc['post_mime_type'] == '' ? "N/A" : $doc['post_mime_type'];
                ?>
                <tr>
                    <td><?php echo $doc['document_name'] ?></td>
                    <td><?php echo $fileType ?></td>
                    <td><?php echo $doc['post_date'] ?></td>
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
    
    function downloadAuthorsReport() {
        $json = file_get_contents($this->authorsAPI);
        $authors = json_decode($json, TRUE);
        
        $selectedOptions = array_keys($_POST);
        if (($key = array_search('action', $selectedOptions)) !== false) {
            unset($selectedOptions[$key]);
        }
        $csv = __DIR__ . "/authors.csv";
        $file_pointer = fopen($csv, 'w');
        fputcsv($file_pointer, $selectedOptions);

        for($i = 0; $i < count($authors); $i++) {
            $line = $authors[$i];
            $last_updated_framework = $line['authored_frameworks'][0];
            $firstLineValues = array();
            for ($j = 0; $j < count($selectedOptions); $j++) {
                $current_key = $selectedOptions[$j];
                if(array_key_exists($current_key, $line)) {
                    array_push(
                    $firstLineValues, $line[$current_key]);
                }
                else if(array_key_exists($current_key, $last_updated_framework)){
                    array_push(
                        $firstLineValues, $last_updated_framework[$current_key]
                    );
                }
            }
            fputcsv($file_pointer, $firstLineValues);
        }
        fclose($file_pointer);
        $_POST = array();
    }
    /**
     * * DOWNLOAD PAGE HTML
    */
    function downloadReportsPage() {
        $selectedOption = !esc_attr(get_option('sortByMenu_3')) ? "author_name" : esc_attr(get_option('sortByMenu_3'));?>
        <div>
            <!-- <form action="options.php" method="POST"> -->
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <div class='documents-wrap'>
                <label for="author_name"> Author Name </label>
                <input type="checkbox" id="author_name" name="author_name" value="author_name" <?php $selectedOption === "author_name" ? "selected" : ""; ?>>
                <label for="last_login"> Last Accessed Wordpress </label>
                <input type="checkbox" id="last_login" name="last_login" value="last_login" <?php $selectedOption === "last_login" ? "selected" : ""; ?>>
                <label for="post_modified"> Last Updated Date </label>
                <input type="checkbox" id="post_modified" name="post_modified" value="post_modified" <?php $selectedOption === "post_modified" ? "selected" : ""; ?>>
                <label for="title"> Last Updated Framework </label>
                <input type="checkbox" id="title" name="title" value="title" <?php $selectedOption === "title" ? "selected" : ""; ?>>
                <input type="hidden" name="action" value="authors_form">
                <button>Download Autors CSV</button>
            </div>
        </form>
        <?php $selectedOption = !esc_attr(get_option('sortByMenu_2')) ? "post_modified" : esc_attr(get_option('sortByMenu_2'));
        //var_dump($selectedOption); ?>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
        <div class='documents-wrap'>
            <label for="post_modified"> Last Modified </label><input type="checkbox" id="post_modified" name="post_modified" value="post_modified">
            <label for="post_author"> Modified By </label><input type="checkbox" id="post_author" name="post_author" value="post_author">
            <label for="last_published"> Last Updated Date </label><input type="checkbox" id="last_published" name="last_published" value="last_published">
            <label for="title"> Framework Title </label><input type="checkbox" id="title" name="title" value="title">
            <label for="rm_number"> RM Number </label><input type="checkbox" id="rm_number" name="rm_number" value="rm_number">
            <label for="doc_name"> Linked Document </label><input type="checkbox" id="doc_name" name="doc_name" value="doc_name">
            <label for="doc_type"> Document Type </label><input type="checkbox" id="doc_type" name="doc_type" value="doc_type">
            <input type="hidden" name="action" value="frameworks_form">
            <button>Download Frameworks CSV</button>
        </div>
        </form>
        <?php $selectedOption = !esc_attr(get_option('sortByMenu')) ? "framework-title" : esc_attr(get_option('sortByMenu'));
        // var_dump($selectedOption); ?>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
        <div class='documents-wrap'>
            <label for="document_name"> Document Title </label><input type="checkbox" id="document_name" name="document_name" value="document_name">
            <label for="post_mime_type"> File Type </label><input type="checkbox" id="post_mime_type" name="post_mime_type" value="post_mime_type">
            <label for="post_date"> Date Uploaded </label><input type="checkbox" id="post_date" name="post_date" value="post_date">
            <label for="title"> Associated Framework </label><input type="checkbox" id="title" name="title" value="title">
            <!-- <label for="display_name"> Author </label><input type="checkbox" id="display_name" name="display_name" value="display_name"> -->
            <input type="hidden" name="action" value="documents_form">
            <button>Download Documents CSV</button>
        </div>
        </form>
        <?php
    }

}
/**
 * SORTING HELPER METHODS
 */


function compareDate($a, $b) {
    return strcmp($a['post_modified'],$b['post_modified']);
}

// SORT AUTHORS

function sortAuthors($dataArray) {
    // var_dump($dataArray);
    usort($dataArray, 'compareAuthorsSubarrays');
    return $dataArray;
}

function compareAuthorsSubarrays($a, $b) {
    $option = esc_attr(get_option('sortByMenu_3')) ? esc_attr(get_option('sortByMenu_3')) : 'author_name';

    if($option === "last_login") {
        return strcmp($b[$option], $a[$option]);     
    }
    if($option === 'post_modified') {
        return strcmp($b['authored_frameworks'][0][$option], $a['authored_frameworks'][0][$option]);
    }
    if($option === 'title') {
        return strcmp($a['authored_frameworks'][0][$option], $b['authored_frameworks'][0][$option]);
    }

    return strcmp($a[$option], $b[$option]); 
}

// SORT FRAMEWORKS

function sortFrameworks($dataArray) {
    // var_dump($dataArray);
    usort($dataArray, 'compareFrameworkSubarrays');
    return $dataArray;
}

function compareFrameworkSubarrays($a, $b) {
    $option = esc_attr(get_option('sortByMenu_2'));
    // var_dump($option);
    // if sorting by date, then sort descending, otherwise ascending
    if($option === "post_modified" || $option === "last_published") {
        return strcmp($b[$option], $a[$option]);     
    }
    return strcmp($a[$option], $b[$option]); 
}


// SORT DOCUMENTS

function sortDocuments($dataArray) {
    usort($dataArray, 'compareDocSubarrays');
    return $dataArray;
}

function compareDocSubarrays($a, $b) {
    // if sorting by date, then sort descending, otherwise ascending
    $option = esc_attr(get_option('sortByMenu')) ? esc_attr(get_option('sortByMenu')) : 'document_name';
    if($option === "post_date") {
        return strcmp($b[get_option('sortByMenu')], $a[get_option('sortByMenu')]);     
    }
    return strcmp($a[$option], $b[$option]); 
}




