<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'config.php';
debug_enable();

$db = new Database();
$db->debug_enable();
//$table = 'test';
//$fields_values = array(
//    'nombre' => 'Edison Javier',
//    'apellido' => 'Ayala Benavides',
//    'email' => 'ejayalab@bt.unal.edu.co',
//);

//$last_id = $db->exec_INSERTquery($table, $fields_values);
//print_debug('Record insertado con id: ' . $last_id);

//$records = $db->get_records('test');
//
//print_debug('records de la tabla test', $records);

$db->exec_UPDATEquery('test', 'id IN(3,4)', array('nombre' => '_Javibaku_'));
?>
