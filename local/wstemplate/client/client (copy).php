<?php
/// MOODLE ADMINISTRATION SETUP STEPS
require('../../../config.php');

require_once('./curl.php');
header('Content-Type: text/plain');
$curl = new curl;
$ipctoken = required_param('token', PARAM_RAW);
var_dump($ipctoken);

// check token valid/invalid
$checkurl = "http://172.16.1.17/api/v4.0/tokens?moodletoken=$ipctoken";
$curl->setHeader(array(
                'User-Agent: MoodleBot/1.0',
                'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                'Connection: keep-alive',
                'x-ipc-key: IMPELSYS@!'
                ));
$resp = $curl->get($checkurl);
$tokendetails = json_decode($resp);

$tokendata = $tokendetails->data;

var_dump($tokendata[0]); exit;
$username = $tokendata[0]->username;
$useremail = $tokendata[0]->email;
if($tokendetails->token=='VALID') {
    
}

$token = 'aa7803c969f7d08dc2fd136a57dc7bf8';
$domainname = 'http://moodle2.ipublishcentral.com';
$functionname = 'core_user_create_users';
// REST RETURNED VALUES FORMAT
$restformat = 'xml'; 
//////// moodle_user_create_users ////////


/// REST CALL

$serverurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;


$restformat = ($restformat == 'json')?'&moodlewsrestformat=' . $restformat:'';
$user1 = array(
            'username' => 'usernametessdst1',
            'password' => 'Impelsys@2016',
            'idnumber' => 'idnumbertsest1',
            'firstname' => 'First Name User Test 1',
            'lastname' => 'Last Name User Test 1',
            'middlename' => 'Middle Name User Test 1',
            'lastnamephonetic' => 'eee',
            'firstnamephonetic' => 'eeee',
            'alternatename' => 'Alternate Name User Test 1',
            'email' => 'usertest1@example.com',
            'description' => 'This is a description for user 1',
            'city' => 'Perth',
            'country' => 'au'
            );
$users = array($user1);
$params = array('users' => $users);
$resp = $curl->post($serverurl . $restformat, $params);



$logindetails = array(
            'username' => 'lionel',
            'email' => 'werrw@dfsdf.com',
            );
$logindetail = array($logindetails);
$param = array('users' => $logindetail);
$functionname = 'core_user_login_auth';
$loginauthurl = $domainname . '/webservice/rest/server.php'. '?wstoken=' . $token . '&wsfunction='.$functionname;
$restformat = ($restformat == 'json')?'&moodlewsrestformat=' . $restformat:'';
$resp = $curl->post($loginauthurl . $restformat, $param);
$p = xml_parser_create();
xml_parse_into_struct($p, $resp, $vals, $index);
$secretkey = $vals[4]['value'];
$username = $vals[9]['value'];
redirect("$CFG->wwwroot/login/confirm.php?data=$secretkey/$username");

exit();

//core\session\manager::start();
//
//require('../../../config.php');
//require_once($CFG->dirroot.'/login/lib.php');
//$username = 'lionel';
//    $password = 'impelsys'; // dummy password
//    $frm = new stdClass;
//    $frm->username = $username;
//    $frm->password = $password;
//$user = authenticate_user_login($frm->username, $frm->password);
//$login = complete_user_login($user);
//print_r($SESSION);