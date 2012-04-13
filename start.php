<?php

/*
 * Se encarga de cargar todos los recursos que se van a utilizar en la plataforma
 * incluye librerias de php, carga en la variable $CFG archivos de javascript y css.
 */
global $CFG;
unset($includes);
$includes = array(
    'lib/lang.php',
    'lib/util.php',
    'lib/db.php',
);
unset($CFG->jsArrayIncludes);
$CFG->jsArrayIncludes = array(
    'assets/js/script.js',
);
unset($CFG->cssArrayIncludes);
$CFG->cssArrayIncludes = array(
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
