<?php
/*
 ** Logging in from portal to moodle
 ** Date july 29,2015
 ** kathir
 */

require('../config.php');
require_once($CFG->dirroot.'/login/lib.php'); // portal kathir

$errormsg = '';
// Check for timed out sessions
if (!empty($SESSION->has_timed_out)) {
    $session_has_timed_out = true;
    unset($SESSION->has_timed_out);
} else {
    $session_has_timed_out = false;
}


$frm  = false;
$user = false;
$token = required_param('token', PARAM_TEXT); // Category id
$authsequence = get_enabled_auth_plugins(true); // auths, in sequence
foreach($authsequence as $authname) {
    $authplugin = get_auth_plugin($authname);
    $authplugin->loginpage_hook();
}
$authplugin->config->ipctoken = $token;

    require_once($CFG->dirroot.'/portal/imp_portal.php');
    $portalobj = new portal_db($authplugin);
    $tokendetails = $portalobj->get_logintoken($token);
    
     // check active token or not
     $tokentime = $tokendetails['date'];
     $currenttime = date('Y-m-d H:i:s');
     
     $timediff = strtotime($currenttime) - strtotime($tokentime);
    
     $diffminutes = $timediff/60;
     
     $diffminutes = intval($diffminutes);
     
     if($diffminutes>1000) { // invalid token
        return false;
     } else { // valid token
      
        $portalobj->update_logintoken($token); // token will be made as INACTIVE
        $username = $tokendetails['username'];
        $password = 'impelsys'; // dummy password
     }   
    $frm = new stdClass;
    $frm->username = $username;
    $frm->password = $password;
    
     // logging in to moodle
    
if ($frm and isset($frm->username)) {                             // Login WITH cookies

    $frm->username = trim(core_text::strtolower($frm->username));

    if (is_enabled_auth('none') ) {
        if ($frm->username !== clean_param($frm->username, PARAM_USERNAME)) {
            $errormsg = get_string('username').': '.get_string("invalidusername");
            $errorcode = 2;
            $user = null;
        }
    }

    if ($user) {
        //user already supplied by aut plugin prelogin hook
    } else if (($frm->username == 'guest') and empty($CFG->guestloginbutton)) {
        $user = false;    /// Can't log in as guest if guest button is disabled
        $frm = false;
    } else {
        if (empty($errormsg)) { 
            $user = authenticate_user_login($frm->username, $frm->password); 
        } 
    }
    if ($user) {
        // language setup
        if (isguestuser($user)) {
            // no predefined language for guests - use existing session or default site lang
            unset($user->lang);

        } else if (!empty($user->lang)) {
            // unset previous session language - use user preference instead
            unset($SESSION->lang);
        }      
    /// Let's get them all set up.
    complete_user_login($user);
    // portal changes starts kathir
    //$enrolments = new portal_enrolments();
    //$enrolments->enrol_users($user->id,$courseid,$roleid,$totalenrolmentdays);
    // portal changes ends    
        // sets the username cookie
        if (!empty($CFG->nolastloggedin)) {
            // do not store last logged in user in cookie
            // auth plugins can temporarily override this from loginpage_hook()
            // do not save $CFG->nolastloggedin in database!

        } else if (empty($CFG->rememberusername) or ($CFG->rememberusername == 2 and empty($frm->rememberusername))) {
            // no permanent cookies, delete old one if exists
            set_moodle_cookie('');

        } else {
            set_moodle_cookie($USER->username);
        }
        
        // Discard any errors before the last redirect.
        unset($SESSION->loginerrormsg);
        redirect($CFG->wwwroot);
    } else {
        echo 'false';
        exit();
    }
}

