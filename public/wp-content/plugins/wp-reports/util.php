<?php
if ( ! defined('ABSPATH')) exit; // exit if accessed directly

class Util {

    function __construct() {

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
            "lot_title" => "Lot Title",
            "lot_id" => "Lot ID",
            "doc_name" => "Linked Document",
            "doc_type" => "Document Type"
        );
        
        $this->documentsLabelMap = array(
            "document_name" => "Document Title",
            "post_mime_type" => "File Type",
            "post_date" => "Date Uploaded",
            "title" => "Associated Framework",
            "author" => "Author"
        );
    }
  
    function downloadAuthorsReport() {

        $authors = authorsQuery();
        
        $labelAndFilter = $this->getLabelAndFilteringOption('authors');

        $csvLabels          = $labelAndFilter[0];
        $selectedOptions    = $labelAndFilter[1];
        
        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=authors-report.csv");
        ob_clean();
        $file_pointer = fopen('php://output', 'w');
        fputcsv($file_pointer, $csvLabels);
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

    }

    function downloadFrameworksReport() {

        $frameworks = frameworksQuery();

        $labelAndFilter = $this->getLabelAndFilteringOption('frameworks');

        $csvLabels          = $labelAndFilter[0];
        $selectedOptions    = $labelAndFilter[1];

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=frameworks-report.csv");
        ob_clean();
        $file_pointer = fopen('php://output', 'w');

        fputcsv($file_pointer, $csvLabels);

        for($i = 0; $i < count($frameworks); $i++) {
            $line = $frameworks[$i];
            $frameworkKeys = false;
            $frameworks_lots = array();
            // check if lots array is not empty
            if (count($line['associated_lots']) > 0) {
                $frameworks_lots = $line['associated_lots'][0];
            }
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
        
    }

    function downloadDocumentsReport() {

        $documents = documentsQuery('frameworks');

        $labelAndFilter = $this->getLabelAndFilteringOption('documents');

        $csvLabels          = $labelAndFilter[0];
        $selectedOptions    = $labelAndFilter[1];

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=documents-report.csv");
        ob_clean();
        $file_pointer = fopen('php://output', 'w');
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

    }

    function getLabelAndFilteringOption($type) {

        $selectedOptions = array_keys($_POST);
        if (($key = array_search('action', $selectedOptions)) !== false) {
            unset($selectedOptions[$key]);
        }
        
        switch($type) {
            case "authors": 
                $labelMap = $this->authorsLabelMap;
                break;
            case "frameworks":
                $labelMap = $this->frameworksLabelMap;
                break;
            case "documents":
                $labelMap = $this->documentsLabelMap;
                break;
            }
        
        if(empty($selectedOptions)){
            return array(
                array_values($labelMap),
                array_keys($labelMap)
            );
            // $csvLabels          = array_values($labelMap);
            // $selectedOptions    = array_keys($labelMap);
        }else{
            return array(
                array_values(array_intersect_key($labelMap, array_flip($selectedOptions))),
                $selectedOptions
            );
        }
    }
}
?>