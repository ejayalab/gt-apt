<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'gt-apt';
$CFG->dbuser    = 'gt-apt';
$CFG->dbpass    = '0l1lwpdaa';
$CFG->lang      = 'es';
$CFG->debug     = false;
$CFG->dirroot   = 'C:\\wamp\\www\\gt-apt\\';
$CFG->wwwroot   = 'http://localhost/gt-apt/';

require_once 'start.php';
?>
