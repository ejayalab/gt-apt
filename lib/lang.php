<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Lang{
    var $lang = null;
    
    function __construct() {
        global $CFG;
        
        if(!include_once ($CFG->dirroot . 'lang/' . $CFG->lang . '.php')){
            print_error('No se pudo incluir el archivo de idioma: ' . $CFG->lang);
        }
        
        $this->lang = $lang;
    }
    
    function get_string($label){
        
        if(empty($label)){
            
            print_debug('No se ha pasado el parametro label en get_string()');
            return false;
        }
        
        $lang = $this->lang;
        
        if(isset($lang) and is_array($lang) and isset($lang[$label]) and !empty($lang[$label])){
            
            return $lang[$label];
        } else {
            
            return "[[$label]]";
        }
    }
}

function get_string($label){
    
    $LANG = new Lang();
    return $LANG->get_string($label);
}
?>
