<?php
if ( ! defined('ABSPATH')) exit; // exit if accessed directly

class Util {

    function __construct() {

        $this->authorsAPI = 'https://' . getenv('WP_SITEURL') . '/wp-json/wp-reports-plugin/v2/authors';
        $this->frameworksAPI = 'https://' . getenv('WP_SITEURL') . '/wp-json/wp-reports-plugin/v2/frameworks';
        $this->documentsAPI = 'https://' . getenv('WP_SITEURL') . '/wp-json/wp-reports-plugin/v2/documents/type=frameworks';
        $this->authorsLabelMap = array(
            "author_name" => "Author Name",
            "last_login" => "Last Accessed Wordpress",
            "post_modified" => "Last Update Date",
            "title" => "Last Updated Framework"
        );
        
        $this->frameworksLabelMap = array(
            "post_modified" => "Last Modified",
            "post_author" => "Modified By",
            "last_published" => "Last Update Date",
            "title" => "Framework Title",
            "rm_number" => "RM Number",
            "doc_name" => "Linked Document",
            "doc_type" => "Document Type"
        );
        
        $this->documentsLabelMap = array(
            "document_name" => "Document Title",
            "post_mime_type" => "File Type",
            "post_date" => "Date Uploaded",
            "title" => "Associated Framework"
        );
    }


    // DOWNLOAD AUTHORS

  
    function downloadAuthorsReport() {

        $json = file_get_contents($this->authorsAPI);
        $authors = json_decode($json, TRUE);
        
        $selectedOptions = array_keys($_POST);
        
        if (($key = array_search('action', $selectedOptions)) !== false) {
            unset($selectedOptions[$key]);
        }

        $csvLables = $this->mapToLabels($selectedOptions, "authors");
        
        $csv = __DIR__ . "/authors-report.csv";

        $file_pointer = fopen($csv, 'w');
        fputcsv($file_pointer, $csvLables);
        //fputcsv($file_pointer, $selectedOptions);

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

        if (file_exists($csv)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($csv));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($csv));
            ob_clean();
            flush();
            readfile($csv);
            unlink($csv);
            exit;
        }
        else {
            echo "<p> Could not download the file </p>";
        }
    }


    // DOWNLOAD FRAMEWORKS

    function downloadFrameworksReport() {

        $json = file_get_contents($this->frameworksAPI);
        $frameworks = json_decode($json, TRUE);

        $selectedOptions = array_keys($_POST);
        if (($key = array_search('action', $selectedOptions)) !== false) {
            unset($selectedOptions[$key]);
        }

        $csvLables = $this->mapToLabels($selectedOptions, "frameworks");

        $csv = __DIR__ . "/frameworks-report.csv";
        $file_pointer = fopen($csv, 'w');
        fputcsv($file_pointer, $csvLables);

        for($i = 0; $i < count($frameworks); $i++) {
            $line = $frameworks[$i];
            $frameworkKeys = false;
            $frameworks_lots = $line['associated_lots'];
            $firstLineValues = array();

            for ($j = 0; $j < count($selectedOptions); $j++) {
                $current_key = $selectedOptions[$j];
                if(array_key_exists($current_key, $line)) {
                    array_push(
                        $firstLineValues, $line[$current_key]
                    );
                }
                else if (array_key_exists($current_key, $frameworks_lots)) {
                    array_push(
                        $firstLineValues, $frameworks_lots[$current_key]
                    );
                }
            }
            fputcsv($file_pointer, $firstLineValues);
        }
        fclose($file_pointer);
        $_POST = array();
        
        if (file_exists($csv)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($csv));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($csv));
            ob_clean();
            flush();
            readfile($csv);
            unlink($csv);
            exit;
        }
        else {
            echo "<p> Could not download the file </p>";
        }
    }

    // DOWNLOAD DOCUMENTS

    function downloadDocumentsReport() {

        $json = file_get_contents($this->documentsAPI);
        $documents = json_decode($json, TRUE);

        $selectedOptions = array_keys($_POST);
        if (($key = array_search('action', $selectedOptions)) !== false) {
            unset($selectedOptions[$key]);
        }
        
        $csvLabels = $this->mapToLabels($selectedOptions, "documents");

        $csv = __DIR__ . "/documents-report.csv";
        $file_pointer = fopen($csv, 'w');
        fputcsv($file_pointer, $csvLabels);

        for($i = 0; $i < count($documents); $i++) {
            $line = $documents[$i];
            $frameworkKeys = false;
            $firstLineValues = array();

            for ($j = 0; $j < count($selectedOptions); $j++) {
                $current_key = $selectedOptions[$j];
                if(array_key_exists($current_key, $line)) {
                    array_push(
                        $firstLineValues, $line[$current_key]
                    );
                }
            }
            fputcsv($file_pointer, $firstLineValues);
        }
        fclose($file_pointer);
        $_POST = array();

        if (file_exists($csv)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($csv));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($csv));
            ob_clean();
            flush();
            readfile($csv);
            unlink($csv);
            exit;
        }
        else {
            echo "<p> Could not download the file </p>";
        }
    }

    function mapToLabels($selectedOptions, $formType) {
        echo "Selected options:";
        var_dump($selectedOptions);
        switch($formType) {
            case "authors": 
                return array_values(array_intersect_key($this->authorsLabelMap, array_flip($selectedOptions)));
                break;
            case "frameworks":
                return array_values(array_intersect_key($this->frameworksLabelMap, array_flip($selectedOptions)));
                break;
            case "documents":
                return array_values(array_intersect_key($this->documentsLabelMap, array_flip($selectedOptions)));
                break;
            default:
                return $selectedOptions;
        }
    }
}
?>