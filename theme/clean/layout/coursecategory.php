<?php

$html = theme_clean_get_html_for_settings($OUTPUT, $PAGE);

// Set default (LTR) layout mark-up for a three column page.
$regionmainbox = 'span9';
$regionmain = 'span8 pull-right';
$sidepre = 'span4 desktop-first-column';
$sidepost = 'span3 pull-right';
// Reset layout mark-up for RTL languages.
if (right_to_left()) {
    $regionmainbox = 'span9 pull-right';
    $regionmain = 'span8';
    $sidepre = 'span4 pull-right';
    $sidepost = 'span3 desktop-first-column';
}

echo $OUTPUT->doctype() ?>
<html <?php // echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php  echo  $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

.showdetails {
    float: right;
    cursor: pointer;
}
.first .img-responsive {
    /*padding-bottom: 1%;*/
    padding:10px 10px 17px 5px;
}

</style>
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>
<?php
    if(!function_exists('limit_words')) {
        function limit_words ($text,$maxwords) {
                $split = preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
                array_unshift($split,"");
                //unset($split[0]);
                $truncated = '';
                $j=1;
                $k=0;
                $a=array();
                for ($i = 0; $i < count($split); $i += 2) {
                   $truncated .= $split[$i].$split[$i+1];
                    if($j <= $maxwords){
                        $a['first'][$k]= $truncated;
                        $truncated='';
                        $k++;
                        //$j=0;
                    }elseif($j > $maxwords){
                        $a['second'][$k]= $truncated;
                        $truncated='';
                        $k++;
                    }
                    $j++;
                }
            return($a);
        }
    }
?>
<header role="banner" class="navbar navbar-fixed-top<?php echo $html->navbarclass ?> moodle-has-zindex">
    <nav role="navigation" class="navbar-inner">
        <div class="container-fluid">
            <a class="brand" href="<?php echo $CFG->wwwroot;?>"><?php echo
                format_string($SITE->shortname, true, array('context' => context_course::instance(SITEID)));
                ?></a>
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <?php echo $OUTPUT->user_menu(); ?>
            <div class="nav-collapse collapse">
                <?php echo $OUTPUT->custom_menu(); ?>
                <ul class="nav pull-right">
                    <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
<div id="page" class="container-fluid">
    <?php echo $OUTPUT->full_header(); ?>
    <div id="page-content" class="row-fluid">
        <div id="region-main-box" class="<?php echo $regionmainbox; ?>">
            <div class="row-fluid">
                <section id="region-main" class="<?php echo $regionmain; ?>">
                    <?php
                    echo $OUTPUT->course_content_header();
                 global $DB;
                 $categoryid = optional_param('categoryid', 0, PARAM_INT); // Category id
                 $courses = $DB->get_records('course',
                    array('category' => "$categoryid"), 'id ASC');
                 //var_dump($courses);
                 $i=0;
                 //var_dump($courses);
                 foreach($courses as $course) {
                    
                    require_once($CFG->dirroot. '/course/renderer.php');
                    $course = new course_in_list($course);
                   //$summary = coursecat_helper::get_course_formatted_summary($course);
                    $objcourserender = new coursecat_helper();
                    $summary = $objcourserender->get_course_formatted_summary($course);
                    $newDom = new DOMDocument();
                    @$newDom->loadHTML($summary);
                    $tag = $newDom->getElementsByTagName('img');
                    foreach ($tag as $tag1) {
                      //  echo $tag1->getAttribute('src');
                    }
                    $summarycontent = preg_replace("/<img[^>]+\>/i", "", $summary); 
                    //$summarycontent = str_replace(' ','',$summarycontent);  ?>
                 <div role="main">
                  <span id="maincontent"></span>
                  <span></span>
                  <div class="course_category_tree clearfix ">
                    <div class="content">
                        <div class="courses category-browse category-browse-13">
                            <div class="coursebox clearfix odd first" id="viewdetails<?php echo $course->id; ?>" data-courseid="<?php echo $course->id; ?>" data-type="<?php echo $i; ?>" style="max-height: 247px;overflow: hidden;">
                                <div class="info">
                                    <h3 class="coursename">
                                        <a class="" href="http://moodle.ipublishcentral.com/course/view.php?id=<?php echo $course->id; ?>"><?php echo $course->fullname ?></a>
                                    </h3>
                                    <div class="moreinfo"></div>
                                </div>
                            <div class="content">
                                <div class="summary">
                                <div class="no-overflow"> <?php
                                    $sumry = limit_words($summary,1000);
                                    $first = implode('',$sumry['first']);
                                    if(isset($sumry['second'])) {
                                         $remaining = implode('',$sumry['second']);
                                    } else {
                                        $remaining = '';
                                    } ?>
                                    
                                    <div class="first">
                                        <?php echo $first; ?>
                                    </div>
                                    <!--<div class="collapse" id="viewdetails<?php // echo $course->id; ?>">
                                        <?php // echo $remaining; ?><br>
                                   </div>-->
                              <!--  <?php // if(!empty($remaining)) { ?>
                                        <a class="showdetails" data-toggle="collapse" data-target="#viewdetails<?php // echo $course->id; ?>"></a>
                                <?php // } ?>-->
                                </div>
                                </div>
                            </div>
                    </div>
                        <?php if($remaining!='') { ?>
                            <a class="showdetails" id="viewdetails<?php echo $course->id; ?>">See More</a>       
                        <?php } ?>
                     
                 </div>
                 
                 <?php $i++; }
                  $style = "display: none;";  ?>
                  
                 <?php if(empty($courses)) {
                    $style = "display: block;";
                  } ?>
                 <div class="courseindex"><div style="<?php echo $style; ?>"><?php  echo $OUTPUT->main_content(); ?></div></div>
                 <?php
                    echo $OUTPUT->course_content_footer();
                    ?>
                </section>
                <?php echo $OUTPUT->blocks('side-pre', $sidepre); ?>
            </div>
        </div>
        <?php echo $OUTPUT->blocks('side-post', $sidepost); ?>
    </div>

    <footer id="page-footer">
        <div id="course-footer"><?php echo $OUTPUT->course_footer(); ?></div>
        <p class="helplink"><?php echo $OUTPUT->page_doc_link(); ?></p>
        <?php
        echo $html->footnote;
        echo $OUTPUT->login_info();
        echo $OUTPUT->home_link();
        echo $OUTPUT->standard_footer_html();
        ?>
    </footer>

    <?php echo $OUTPUT->standard_end_of_body_html() ?>

</div>
<script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script type="text/javascript">
    $(document).ready(
	function () {
            $('.showdetails').click( function () {
            var id = $(this).attr('id');
            var idelement = document.getElementById(id);
            var idvalue = '#'.concat(id);
            if (idelement.style.maxHeight=="500px") {
             //   idelement.style.maxHeight = "247px"
                $( idvalue ).animate({
                maxHeight: "247px"
              }, 1000, function() {
                // Animation complete.
                seemore();
              });
                function seemore(){
                    var element = document.getElementsByClassName('showdetails');
                    element[0].innerHTML = 'See More';
                }
            } else {
                var idvalue = '#'.concat(id);
            $( idvalue ).animate({
                maxHeight: "500px"
              }, 1000, function() {
                // Animation complete.
              });
              this.innerHTML="See less";    
            }
               var text = $(this).attr('text');
            });
        });
</script>
</body>
</html>
 