<?php
// This conatins the enrolling functionality from ipc to moodle.

//require("../config.php");
//require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->libdir.'/authlib.php');
class portal_db {
    // function db_init($authplugin) {
    //    if ($authplugin->is_configured() === false) {
    //        throw new moodle_exception('auth_dbcantconnect', 'auth_db');
    //    }
    //
    //    // Connect to the external database (forcing new connection).
    //    $authdb = ADONewConnection($authplugin->config->type);
    //    if (!empty($authplugin->config->debugauthdb)) {
    //        $authdb->debug = true;
    //        ob_start(); //Start output buffer to allow later use of the page headers.
    //    }
    //    $authdb->Connect($authplugin->config->host, $authplugin->config->user, $authplugin->config->pass, $authplugin->config->name, true);
    //    $authdb->SetFetchMode(ADODB_FETCH_ASSOC);
    //    if (!empty($authplugin->config->setupsql)) {
    //        $authdb->Execute($authplugin->config->setupsql);
    //    }
    //
    //    return $authdb;
    //}
    
    
    function __construct($authplugin) {
        if ($authplugin->is_configured() === false) {
            throw new moodle_exception('auth_dbcantconnect', 'auth_db');
        }
    
        // Connect to the external database (forcing new connection).
        $this->_authdb = ADONewConnection($authplugin->config->type);
        if (!empty($authplugin->config->debugauthdb)) {
            $this->_authdb->debug = true;
            ob_start(); //Start output buffer to allow later use of the page headers.
        }
        $this->_authdb->Connect($authplugin->config->host, $authplugin->config->user, $authplugin->config->pass, $authplugin->config->name, true);
        $this->_authdb->SetFetchMode(ADODB_FETCH_ASSOC);
        if (!empty($authplugin->config->setupsql)) {
            $this->_authdb->Execute($authplugin->config->setupsql);
        }
    
        return $this->_authdb;
    }
    
    function get_logintoken($token) {
        $rs = $this->_authdb->Execute("SELECT * FROM iplat_moodle_token WHERE status='ACTIVE' AND token = '$token'");
        $fields = $rs->FetchRow();
        $rs->Close();
        return  $fields;
    }
    
    function update_logintoken($token) {
        $rs = $this->_authdb->Execute("UPDATE `iplat_moodle_token` SET  `status` =  'INACTIVE' WHERE  `iplat_moodle_token`.`token` ='$token';");
        $rs->Close();
        if($rs) {
            return true;
        }
        return false;
    }
    
    
    
}

?>