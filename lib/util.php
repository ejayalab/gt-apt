<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function debug_enable(){
    global $CFG;
    $CFG->debug = true;
}

function print_debug($message, $var = null, $pre=true){
    global $CFG;    
//        if($this->debugDB){
//            
//            $debug = new stdClass();
//            $debug->message = $message . '
//                ' . print_r($var, true);
//            $this->DB->insert_record('pm_debug', $debug);
//        }
        
    if(!$CFG->debug){
        return false;
    }

    if(empty($message)){

        print 'No se ha pasado ningun mensaje en printDebug()';
    }

    if($pre){

        print '<pre>';
    }

    print '<b>' . $message . '</b><br/>';
    print_r($var);

    if($pre){

        print '</pre>';
    }
}

function print_error($error){
    global $CFG;
    $vars = array();
    $vars['error'] = $error;
    
    if(!include_once $CFG->dirroot . 'views/error.php'){
        
        print 'No se encontro la vista de error, error: ' . $error;
    }
    
    exit();
}
?>
