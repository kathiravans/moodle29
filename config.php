<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

if($_SERVER['SERVER_NAME']=='moodle2.ipublishcentral.com') {
    $CFG->dbname    = 'moodle290';
    //$CFG->theme    = 'clean';
    $CFG->wwwroot   = 'http://moodle2.ipublishcentral.com';
    $CFG->dataroot  = '/var/www/moodledata290';
} else if($_SERVER['SERVER_NAME']=='moodle.ipublishcentral.com'){
    $CFG->dbname    = 'moodle2901';
    $CFG->theme    = 'clean';
    $CFG->wwwroot   = 'http://moodle.ipublishcentral.com';
    $CFG->dataroot  = '/var/www/moodledata2901';
}


$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';

$CFG->dbuser    = 'root';
$CFG->dbpass    = 'root';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
);

//$CFG->wwwroot   = 'http://ipcmoodle.com';


$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
