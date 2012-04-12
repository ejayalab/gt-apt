<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
unset($includes);
$includes = array(
    'lib/lang.php',
    'lib/util.php',
);
unset($jsArrayIncludes);
global $jsArrayIncludes;
$jsArrayIncludes = array(
    'assets/js/script.js',
);
unset($cssArrayIncludes);
global $cssArrayIncludes;
$cssArrayIncludes = array(
    'assets/css/styles.css',
);
        
foreach ($includes as $pathfile) {
    
    if(!include_once($CFG->dirroot . $pathfile)){
        
        print_simple_message('No se pudo incluir el archivo: ' . $pathfile);
    }
}

function print_simple_message($message){
    
    print '<pre>
        ';
    print $message;
    
    print '</pre>';
}
?>
