<?php
/// MOODLE - IPC Portal users Login
require('../../../config.php');
require_once('./curl.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->libdir . "/coursecatlib.php");
require($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/roles/lib.php');

global $CFG, $DB;
header('Content-Type: text/plain');
$curl = new curlcall;
$ipctoken = required_param('token', PARAM_RAW);
// check token valid/invalid
$currenttime = date('Y-m-d H:i:s');
$currentime = strtotime($currenttime);
$checkurl = "$CFG->portalurl/api/v4.0/tokens?authtoken=$ipctoken:$currentime";
//$checkurl = "http://172.16.1.17/api/v4.0/tokens?authtoken=541d9414fb40e0:1458283052";
$curl->setHeader(array(
                'User-Agent: MoodleBot/1.0',
                'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                'Connection: keep-alive',
                'x-ipc-key: IMPELSYS@!'
                ));
$resp = $curl->get($checkurl);
$tokendetails = json_decode($resp);

$tokendata = $tokendetails->data;
$username = $tokendata[0]->username;
$useremail = $tokendata[0]->email;

if($tokendetails->token=='VALID') {
    
    if($tokendata[1]->accounttype=='INSTITUTION_COMMON') {
         print_error("Institution common users are not allowed");
         exit;
    }
        
    if (!$DB->record_exists('user', array('username' => $username))) { // create user
     
        $newuser = new stdClass;
        $newuser->username = $username;
        $newuser->password = 'Impelsys@2016';
        $newuser->email = $useremail;
        $newuser->email2 = $useremail;
        $newuser->firstname = $username;
        $newuser->lastname = 'surname';
        $newuser->city = $username;
        $newuser->country = 'IN';
        $newuser->confirmed = '1';
        $newuser->lang = 'en';
        $newuser->firstaccess = 0;
        $newuser->timecreated = time();
        $newuser->mnethostid = 1;
        $newuser->auth = 'manual';
        $plainpassword = $newuser->password;
        $newuser->password = hash_internal_user_password($newuser->password);
        if (empty($newuser->calendartype)) {
            $newuser->calendartype = $CFG->calendartype;
        }
        $newuser->id = user_create_user($newuser, false, false);
        user_add_password_history($newuser->id, $plainpassword);
        // Save any custom profile field information.
        profile_save_data($newuser);
        // Trigger event.
        \core\event\user_created::create_from_userid($newuser->id)->trigger();
        
    }
     
    // login user
    
        $username = $username;
        $password = 'Impelsys@2016'; 
        $user = authenticate_user_login($username, $password);
        if(!$user) {
            print_error('Invalid login');
            break;
        }
       
        $login = complete_user_login($user); 
       
    // create categories
    if($tokendata[1]->institutionname!='') {
        $data = new stdClass();
        $data->name = $tokendata[1]->institutionname;
        $data->visible = '1';
        $data->description = 'This is '.$tokendata[1]->institutionname.' hangouts from <a href='.$CFG->portalurl.' target=_blank>'.$CFG->portalsitename.'</a>';
        $data->idnumber = $tokendata[1]->institutionid;
        if (!$DB->record_exists('course_categories', array('name' => $data->name))) {
            $catrecords = coursecat::create($data);
        } else {
            $catrecords = $DB->get_record('course_categories', array('name' => $data->name));
        }
      
             
        // Enrolling Institutional user as student in Retail Category
         if($tokendata[1]->usertype=="ADMIN") {
            $roleid    = "1"; // institutional admin
            $context = context_coursecat::instance($catrecords->id);
            role_assign($roleid, $user->id, $context->id);
         } else if($tokendata[1]->usertype=="REGULAR") {
            $roleid    = "5"; // course creator role id 
        } 
        //$context = context_coursecat::instance($catrecords->id);
        //role_assign($roleid, $user->id, $context->id);
        
        // Creating sub categories

        // course sub category
        
            $subcategory = '';
            $idnumber = '';
            $idnumber = $tokendata[1]->institutionid.'_course';
            if (!$DB->record_exists('course_categories', array('idnumber' => $idnumber))) {
                $coursesubcategory = coursecat::create(array('name' => 'Course','visible'=>'1','description'=>'','idnumber'=>$idnumber, 'parent' => $catrecords->id));
            } else {
                $coursesubcategory = $DB->get_record('course_categories', array('idnumber' =>$idnumber));
            }
             
            $context = context_coursecat::instance($coursesubcategory->id);
            role_assign($roleid, $user->id, $context->id);
        
        // study group sub category
        
        $subcategory = '';
        $idnumber = '';
        $idnumber = $tokendata[1]->institutionid.'_studygroups';
        if (!$DB->record_exists('course_categories', array('idnumber' => $idnumber))) {
            $studysubcategory = coursecat::create(array('name' => 'Study Groups','visible'=>'1','description'=>'','idnumber'=>$idnumber, 'parent' => $catrecords->id));
        } else {
            $studysubcategory = $DB->get_record('course_categories', array('idnumber' =>$idnumber));
        }
        
        // assign role as course creator and not as manager
        $context = context_coursecat::instance($studysubcategory->id);
        role_assign('2', $user->id, $context->id);
        
        if($tokendata[1]->usertype=="ADMIN") {
            $url = "$CFG->wwwroot/course/index.php?categoryid=".$coursesubcategory->id;
        } else if($tokendata[1]->usertype=="REGULAR") {
            $url = "$CFG->wwwroot/course/management.php?categoryid=".$studysubcategory->id;
        } 
        
        
        redirect("$url");
        
    } else { // Retail user
        
        $data = new stdClass();
        $data->name = 'Retail';
        $data->visible = '0';
        $data->description = 'This is hangouts from <a href='.$CFG->portalurl.' target=_blank>'.$CFG->portalsitename.'</a>';
        $data->idnumber = 'retail_institution';
        if (!$DB->record_exists('course_categories', array('idnumber' => 'retail_institution'))) {
            $catrecords = coursecat::create($data);
        } else {
            $catrecords = $DB->get_record('course_categories', array('idnumber' =>'retail_institution'));
        }
        $context = context_coursecat::instance($catrecords->id);
        // Enrolling retail user as student in Retail Category
        $contextid = $context->id;
        $roleid    = "5"; // student role
        role_assign($roleid, $user->id, $context->id);
        
        
        // Creating sub categories
        
        //if (!$DB->record_exists('course_categories', array('idnumber' => 'course_retail'))) {
        //    $subcategory = coursecat::create(array('name' => 'Course Retail','visible'=>'0','description'=>'','idnumber'=>'course_retail', 'parent' => $catrecords->id));
        //} else {
        //    $subcategory = $DB->get_record('course_categories', array('idnumber' =>'course_retail'));
        //}
        //
        //if (!$DB->record_exists('course_categories', array('idnumber' => 'studygroups_retail'))) {
        //    $subcategory = coursecat::create(array('name' => 'Study groups Retail','visible'=>'0','description'=>'','idnumber'=>'studygroups_retail', 'parent' => $catrecords->id));
        //} else {
        //    $subcategory = $DB->get_record('course_categories', array('idnumber' =>'course_retail'));
        //}
        
        
       redirect("$CFG->wwwroot/course/index.php?categoryid=$catrecords->id");
    }
    
    redirect($CFG->wwwroot);

} else {
    print_error("Invalid IPC token");
}
exit;  

