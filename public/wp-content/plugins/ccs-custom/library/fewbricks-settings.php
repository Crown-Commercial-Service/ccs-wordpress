<?php

add_filter('fewbricks/project_files_base_path', 'get_project_files_base_path');
function get_project_files_base_path() {
    // Path to where you placed the fewbricks-directory that originally resided
    // in the main fewbricks directory.
    // Include directory name, exclude trailing slash
    $directory = dirname(__FILE__);
    $directory = $directory . '/../../fewbricks_definitions';
    return $directory;
}
