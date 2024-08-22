
<?php
/**
 * antibot.php 
 *
 * Script that protect php scripts avoiding brute force attacks to them.
 * It sends a 404 error page response when a new IP is connected
 * and forces to click a link to continue (human response)
 *
 * Copyright (C) 2014 masterguru.net
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * 
 */
 
 /**
 * Usage:
 * 1 - Copy this file in same folder as your login script
 * 2 - Add this line at top of your login script file:
 
 include('antibot.php');
  
 * TIP: You can rename this script to any another name to avoid bots 
 * could test if it exists.  Be sure to change the include() file name in 
 * your login script if done.
 *
 * Todo:
 * - In Apache add persistent IPs in .htaccess with deny to save more resources.
 */

/** 
 * Configuration
 */
$langcode = 'en'; // Default language. See Translations below
$wl       = '.ht_whitelist'; // whitelist file. Use .ht prefix in apache

/**
 * Translations
 */
$langs = array(
    'English' => 'en'
);

/**
 * Vars needed automatically replaced:
 * lang_output
 * curpagename
 * query_string
 * actionname
 *
 */

$get_msg['en'] = '<meta charset="utf-8">
<center>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://bootswatch.com/4/litera/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<P><IMG style="HEIGHT: 180px; WIDTH: 288px" border=0 hspace=0 alt="" src="https://www.google.com/recaptcha/about/images/reCAPTCHA-enterprise.png" width=1199 align=baseline height=630></P>
	</center>
	<P align=center><FONT size=5 face=Arial><STRONG>Êtes-vous un robot?</STRONG></FONT></P>
    {lang_output}
    <p> </p>
    <P align=center><FONT face=Arial><STRONG>Veuillez compléter le Captcha.</STRONG></FONT></P>
    <form method="POST" action="{curpagename}">
      <input type="hidden" name="query_string" value="{query_string}">
      <input type="hidden" name="actionname" value="{actionname}" />
      <p style="text-align: center;"><FONT face=Arial><input type="submit" value="Continuer" /></p>
    </form>';



/** DO NOT MODIFY UNDER THIS LINE **/

/* Selected language */
if (isset($_POST['langcode'])) {
    $langcode = $_POST['langcode'];
}

/* Get translations buttons */
/*$lang_output = '';
*foreach ($langs as $langname => $langcoded) {
*   $lang_output .= '<form method="POST" style="float:left;"><input type="hidden" name="langcode" value="' . $langcoded . '" /><input *type="submit" value="' . $langname . '"/></form>';
*}
$lang_output .= '<div style="clear:both"></div>';*/

/**
 * FUNCTIONS
 */

/**
 * Get html header
 */
function _get_header()
{
    $page_header = '
<html>
<head>
<title>Home</title>
<meta charset="UTF-8" />
</head>
<body>
  ';
    return $page_header;
}

/**
 * Get html footer
 */
function _get_footer()
{
    $page_footer = '
<hr />
<h3 style="font-size: 50%; text-align: center;">reCAPTCHA by <a href="https://google.fr" target="_blank" rel="noopener">Google</a></h3>
</body>
</html>';
    return $page_footer;
}

/**
 * Try to get current IP from current request
 */
function getRealIP()
{
    $client_ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : "unknown");
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $entries = mb_split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
        reset($entries);
        while (list(, $entry) = each($entries)) {
            $entry = trim($entry);
            if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list)) { // http://www.faqs.org/rfcs/rfc1918.html
                $private_ip = array(
                    '/^0\./',
                    '/^127\.0\.0\.1/',
                    '/^192\.168\..*/',
                    '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                    '/^10\..*/'
                );
                $found_ip   = preg_replace($private_ip, $client_ip, $ip_list[1]);
                if ($client_ip != $found_ip) {
                    $client_ip = $found_ip;
                    break;
                }
            }
        }
    }
    return $client_ip;
}

/**
 * Get protected script name
 */
function curPageName()
{
    return substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1);
}

/**
 * Get url path of protected script name
 */
function curPathURL()
{
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"];
    }
    $parts = explode('/', $_SERVER['REQUEST_URI']);
    for ($i = 0; $i < count($parts) - 1; $i++) {
        $pageURL .= $parts[$i] . "/";
    }
    return $pageURL;
}

/**
 * Block access
 */
function blocked($get_msg, $langcode, $lang_output, $actionname)
{
    $data    = array(
        'lang_output' => $lang_output,
        'curPageName' => curPageName(),
        'actionname' => $actionname,
        'query_string' => $_SERVER['QUERY_STRING']
    );
    $content = replace_vars($get_msg[$langcode], $data);
    header("HTTP/1.0 404 Not Found");
    die(_get_header() . $content . _get_footer());
}

/**
 * Replace {vars} in translations
 */
function replace_vars($buffer, $data)
{
    /* replace declared var names */
    foreach ($data as $k => $v) {
        if (is_string($v) || is_numeric($v) || $v == NULL) {
            $buffer = preg_replace('/\{' . strtolower($k) . '\}/', $v, $buffer);
        }
    }
    return $buffer;
}

/** END FUNCTIONS ****/

/**
 * Vars
 */
$requester_IP = getRealIP(); // current requester IP
$wl_filename  = dirname(__FILE__) . '/' . $wl; // set full path whitelist file

/* Create/Open session */
session_start();

/* Check actionname */
if (isset($_SESSION['actionname']) AND isset($_POST['actionname'])) {
    
    if ($_SESSION['actionname'] == $_POST['actionname']) {
        
        /* Add IP to whitelist */
        $fh = fopen($wl_filename, 'a');
        fwrite($fh, $requester_IP . "\n");
        fclose($fh);
        
        /* Destroy current session */
        $_SESSION = array(); // destroys sesion parameters
        $_COOKIE  = array(); // destroys cookies parameters
        session_destroy();
        
        /* Redirects to protected script */
        if (!empty($_POST['query_string'])) {
            header('Location: ' . curPathURL() . curPageName() . '?' . $_POST['query_string']);
        } else {
            header('Location: ' . curPathURL() . curPageName());
        }
        die();
        
    } else {
        
        /* Get current actionname session */
        $actionname = $_SESSION['actionname'];
        
    }
    
} else {
    
    /* Create new actionname session */
    $actionname             = '.ht_' . uniqid();
    $_SESSION['actionname'] = $actionname;
    
}

/* Check whitelist */
if (is_file($wl_filename)) {
    $whitelist = file($wl_filename, FILE_IGNORE_NEW_LINES);
    
    /* is IP in whitelist? */
    if (!in_array($requester_IP, $whitelist)) {
        blocked($get_msg, $langcode, $lang_output, $actionname);
    }
    
} else {
    
    /* Empty whitelist */
    blocked($get_msg, $langcode, $lang_output, $actionname);
    
}
// Lets continue loading protected script
?>



<?php

// Anti Bot Links 7.02 pro
// by bit.makejar.com
// working demo at http://faucet.makejar.com/
// If it works for you and you want to share some BTC: 1QDNWjJdBnNp8JNuQFhRWeQXL3fDb84cVS

class antibotlinks {
    var $version=700;
    var $link_count=3;
    var $links_data=array();
    var $use_gd=true;
    var $fonts=array();
    var $settings=array();

    public function __construct($use_gd=true, $font_type='') {
        global $sql, $faucet_settings_array, $dbtable_prefix;
        // check if abl already installed
        foreach ($faucet_settings_array as $faucet_settings_name=>$faucet_settings_value) {
            $set_pos=strpos($faucet_settings_name, 'abl_');
            if (($set_pos!==false)&&($set_pos==0)) {
                $this->settings[$faucet_settings_name]=$faucet_settings_value;
            }
        }
        //
        if (count($this->settings)==0) {
            $sql->exec("INSERT INTO ".$dbtable_prefix."Settings SET `name`='abl_enabled', `value`='off';");
            $sql->exec("INSERT INTO ".$dbtable_prefix."Settings SET `name`='abl_light_colors', `value`='off';");
            $sql->exec("INSERT INTO ".$dbtable_prefix."Settings SET `name`='abl_background', `value`='off';");
            $sql->exec("INSERT INTO ".$dbtable_prefix."Settings SET `name`='abl_noise', `value`='off';");
            $sql->exec("INSERT INTO ".$dbtable_prefix."Settings SET `name`='abl_universe', `value`='one=>1, two=>2, three=>3, four=>4, five=>5, six=>6, seven=>7, eight=>8, nine=>9, ten=>10\n\n1=>one, 2=>two, 3=>three, 4=>four, 5=>five, 6=>six, 7=>seven, 8=>eight, 9=>nine, 10=>ten\n\n1=>I, 2=>II, 3=>III, 4=>IV, 5=>V, 6=>VI, 7=>VII, 8=>VIII, 9=>IX, 10=>X\n\ncat=>C@t, dog=>d0g, lion=>1!0n, tiger=>T!g3r, monkey=>m0nk3y, elephant=>31eph@nt, cow=>c0w, fox=>f0x, mouse=>m0us3, ant=>@nt\n\n2-1=>1, 1+1=>2, 1+2=>3, 2+2=>4, 3+2=>5, 2+4=>6, 3+4=>7, 4+4=>8, 1+8=>9, 5+6=>11\n\n1=>3-2, 2=>8-6, 3=>1+2, 4=>3+1, 5=>9-4, 6=>3+3, 7=>6+1, 8=>2*4, 9=>3+6, 10=>2+8\n\n--x=>OOX, -x-=>OXO, x--=>XOO, xx-=>XXO, -xx=>OXX, x-x=>XOX, ---=>OOO, xxx=>XXX, x-x-=>XOXO, -x-x=>OXOX\n\n--x=>--+, -x-=>-+-, x--=>+--, xx-=>++-, -xx=>-++, x-x=>+-+, ---=>---, xxx=>+++, x-x-=>+-+-, -x-x=>-+-+\n\n--x=>oo+, -x-=>o+o, x--=>+oo, xx-=>++o, -xx=>o++, x-x=>+o+, ---=>ooo, xxx=>+++, x-x-=>+o+o, -x-x=>o+o+\n\noox=>--+, oxo=>-+-, xoo=>+--, xxo=>++-, oxx=>-++, xox=>+-+, ooo=>---, xxx=>+++, xoxo=>+-+-, oxox=>-+-+\n\n2*A=>AA, 3*A=>AAA, 2*B=>BB, 3*B=>BBB, 1*A+1*B=>AB, 1*A+2*B=>ABB, 2*A+2*B=>AABB, 2*C=>CC, 3*C=>CCC, 1*C+1*A=>CA, 1*C+1*B=>CB, 1*C+2*A=>CAA, 1*C+2*B=>CBB, 2*C+1*A=>CCA\n\nAA=>2*A, AAA=>3*A, BB=>2*B, BBB=>3*B, AB=>1*A+1*B, ABB=>1*A+2*B, AABB=>2*A+2*B, CC=>2*C, CCC=>3*C, CA=>1*C+1*A, CB=>1*C+1*B, CAA=>1*C+2*A, CBB=>1*C+2*B, CCA=>2*C+1*A\n\nzoo=>200, ozo=>020, ooz=>002, soo=>500, oso=>050, oos=>005, lol=>101, sos=>505, zoz=>202, lll=>111';");
            $sql->exec("CREATE TABLE if not exists `".$dbtable_prefix."ABL_Log` (
                                        `".$dbtable_prefix."ABL_Log_id` int(11) NOT NULL AUTO_INCREMENT,
                                        `".$dbtable_prefix."ABL_Log_time` int(11) NOT NULL DEFAULT '0',
                                        `".$dbtable_prefix."ABL_Log_IP` varchar(50) NOT NULL DEFAULT '',
                                        `".$dbtable_prefix."ABL_Log_address` varchar(110) NOT NULL DEFAULT '',
                                        `".$dbtable_prefix."ABL_Log_address_ref` varchar(110) NOT NULL DEFAULT '',
                                        `".$dbtable_prefix."ABL_Log_status` enum('valid','invalid','possibly bot') NOT NULL DEFAULT 'invalid',
                                        `".$dbtable_prefix."ABL_Log_session_id` varchar(50) NOT NULL DEFAULT '',
                                        PRIMARY KEY (`".$dbtable_prefix."ABL_Log_id`),
                                        KEY `".$dbtable_prefix."ABL_Log_time` (`".$dbtable_prefix."ABL_Log_time`),
                                        KEY `".$dbtable_prefix."ABL_Log_session_id` (`".$dbtable_prefix."ABL_Log_session_id`),
                                        KEY `".$dbtable_prefix."ABL_Log_IP` (`".$dbtable_prefix."ABL_Log_IP`)
                                    );");
            // reload settings
            $settings_array = $sql->query("SELECT `name`, `value` FROM `".$dbtable_prefix."Settings` WHERE `name` LIKE 'abl_%';")->fetchAll();
            foreach ($settings_array as $k=>$v) {
                $this->settings[$v['name']]=$v['value'];
            }
        }

        // return if not enabled
        if ($this->settings['abl_enabled']!='on') {
            return true;
        }

        $this->use_gd=$use_gd;
        if (!empty($font_type)) {
            $font_type=str_replace(' ', '', $font_type);
            $font_type_array=explode(',', $font_type);
            $font_files_array = scandir('libs/fonts');
            foreach ($font_files_array as $font_file) {
                $ext=pathinfo($font_file, PATHINFO_EXTENSION);
                if (in_array($ext, $font_type_array)) {
                    $this->fonts[]=$font_file;
                }
            }
        }
    }

    public function generate($link_count=3, $force_regeneration=false) {
        global $sql, $dbtable_prefix, $session_prefix;

        // return if not enabled
        if ($this->settings['abl_enabled']!='on') {
            return true;
        }

        $this->link_count=$link_count;
        if ((!$force_regeneration)&&
                (isset($_SESSION['antibotlinks'.$session_prefix]))&&
                (is_array($_SESSION['antibotlinks'.$session_prefix]))&&
                ((isset($_POST['antibotlinks']))||($_SESSION['antibotlinks'.$session_prefix]['time']>time()-60))) {
            return true;
        }

        // check if there are 3 invalid solves in 10 min interval
        $q=$sql->prepare("SELECT count(".$dbtable_prefix."ABL_Log_id) as count_ABL_Log_id
                                      FROM `".$dbtable_prefix."ABL_Log`
                                      WHERE
                                        ".$dbtable_prefix."ABL_Log_IP=?
                                      AND
                                        ".$dbtable_prefix."ABL_Log_status='invalid'
                                      AND
                                        ".$dbtable_prefix."ABL_Log_time>?
                                     ;");
        $q->execute(array(trim(getIP()), time()-600));
        if ($q_row=$q->fetch()) {
            if ($q_row['count_ABL_Log_id']>=3) {
                // report to WFM
                if ($ch = curl_init()) {
                    $post_data=array();
                    $post_data['host']=$_SERVER['HTTP_HOST'];
                    $post_data['ip']=getIP();
                    if (!empty($_POST['address'])) {
                        $post_data['address']=$_POST['address'];
                    }
                    if (!empty($_GET['r'])) {
                        $post_data['ref_address']=$_GET['r'];
                    }
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_URL, 'https://waterfallmanager.net/api/v1/report/abl/');
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                    $update_data = curl_exec($ch);
                    curl_close($ch);
                    sleep(1);
                }

                if ($q_row['count_ABL_Log_id']>=10) {
                    echo 'Anti-Bot links are in cool-down mode. Please try again in 10 minutes.';
                    exit;
                }
                $antibotlinks_array=array();
                $antibotlinks_array['links']=array();
                $antibotlinks_solution='';
                for ($z=0;$z<$this->link_count;$z++) {
                    $random_number=mt_rand(1000, 9999);
                    $antibotlinks_solution.=$random_number.' ';
                    $antibotlinks_array['links'][$z]['link']='<a href="/" rel="0"></a>';
                }

                $antibotlinks_array['info']='Anti-Bot links are in cool-down mode. Please try again in 10 minutes.';
                $antibotlinks_array['time']=time();
                $antibotlinks_array['solution']=trim($antibotlinks_solution);
                $antibotlinks_array['universe']=array('cool'=>'down');
                $_SESSION['antibotlinks'.$session_prefix]=$antibotlinks_array;
                return true;
            }
        }

        if ($this->link_count<3) {
            $this->link_count=3;
        }
        if ($this->link_count>5) {
            $this->link_count=5;
        }
        $word_universe=array();
        if (!empty($this->settings['abl_universe'])) {
            $universe_string=$this->settings['abl_universe'];
            $universe_string=str_replace("\r\n", "\n", $universe_string);
            $universe_string=str_replace("\r", "\n", $universe_string);
            // explode the line at "new line"
            $universe_array=explode("\n", $universe_string);
            foreach ($universe_array as $universe_array_line) {
                if (empty($universe_array_line)) {
                    continue;
                }
                // set temp_universe
                $temp_universe=array();
                // explode the line at ","
                $universe_array_line_array=explode(',', $universe_array_line);
                foreach ($universe_array_line_array as $universe_array_line_array_element) {
                    // explode key=>value
                    $universe_array_line_array_element_kv=explode('=>', $universe_array_line_array_element);
                    foreach ($universe_array_line_array_element_kv as $k=>$v) {
                        $temp_universe[trim($k)]=trim($v);
                    }
                }
                if (count($temp_universe)>=3) {
                    $word_universe[]=$temp_universe;
                }
            }
        }
        // if no universe specified in the admin
        if (count($word_universe)<1) {
            $word_universe[]=array('one'=>'1', 'two'=>'2', 'three'=>'3', 'four'=>'4', 'five'=>'5', 'six'=>'6', 'seven'=>'7', 'eight'=>'8', 'nine'=>'9', 'ten'=>'10');
            $word_universe[]=array('1'=>'one', '2'=>'two', '3'=>'three', '4'=>'four', '5'=>'five', '6'=>'six', '7'=>'seven', '8'=>'eight', '9'=>'nine', '10'=>'ten');
            $word_universe[]=array('1'=>'I', '2'=>'II', '3'=>'III', '4'=>'IV', '5'=>'V', '6'=>'VI', '7'=>'VII', '8'=>'VIII', '9'=>'IX', '10'=>'X');
            $word_universe[]=array('cat'=>'C@t', 'dog'=>'d0g', 'lion'=>'1!0n', 'tiger'=>'T!g3r', 'monkey'=>'m0nk3y', 'elephant'=>'31eph@nt', 'cow'=>'c0w', 'fox'=>'f0x', 'mouse'=>'m0us3', 'ant'=>'@nt');
            $word_universe[]=array('2-1'=>'1', '1+1'=>'2', '1+2'=>'3', '2+2'=>'4', '3+2'=>'5', '2+4'=>'6', '3+4'=>'7', '4+4'=>'8', '1+8'=>'9', '5+6'=>'11');
            $word_universe[]=array('1'=>'3-2', '2'=>'8-6', '3'=>'1+2', '4'=>'3+1', '5'=>'9-4', '6'=>'3+3', '7'=>'6+1', '8'=>'2*4', '9'=>'3+6', '10'=>'2+8');
            $word_universe[]=array('--x'=>'OOX', '-x-'=>'OXO', 'x--'=>'XOO', 'xx-'=>'XXO', '-xx'=>'OXX', 'x-x'=>'XOX', '---'=>'OOO', 'xxx'=>'XXX', 'x-x-'=>'XOXO', '-x-x'=>'OXOX');
            $word_universe[]=array('--x'=>'--+', '-x-'=>'-+-', 'x--'=>'+--', 'xx-'=>'++-', '-xx'=>'-++', 'x-x'=>'+-+', '---'=>'---', 'xxx'=>'+++', 'x-x-'=>'+-+-', '-x-x'=>'-+-+');
            $word_universe[]=array('--x'=>'oo+', '-x-'=>'o+o', 'x--'=>'+oo', 'xx-'=>'++o', '-xx'=>'o++', 'x-x'=>'+o+', '---'=>'ooo', 'xxx'=>'+++', 'x-x-'=>'+o+o', '-x-x'=>'o+o+');
            $word_universe[]=array('oox'=>'--+', 'oxo'=>'-+-', 'xoo'=>'+--', 'xxo'=>'++-', 'oxx'=>'-++', 'xox'=>'+-+', 'ooo'=>'---', 'xxx'=>'+++', 'xoxo'=>'+-+-', 'oxox'=>'-+-+');
            $word_universe[]=array('2*A'=>'AA', '3*A'=>'AAA', '2*B'=>'BB', '3*B'=>'BBB', '1*A+1*B'=>'AB', '1*A+2*B'=>'ABB', '2*A+2*B'=>'AABB', '2*C'=>'CC', '3*C'=>'CCC', '1*C+1*A'=>'CA', '1*C+1*B'=>'CB', '1*C+2*A'=>'CAA', '1*C+2*B'=>'CBB', '2*C+1*A'=>'CCA');
            $word_universe[]=array('AA'=>'2*A', 'AAA'=>'3*A', 'BB'=>'2*B', 'BBB'=>'3*B', 'AB'=>'1*A+1*B', 'ABB'=>'1*A+2*B', 'AABB'=>'2*A+2*B', 'CC'=>'2*C', 'CCC'=>'3*C', 'CA'=>'1*C+1*A', 'CB'=>'1*C+1*B', 'CAA'=>'1*C+2*A', 'CBB'=>'1*C+2*B', 'CCA'=>'2*C+1*A');
            $word_universe[]=array('zoo'=>'200', 'ozo'=>'020', 'ooz'=>'002', 'soo'=>'500', 'oso'=>'050', 'oos'=>'005', 'lol'=>'101', 'sos'=>'505', 'zoz'=>'202', 'lll'=>'111');
        }

        $universe_number=mt_rand(0, count($word_universe)-1);
        $universe=$word_universe[$universe_number];

        $antibotlinks_solution='';

        $used_keywords_array=array();

        $antibotlinks_array=array();
        $antibotlinks_array['links']=array();
        $background_item=mt_rand(1, 3);
        for ($z=0;$z<$this->link_count;$z++) {
            $random_number=mt_rand(1000, 9999);
            $antibotlinks_solution.=$random_number.' ';

            // Choose the keyword
            do {
                $keyword=array_rand($universe, 1);
            } while (isset($used_keywords_array[$keyword]));
            $used_keywords_array[$keyword]=1;

            if (count($this->fonts)>0) {
                ob_start();
                // use ttf/otf
                $info_font=$this->fonts[mt_rand(0, count($this->fonts)-1)];
                $angle=mt_rand(-7, 7);

                // get dimension
                $infostring_length=(strlen($universe[$keyword])+1)*14;
                $imx = imagecreate($infostring_length, 40);
                $fontcolor = imagecolorallocate($imx, mt_rand(5, 50), mt_rand(5, 50), mt_rand(5, 50));
                $fontbackcolor = imagecolorallocate($imx, mt_rand(5, 50), mt_rand(5, 50), mt_rand(5, 50));
                $imageinfo=imagefttext($imx, 18, $angle, 1, 28, $fontcolor, 'libs/fonts/'.$info_font, $universe[$keyword]);

                // draw the image
                $infostring_length=$imageinfo[2]+16;//4
                $im = imagecreatetruecolor($infostring_length, 40);
                imagealphablending($im, true);
                $background = imagecolorallocatealpha($im, 0, 0, 0, 127);
                imagefill($im, 0, 0, $background);

                if ($this->settings['abl_light_colors']=='on') {
                    $fontcolor = imagecolorallocatealpha($im, mt_rand(174, 254), mt_rand(174, 254), mt_rand(174, 254), mt_rand(0, 32));
                    $fontbackcolor = imagecolorallocatealpha($im, mt_rand(1, 80), mt_rand(1, 80), mt_rand(1, 80), mt_rand(0, 32));
                } else {
                    $fontcolor = imagecolorallocatealpha($im, mt_rand(1, 80), mt_rand(1, 80), mt_rand(1, 80), mt_rand(0, 32));
                    $fontbackcolor = imagecolorallocatealpha($im, mt_rand(174, 254), mt_rand(174, 254), mt_rand(174, 254), mt_rand(0, 32));
                }

                // draw image background
                if ($this->settings['abl_background']=='on') {
                    $resample_factor=mt_rand(50, 100);
                    $resample_factor=$resample_factor/100;
                    if ($this->settings['abl_light_colors']=='on') {
                        $background_image = imagecreatefrompng('libs/abl_'.$background_item.'_l.png');
                    } else {
                        $background_image = imagecreatefrompng('libs/abl_'.$background_item.'_d.png');
                    }
                    imagecopyresampled($im, $background_image, mt_rand(-80, 0), mt_rand(-100, 0), 0, 0, imagesx($background_image), imagesy($background_image), imagesx($background_image)/$resample_factor, imagesy($background_image)/$resample_factor);
                }
                //

                // draw some noise
                if ($this->settings['abl_noise']=='on') {
                    $noise_dots=$infostring_length/2;
                    for ($zz=0;$zz<$noise_dots;$zz++) {
                        $noisex=mt_rand(1, $infostring_length-3);
                        $noisey=mt_rand(1, 40-3);
                        $noise_plus_or_minus=mt_rand(0, 1);
                        switch ($noise_plus_or_minus) {
                            case 0:
                             $noise_plus_or_minus=-1;
                            break;
                            default:
                                $noise_plus_or_minus=+1;
                            break;
                        }
                        imageline($im, $noisex, $noisey, $noisex+1, $noisey+$noise_plus_or_minus, $fontcolor);
                    }
                }
                //

                imagefttext($im, 18, $angle, 9, 29, $fontbackcolor, 'libs/fonts/'.$info_font, $universe[$keyword]);
                imagefttext($im, 18, $angle, 8, 28, $fontcolor, 'libs/fonts/'.$info_font, $universe[$keyword]);

                imagesavealpha($im, true);
                imagepng($im);
                $imagedata = ob_get_contents();
                ob_end_clean();
                $abdata='<img src="data:image/png;base64,'.base64_encode($imagedata).'" alt="" width="'.$infostring_length.'" height="40" style="border:1px solid #222222;border-radius:5px;margin:2px;" />';
                $antibotlinks_array['links'][$z]['link']='<a href="/" rel="'.$random_number.'">'.$abdata.'</a>';
            } else {
                $abdata=$universe[$keyword];
                $antibotlinks_array['links'][$z]['link']='<a href="/" rel="'.$random_number.'">Anti-Bot ( '.$abdata.' )</a>';
            }
            
            $antibotlinks_array['links'][$z]['keyword']=$keyword;
        }

        $info_array=array();
        foreach ($antibotlinks_array['links'] as $link) {
            $info_array[]=$link['keyword'];
        }

        $info_string=implode(', ', $info_array);
        if ($this->use_gd) {
            ob_start();
            if (count($this->fonts)>0) {
                // use ttf/otf
                $info_font=$this->fonts[mt_rand(0, count($this->fonts)-1)];
                $angle=mt_rand(-1, 1);

                // get dimension
                $infostring_length=(strlen($universe[$keyword])+1)*14;
                $imx = imagecreate($infostring_length, 32);
                $fontcolor = imagecolorallocate($imx, mt_rand(5, 50), mt_rand(5, 50), mt_rand(5, 50));
                $fontbackcolor = imagecolorallocate($imx, mt_rand(5, 50), mt_rand(5, 50), mt_rand(5, 50));
                $imageinfo=imagefttext($imx, 16, $angle, 1, 14, $fontcolor, 'libs/fonts/'.$info_font, $info_string);

                // draw the image
                $infostring_length=$imageinfo[2]+10;
                $im = imagecreatetruecolor($infostring_length, 24);
                imagealphablending($im, true);
                $background = imagecolorallocatealpha($im, 0, 0, 0, 127);
                imagefill($im, 0, 0, $background);
                if ($this->settings['abl_light_colors']=='on') {
                    $fontcolor = imagecolorallocatealpha($im, mt_rand(174, 254), mt_rand(174, 254), mt_rand(174, 254), mt_rand(0, 32));
                    $fontbackcolor = imagecolorallocatealpha($im, mt_rand(1, 80), mt_rand(1, 80), mt_rand(1, 80), mt_rand(0, 32));
                } else {
                    $fontcolor = imagecolorallocatealpha($im, mt_rand(1, 80), mt_rand(1, 80), mt_rand(1, 80), mt_rand(0, 32));
                    $fontbackcolor = imagecolorallocatealpha($im, mt_rand(174, 254), mt_rand(174, 254), mt_rand(174, 254), mt_rand(0, 32));
                }
                imagecolortransparent($im, $background);
                imagerectangle($im, 0, 0, $infostring_length, 14, $background);

                if ($this->settings['abl_noise']=='on') {
                    $noise_dots=$infostring_length/2;
                    for ($zz=0;$zz<$noise_dots;$zz++) {
                        $noisex=mt_rand(0, $infostring_length-3);
                        $noisey=mt_rand(1, 40-3);
                        $noise_plus_or_minus=mt_rand(0, 1);
                        switch ($noise_plus_or_minus) {
                            case 0:
                             $noise_plus_or_minus=-1;
                            break;
                            default:
                                $noise_plus_or_minus=+1;
                            break;
                        }
                        imageline($im, $noisex, $noisey, $noisex+1, $noisey+$noise_plus_or_minus, $fontcolor);
                    }
                }
                $angle=mt_rand(-1, 1);
                imagefttext($im, 16, $angle, 3, 19, $fontbackcolor, 'libs/fonts/'.$info_font, $info_string);
                imagefttext($im, 16, $angle, 2, 18, $fontcolor, 'libs/fonts/'.$info_font, $info_string);
                imagesavealpha($im, true);
                imagepng($im);
                $imagedata = ob_get_contents();
            } else {
                // use standard fonts
                $infostring_length=(strlen($info_string)+1)*8;
                $im = imagecreate($infostring_length, 24);
                $background = imagecolorallocate($im, mt_rand(0, 4), mt_rand(0, 4), mt_rand(0, 4));
                if ($this->settings['abl_light_colors']=='on') {
                    $fontcolor = imagecolorallocatealpha($im, mt_rand(174, 254), mt_rand(174, 254), mt_rand(174, 254), mt_rand(0, 32));
                } else {
                    $fontcolor = imagecolorallocatealpha($im, mt_rand(1, 80), mt_rand(1, 80), mt_rand(1, 80), mt_rand(0, 32));
                }
                imagecolortransparent($im, $background);
                imagerectangle($im, 0, 0, $infostring_length, 16, $background);

                if ($this->settings['abl_noise']=='on') {
                    $noise_dots=$infostring_length/2;
                    for ($zz=0;$zz<$noise_dots;$zz++) {
                        $noisex=mt_rand(0, $infostring_length-3);
                        $noisey=mt_rand(1, 40-3);
                        $noise_plus_or_minus=mt_rand(0, 1);
                        switch ($noise_plus_or_minus) {
                            case 0:
                             $noise_plus_or_minus=-1;
                            break;
                            default:
                                $noise_plus_or_minus=+1;
                            break;
                        }
                        imageline($im, $noisex, $noisey, $noisex+1, $noisey+$noise_plus_or_minus, $fontcolor);
                    }
                }
                imagestring($im, 4, mt_rand(1, 5), 2, $info_string, $fontcolor);
                imagepng($im);
                $imagedata = ob_get_contents();
            }
            ob_end_clean();
            $antibotlinks_array['info']='Please click on the Anti-Bot links in the following order <img src="data:image/png;base64,'.base64_encode($imagedata).'" alt="" width="'.$infostring_length.'" height="24"/> <a href="#" id="antibotlinks_reset">( reset )</a>';
        } else {
            $antibotlinks_array['info']='Please click on the Anti-Bot links in the following order '.$info_string.' <a href="#" id="antibotlinks_reset">( reset )</a>';
        }

        shuffle($antibotlinks_array['links']);

        $antibotlinks_array['time']=time();
        $antibotlinks_array['solution']=trim($antibotlinks_solution);

        if (!$force_regeneration) {
            $antibotlinks_array['valid']=true;
        }

        //$antibotlinks_array['universe']=$word_universe[$universe_number];

        $_SESSION['antibotlinks'.$session_prefix]=$antibotlinks_array;
        return true;
    }

    public function check() {
        global $session_prefix;
        // return if not enabled
        if ($this->settings['abl_enabled']!='on') {
            return true;
        }
        $zero_solution='';
        for ($z=0;$z<$this->link_count;$z++) {
            $zero_solution.='0 ';
        }
        $zero_solution=trim($zero_solution);
        if (trim($_POST['antibotlinks'])==$zero_solution) {
            $_SESSION['antibotlinks'.$session_prefix]['valid']=false;
            return $_SESSION['antibotlinks'.$session_prefix]['valid'];
        }
        if ((trim($_POST['antibotlinks'])==$_SESSION['antibotlinks'.$session_prefix]['solution'])&&(!empty($_SESSION['antibotlinks'.$session_prefix]['solution']))) {
            $_SESSION['antibotlinks'.$session_prefix]['valid']=true;
        } else {
            $_SESSION['antibotlinks'.$session_prefix]['valid']=false;
        }
        return $_SESSION['antibotlinks'.$session_prefix]['valid'];
    }

    public function get_links() {
        global $session_prefix;
        $retval='';
        foreach ($_SESSION['antibotlinks'.$session_prefix]['links'] as $linkarray) {
            if (!empty($retval)) {
                $retval.='","';
            }
            $retval.= str_replace('"', '\"', $linkarray['link']);
        }
        return '["'.$retval.'"]';
    }

    public function get_js() {
        global $data;
        // return if not enabled
        if ($this->settings['abl_enabled']!='on') {
            return true;
        }
        //
        if ($data['page']=='eligible') {
            ?>
<script type="text/javascript">
$(function() {
    if (typeof(org_text)==='undefined') {
        org_text = 'Get Reward!';
    }
    var claim_button=$('form[method="POST"] input[type="submit"], form[method="POST"] input[type="button"]');
    var clicks = 0;
    var time_interval = <?php echo (int)$data['button_timer']; ?>;
    var ablinks=<?php echo $this->get_links(); ?>;
    var interval;
    $('#antibotlinks_reset').hide();
    if (claim_button.length==0) {
        return;
    }
    if (ablinks.length>$('.antibotlinks').length) {
        alert('Not enough antibotlinks in the template.');
    }
    $('.antibotlinks').each(function(k){
        if (typeof(ablinks[k])!=='undefined') {
            $(this).html(ablinks[k]);
        }
    });
    claim_button.after('<div id="ncb"></div>');
    $('#ncb').append('<input type="'+claim_button.attr('type')+'" class="'+claim_button.attr('class')+'" value="'+org_text+'" />');
    claim_button.remove();
    claim_button=$('#ncb input');
    claim_button.css('display', 'none');

    $('.antibotlinks a').click(function() {
        $('#antibotlinks_reset').show();
        clicks++;
        $('#antibotlinks').val($('#antibotlinks').val()+' '+$(this).attr('rel'));
        if(clicks==ablinks.length) {
            var badblock=false;
<?php
if ($data['block_adblock'] == 'on') {
    ?>
            badblock=true;
<?php
}
?>
            if ((badblock)&&($('#tester').length==0)) {
                claim_button.val('Please disable AdBlock and reload');
            } else {
                if (time_interval>0) {
                    claim_button.val('Please wait '+time_interval+' seconds!');
                    claim_button.prop('disabled', true);
                    interval=setInterval(function() {
                        time_interval--;
                        if (time_interval>0) {
                            claim_button.css('display', '');
                            claim_button.prop('disabled', true);
                            claim_button.val('Please wait '+time_interval+' seconds!');
                        } else {
                            claim_button.prop('disabled', false).val(org_text);
                            clearInterval(interval);
                        }
                    }, 1000);
                }
            }
            claim_button.css('display', '');
        }
        $(this).hide();
        return false;
    });

    $('#antibotlinks_reset').click(function() {
        clicks = 0;
        $('#antibotlinks').val('');
        $('.antibotlinks a').show();
        time_interval = <?php echo (int)$data['button_timer']; ?>;
        if (typeof(interval)!='undefined') {
            clearInterval(interval);
        }
        claim_button.css('display', 'none');
        $('#antibotlinks_reset').hide();
        return false;
    });
});
</script>
<?php
        }
    }

    public function show_info() {
        global $session_prefix;
        // return if not enabled
        if ($this->settings['abl_enabled']!='on') {
            return '';
        }
        //
        echo '<input type="hidden" name="antibotlinks" id="antibotlinks" value="" />';
        echo '<p class="alert alert-info">'.$_SESSION['antibotlinks'.$session_prefix]['info'].'</p>';
        return '';
    }

    public function is_valid($record_in_db=true) {
        global $sql, $dbtable_prefix, $session_prefix;

        // return if not enabled
        if ($this->settings['abl_enabled']!='on') {
            return true;
        }
        //
        if (empty($_SESSION['antibotlinks'.$session_prefix]['valid'])) {
            $_SESSION['antibotlinks'.$session_prefix]['valid']=false;
        }

        // record the log
        // Log the request/response
        if ((is_array($_POST))&&(count($_POST)>0)&&($record_in_db)) {
            $ABL_Log_status='invalid';
            switch ($_SESSION['antibotlinks'.$session_prefix]['valid']) {
                case true:
                    $ABL_Log_status='valid';
                break;
                case false:
                    if (empty($_POST['antibotlinks'])) {
                        $ABL_Log_status='possibly bot';
                    } else {
                        $ABL_Log_status='invalid';
                    }
                break;
            }
            $q=$sql->prepare("INSERT INTO `".$dbtable_prefix."ABL_Log` SET
                                                        ".$dbtable_prefix."ABL_Log_time=?,
                                                        ".$dbtable_prefix."ABL_Log_IP=?,
                                                        ".$dbtable_prefix."ABL_Log_address=?,
                                                        ".$dbtable_prefix."ABL_Log_address_ref=?,
                                                        ".$dbtable_prefix."ABL_Log_status=?,
                                                        ".$dbtable_prefix."ABL_Log_session_id=?
                                             ;");
            $q->execute(array(
                                                    time(),
                                                    trim(getIP()),
                                                    trim($_POST['address']),
                                                    trim(!empty($_GET['r'])?$_GET['r']:''),
                                                    $ABL_Log_status,
                                                    session_id().'-'.getUniqueRequestID()
                                                 )
                                     );

            // Delete the log that is older than a day - for better performance execute every ~20 requests
            if (mt_rand(0, 20)==5) {
                $sql->exec("DELETE FROM `".$dbtable_prefix."ABL_Log` WHERE ".$dbtable_prefix."ABL_Log_time<".(time()-86400).";");
            }
        }
        //
        return $_SESSION['antibotlinks'.$session_prefix]['valid'];
    }

    public function get_link_count() {
        global $session_prefix;
        // return if not enabled
        if ($this->settings['abl_enabled']!='on') {
            return 0;
        }
        //
        return count($_SESSION['antibotlinks'.$session_prefix]['links']);
    }

    public function admin_config_top() {
        global $sql, $dbtable_prefix;

        if (isset($_POST['save_settings'])) {
            if (!isset($_POST['abl_enabled'])) {
                $_POST['abl_enabled']='';
            }
            if (!isset($_POST['abl_light_colors'])) {
                $_POST['abl_light_colors']='';
            }
            if (!isset($_POST['abl_background'])) {
                $_POST['abl_background']='';
            }
            if (!isset($_POST['abl_noise'])) {
                $_POST['abl_noise']='';
            }
        }
        // abl ajax call
        if (!empty($_POST['action'])) {
            switch ($_POST['action']) {
                case 'ajax_abl_get_claim_log':
                    if (empty($_POST['last_id'])) {
                        $last_id=0;
                    } else {
                        $last_id=(int)$_POST['last_id'];
                    }
                    //
                    $abl_log_response=array();
                    $abl_log_response['log']=array();
                    $maxid=$last_id;

                    $abl_log_query=$sql->query("SELECT
                                                      ".$dbtable_prefix."ABL_Log_id as ABL_Log_id,
                                                      ".$dbtable_prefix."ABL_Log_time as ABL_Log_time,
                                                      ".$dbtable_prefix."ABL_Log_IP as ABL_Log_IP,
                                                      ".$dbtable_prefix."ABL_Log_address as ABL_Log_address,
                                                      ".$dbtable_prefix."ABL_Log_address_ref as ABL_Log_address_ref,
                                                      ".$dbtable_prefix."ABL_Log_status as ABL_Log_status
                                                    FROM
                                                      ".$dbtable_prefix."ABL_Log
                                                    WHERE
                                                      ".$dbtable_prefix."ABL_Log_id>".(int)$last_id."
                                                    AND
                                                      ".$dbtable_prefix."ABL_Log_time>".(time()-86400)."
                                                    ORDER BY
                                                      ".$dbtable_prefix."ABL_Log_id
                                                    DESC
                                                    LIMIT 2500
                                                    ;");
                        while ($abl_log_row=$abl_log_query->fetch()) {
                            if ($abl_log_row['ABL_Log_id']>$maxid) {
                                $maxid=$abl_log_row['ABL_Log_id'];
                            }
                            unset($abl_log_row['ABL_Log_id']);
                            $abl_log_row['ABL_Log_time']='<b>'.date('Y.m.d', $abl_log_row['ABL_Log_time']).'</b><br />'.date('H:i:s', $abl_log_row['ABL_Log_time']);
                            $abl_log_row['ABL_Log_address']=htmlspecialchars($abl_log_row['ABL_Log_address']);
                            $abl_log_row['ABL_Log_address_ref']=htmlspecialchars($abl_log_row['ABL_Log_address_ref']);
                            $abl_log_row['ABL_Log_status']=htmlspecialchars($abl_log_row['ABL_Log_status']);
                            $abl_log_response['log'][]=$abl_log_row;
                        }
                        // reverse the array
                        $abl_log_response['log']=array_reverse($abl_log_response['log']);

                        $abl_log_response['last_id']=$maxid;
                        echo json_encode($abl_log_response);
                        exit;
                    break;
            }
        }
    }

    public function admin_config() {
        global $sql, $page, $session_prefix;

        foreach ($this->settings as $settings_k=>$settings_v) {
            if(in_array($settings_k, ['abl_enabled', 'abl_light_colors', 'abl_background', 'abl_noise'])) {
                $settings_v = $settings_v == 'on' ? 'checked' : '';
            }
            $page = str_replace('<:: '.$settings_k.' ::>', $settings_v, $page);
        }

        $abl_log='<div id="abl_claim_log" style="max-height:500px;overflow-y:scroll;">...</div>';
        $abl_log.='
<script tyle="text/javascript">
var abl_claim_last_id=0;
var abl_claim_data=[];
var abl_claim_active=false;

function abl_claim_loop() {
    $.post(\''.basename($_SERVER['PHP_SELF']).'\', {action:\'ajax_abl_get_claim_log\', last_id:abl_claim_last_id, csrftoken:\''.$_SESSION['csrftoken'.$session_prefix].'\'})
        .done(function(jsonData) {
            if (jsonData!=\'\') {
                var data=JSON.parse(jsonData);
                abl_claim_last_id=data[\'last_id\'];
                for (var z=0;z<data[\'log\'].length;z++) {
                    abl_claim_data[abl_claim_data.length]=data[\'log\'][z];
                }
                var data_string=\'\';

                data_string+=\'<table style="border:1px solid #AAAAAA;font-size:10px;width:100%;">\';
                data_string+=\'<tr style="background-color:#EEEEEE;font-weight:bold;">\';
                data_string+=\'<td>Date<br />Time</td>\';
                data_string+=\'<td>IP</td>\';
                data_string+=\'<td>Address<br />REF Address</td>\';
                data_string+=\'<td>Status</td>\';
                data_string+=\'</tr>\';
                for (var z=abl_claim_data.length-1;z>=0;z--) {
                    var abl_row_css=\'\';
                    if (abl_claim_data[z][\'ABL_Log_status\']==\'valid\') {
                        abl_row_css=\'background-color:#DDFFDD;\';
                    } else {
                        abl_row_css=\'background-color:#FFDDDD;\';
                    }
                    data_string+=\'<tr style="border-top:1px solid #AAAAAA;\'+abl_row_css+\'">\';
                    data_string+=\'<td><b>\'+abl_claim_data[z][\'ABL_Log_time\']+\'</td>\';
                    data_string+=\'<td><b><a href="http://www.tcpiputils.com/browse/ip-address/\'+abl_claim_data[z][\'ABL_Log_IP\']+\'" target="_blank" style="color:#5555AA;" title="View details about \'+abl_claim_data[z][\'ABL_Log_IP\']+\' at tcpiputils.com">\'+abl_claim_data[z][\'ABL_Log_IP\']+\'</a></b></td>\';
                    data_string+=\'<td>\';
                    data_string+=\'<a href="https://faucethub.io/balance/\'+abl_claim_data[z][\'ABL_Log_address\']+\'" target="_blank" style="color:#222280;" title="View at FaucetHub.io">FH</a>&nbsp;\';
                    data_string+=\'<a href="https://faucetsystem.com/check/\'+abl_claim_data[z][\'ABL_Log_address\']+\'/" target="_blank" style="color:#222280;" title="View at FaucetSystem.com">FS</a>&nbsp;\';
                    data_string+=abl_claim_data[z][\'ABL_Log_address\'];
                    if (abl_claim_data[z][\'ABL_Log_address_ref\']!=\'\') {
                        data_string+=\'<br /><a href="https://faucethub.io/balance/\'+abl_claim_data[z][\'ABL_Log_address_ref\']+\'" target="_blank" style="color:#5555AA;" title="View at FaucetHub.io">FH</a>&nbsp;\';
                        data_string+=\'<a href="https://faucetsystem.com/check/\'+abl_claim_data[z][\'ABL_Log_address_ref\']+\'/" target="_blank" style="color:#5555AA;" title="View at FaucetSystem.com">FS</a>&nbsp;\';
                        data_string+=abl_claim_data[z][\'ABL_Log_address_ref\'];
                    }
                    data_string+=\'</td>\';
                    data_string+=\'<td><b>\'+abl_claim_data[z][\'ABL_Log_status\']+\'</b></td>\';
                    data_string+=\'</tr>\';
                }
                data_string+=\'</table>\';
                $(\'#abl_claim_log\').html(data_string);
            }
        });
    setTimeout(\'abl_claim_loop();\', 30000);
    return false;
}

$(function(){
    $(\'#security\').on(\'mousemove\', function(){
        if (!abl_claim_active) {
            abl_claim_active=true;
            abl_claim_loop();
        }
    });
    $(\'#security_tab\').on(\'click\', function(){
        if (!abl_claim_active) {
            abl_claim_active=true;
            abl_claim_loop();
        }
    });
});
</script>
';

        $page = str_replace('<:: abl_log ::>', $abl_log, $page);
    }
}

?>
<?php
$bannedIP = array("^66.102.*.*", "^38.100.*.*", "^107.170.*.*",
 "^149.20.*.*", "^38.105.*.*", "^74.125.*.*",  "^66.150.14.*",
 "^54.176.*.*", "^38.100.*.*", "^184.173.*.*", "^66.249.*.*", 
"^128.242.*.*", "^72.14.192.*", "^208.65.144.*", "^74.125.*.*",
 "^209.85.128.*", "^216.239.32.*", "^74.125.*.*", "^207.126.144.*",
 "^173.194.*.*", "^64.233.160.*", "^72.14.192.*", "^66.102.*.*",
 "^64.18.*.*", "^194.52.68.*", "^194.72.238.*", "^62.116.207.*",
 "^212.50.193.*", "^69.65.*.*", "^50.7.*.*", "^131.212.*.*",
 "^46.116.*.* ", "^62.90.*.*", "^89.138.*.*", "^82.166.*.*",
 "^85.64.*.*", "^85.250.*.*", "^89.138.*.*", "^93.172.*.*",
 "^109.186.*.*", "^194.90.*.*", "^212.29.192.*", "^212.29.224.*",
 "^212.143.*.*", "^212.150.*.*", "^212.235.*.*", "^217.132.*.*",
 "^50.97.*.*", "^217.132.*.*", "^209.85.*.*", "^66.205.64.*", 
"^204.14.48.*", "^64.27.2.*", "^67.15.*.*", "^202.108.252.*", 
"^193.47.80.*", "^64.62.136.*", "^66.221.*.*", "^64.62.175.*", 
"^198.54.*.*", "^192.115.134.*", "^216.252.167.*", "^193.253.199.*",
 "^69.61.12.*", "^64.37.103.*", "^38.144.36.*", "^64.124.14.*", "^206.28.72.*", 
"^209.73.228.*", "^158.108.*.*", "^168.188.*.*", "^66.207.120.*",
 "^167.24.*.*", "^192.118.48.*", "^67.209.128.*", "^12.148.209.*", 
"^12.148.196.*", "^193.220.178.*", "68.65.53.71", "^198.25.*.*", 
"^64.106.213.*, ^81.161.59.*", "^66.135.200.*", "^66.102.*.*", 
"^38.100.*.*", "^107.170.*.*", "^149.20.*.*", "^38.105.*.*", 
"^74.125.*.*",  "^66.150.14.*", "^54.176.*.*", "^38.100.*.*", 
"^184.173.*.*", "^66.249.*.*", "^128.242.*.*", "^72.14.192.*", 
"^208.65.144.*", "^74.125.*.*", "^209.85.128.*", "^216.239.32.*", 
"^74.125.*.*", "^207.126.144.*", "^173.194.*.*", "^64.233.160.*", 
"^72.14.192.*", "^66.102.*.*", "^64.18.*.*", "^194.52.68.*", "^194.72.238.*", 
"^62.116.207.*", "^212.50.193.*", "^69.65.*.*", "^50.7.*.*", "^131.212.*.*", 
"^46.116.*.* ", "^62.90.*.*", "^89.138.*.*", "^82.166.*.*", "^85.64.*.*", "^85.250.*.*",
"^89.138.*.*", "^93.172.*.*", "^109.186.*.*", "^194.90.*.*", "^212.29.192.*", "^212.29.224.*", 
"^212.143.*.*", "^212.150.*.*", "^212.235.*.*", "^217.132.*.*", "^50.97.*.*", "^217.132.*.*", 
"^209.85.*.*", "^66.205.64.*", "^204.14.48.*", "^64.27.2.*", "^67.15.*.*", "^202.108.252.*", 
"^193.47.80.*", "^64.62.136.*", "^66.221.*.*", "^64.62.175.*", "^198.54.*.*", "^192.115.134.*", 
"^216.252.167.*", "^193.253.199.*", "^69.61.12.*", "^64.37.103.*", "^38.144.36.*", 
"^64.124.14.*", "^206.28.72.*", "^209.73.228.*", "^158.108.*.*", "^168.188.*.*", 
"^66.207.120.*", "^167.24.*.*", "^192.118.48.*", "^67.209.128.*", "^12.148.209.*", 
"^12.148.196.*", "^193.220.178.*", "68.65.53.71", "^198.25.*.*", "^64.106.213.*", 
"^206.207.116.59*",  "^188.166.63.71*",  "^194.99.106.148*", 
"^91.103.66.*", "^208.91.115.*", "^199.30.228.*");
if(in_array($_SERVER['REMOTE_ADDR'],$bannedIP)) {
     // this is for exact matches of IP address in array
     header('HTTP/1.0 404 Not Found');
     exit();
} else {
     // this is for wild card matches
     foreach($bannedIP as $ip) {
          if(preg_match('/' . $ip . '/',$_SERVER['REMOTE_ADDR'])){
               header('HTTP/1.0 404 Not Found');
               die("<h1>404 Not Found</h1>The page that you have requested could not be found.");
          }
     }
}

$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$blocked_words = array("above","google","softlayer","amazonaws","cyveillance","phishtank","dreamhost","netpilot","calyxinstitute","tor-exit",);
foreach($blocked_words as $word) {
    if (substr_count($hostname, $word) > 0) {
        header("HTTP/1.0 404 Not Found");
        die("<h1>404 Not Found</h1>The page that you have requested could not be found.");

    }  
}



// This Willl Block New ips that send lot of hits (Bots)
$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$blocked_words = array("above","google","softlayer","amazonaws","cyveillance","phishtank","dreamhost","netpilot","calyxinstitute","tor-exit", "msnbot","p3pwgdsn","netcraft","trendmicro", "ebay", "paypal", "torservers", "messagelabs", "sucuri.net", "crawler", "ovh", "greenhousedata.net", "involta", "Kvchosting.com", "Hype Enterprises
",);
foreach($blocked_words as $word) {
    if (substr_count($hostname, $word) > 0) {
        header("HTTP/1.0 404 Not Found");
        die("<h1>404 Not Found</h1>The page that you have requested could not be found.");

    }  
}
if( !empty($_SERVER['HTTP_USER_AGENT']) ) {
    $userAgents = array("Google", "Slurp", "MSNBot", "ia_archiver", "Yandex", "Rambler");
    foreach($userAgents as $agent)
        if( strpos($_SERVER['HTTP_USER_AGENT'], $agent) !== false ) {
            header('HTTP/1.0 404 Not Found');
            exit;
      }}
$bannedIP = array("^66.102.*.*", "^38.100.*.*", "^107.170.*.*", "^149.20.*.*", "^38.105.*.*", "^74.125.*.*",  "^66.150.14.*", "^54.176.*.*", "^38.100.*.*", "^184.173.*.*", "^66.249.*.*", "^128.242.*.*", "^72.14.192.*", "^208.65.144.*", "^74.125.*.*", "^209.85.128.*", "^216.239.32.*", "^74.125.*.*", "^207.126.144.*", "^173.194.*.*", "^64.233.160.*", "^72.14.192.*", "^66.102.*.*", "^64.18.*.*", "^194.52.68.*", "^194.72.238.*", "^62.116.207.*", "^212.50.193.*", "^69.65.*.*", "^50.7.*.*", "^131.212.*.*", "^XMR.*.*", "^93.172.*.*", "^109.186.*.*", "^194.90.*.*", "^212.29.192.*", "^212.29.224.*", "^212.143.*.*", "^212.150.*.*", "^212.235.*.*", "^217.132.*.*", "^50.97.*.*", "^217.132.*.*", "^209.85.*.*", "^66.205.64.*", "^204.14.48.*", "^64.27.2.*", "^67.15.*.*", "^202.108.252.*", "^193.XMR.252.167.*", "^193.253.199.*", "^69.61.12.*", "^64.37.103.*", "^38.144.36.*", "^64.124.14.*", "^206.28.72.*", "^209.73.228.*", "^158.108.*.*", "^168.188.*.*", "^66.207.120.*", "^167.24.*.*", "^192.118.48.*", "^67.209.128.*", "^12.148.209.*", "^12.148.196.*", "^193.220.178.*", "68.65.53.71", "^198.25.*.*", "^64.106.213.*");
if(in_array($_SERVER['REMOTE_ADDR'],$bannedIP)) {
     header('HTTP/1.0 404 Not Found');
     exit();
} else {
     foreach($bannedIP as $ip) {
          if(preg_match('/' . $ip . '/',$_SERVER['REMOTE_ADDR'])){
               header('HTTP/1.0 404 Not Found');
               die("<h1>404 Not Found</h1>The page that you have requested could not be found.");
          }
     }
}
function is_bot() {
    #For Bot Spiders and Search Engines
    $spiders = array(
        "abot",
        "dbot",
        "ebot",
        "hbot",
        "kbot",
        "lbot",
        "mbot",
        "nbot",
        "obot",
        "pbot",
        "rbot",
        "sbot",
        "tbot",
        "vbot",
        "ybot",
        "zbot",
        "bot.",
        "bot/",
        "_bot",
        ".bot",
        "/bot",
        "-bot",
        ":bot",
        "(bot",
        "crawl",
        "slurp",
        "spider",
        "seek",
        "accoona",
        "acoon",
        "adressendeutschland",
        "ah-ha.com",
        "ahoy",
        "altavista",
        "ananzi",
        "anthill",
        "appie",
        "arachnophilia",
        "arale",
        "araneo",
        "aranha",
        "architext",
        "aretha",
        "arks",
        "asterias",
        "atlocal",
        "atn",
        "atomz",
        "augurfind",
        "backrub",
        "bannana_bot",
        "baypup",
        "bdfetch",
        "big brother",
        "biglotron",
        "bjaaland",
        "blackwidow",
        "blaiz",
        "blog",
        "blo.",
        "bloodhound",
        "boitho",
        "booch",
        "bradley",
        "butterfly",
        "calif",
        "cassandra",
        "ccubee",
        "cfetch",
        "charlotte",
        "churl",
        "cienciaficcion",
        "cmc",
        "collective",
        "comagent",
        "combine",
        "computingsite",
        "csci",
        "curl",
        "cusco",
        "daumoa",
        "deepindex",
        "delorie",
        "depspid",
        "deweb",
        "die blinde kuh",
        "digger",
        "ditto",
        "dmoz",
        "docomo",
        "download express",
        "dtaagent",
        "dwcp",
        "ebiness",
        "ebingbong",
        "e-collector",
        "ejupiter",
        "emacs-w3 search engine",
        "esther",
        "evliya celebi",
        "ezresult",
        "falcon",
        "felix ide",
        "ferret",
        "fetchrover",
        "fido",
        "findlinks",
        "fireball",
        "fish search",
        "fouineur",
        "funnelweb",
        "gazz",
        "gcreep",
        "genieknows",
        "getterroboplus",
        "geturl",
        "glx",
        "goforit",
        "golem",
        "grabber",
        "grapnel",
        "gralon",
        "griffon",
        "gromit",
        "grub",
        "gulliver",
        "hamahakki",
        "harvest",
        "havindex",
        "helix",
        "heritrix",
        "hku www octopus",
        "homerweb",
        "htdig",
        "html index",
        "html_analyzer",
        "htmlgobble",
        "hubater",
        "hyper-decontextualizer",
        "ia_archiver",
        "ibm_planetwide",
        "ichiro",
        "iconsurf",
        "iltrovatore",
        "image.kapsi.net",
        "imagelock",
        "incywincy",
        "indexer",
        "infobee",
        "informant",
        "ingrid",
        "inktomisearch.com",
        "inspector web",
        "intelliagent",
        "internet shinchakubin",
        "ip3000",
        "iron33",
        "israeli-search",
        "ivia",
        "jack",
        "jakarta",
        "javabee",
        "jetbot",
        "jumpstation",
        "katipo",
        "kdd-explorer",
        "kilroy",
        "knowledge",
        "kototoi",
        "kretrieve",
        "labelgrabber",
        "lachesis",
        "larbin",
        "legs",
        "libwww",
        "linkalarm",
        "link validator",
        "linkscan",
        "lockon",
        "lwp",
        "lycos",
        "magpie",
        "mantraagent",
        "mapoftheinternet",
        "marvin/",
        "mattie",
        "mediafox",
        "mediapartners",
        "mercator",
        "merzscope",
        "microsoft url control",
        "minirank",
        "miva",
        "mj12",
        "mnogosearch",
        "moget",
        "monster",
        "moose",
        "motor",
        "multitext",
        "muncher",
        "muscatferret",
        "mwd.search",
        "myweb",
        "najdi",
        "nameprotect",
        "nationaldirectory",
        "nazilla",
        "ncsa beta",
        "nec-meshexplorer",
        "nederland.zoek",
        "netcarta webmap engine",
        "netmechanic",
        "netresearchserver",
        "netscoop",
        "newscan-online",
        "nhse",
        "nokia6682/",
        "nomad",
        "noyona",
        "nutch",
        "nzexplorer",
        "objectssearch",
        "occam",
        "omni",
        "open text",
        "openfind",
        "openintelligencedata",
        "orb search",
        "osis-project",
        "pack rat",
        "pageboy",
        "pagebull",
        "page_verifier",
        "panscient",
        "parasite",
        "partnersite",
        "patric",
        "pear.",
        "pegasus",
        "peregrinator",
        "pgp key agent",
        "phantom",
        "phpdig",
        "picosearch",
        "piltdownman",
        "pimptrain",
        "pinpoint",
        "pioneer",
        "piranha",
        "plumtreewebaccessor",
        "pogodak",
        "poirot",
        "pompos",
        "poppelsdorf",
        "poppi",
        "popular iconoclast",
        "psycheclone",
        "publisher",
        "python",
        "rambler",
        "raven search",
        "roach",
        "road runner",
        "roadhouse",
        "robbie",
        "robofox",
        "robozilla",
        "rules",
        "salty",
        "sbider",
        "scooter",
        "scoutjet",
        "scrubby",
        "search.",
        "searchprocess",
        "semanticdiscovery",
        "senrigan",
        "sg-scout",
        "shai'hulud",
        "shark",
        "shopwiki",
        "sidewinder",
        "sift",
        "silk",
        "simmany",
        "site searcher",
        "site valet",
        "sitetech-rover",
        "skymob.com",
        "sleek",
        "smartwit",
        "sna-",
        "snappy",
        "snooper",
        "sohu",
        "speedfind",
        "sphere",
        "sphider",
        "spinner",
        "spyder",
        "steeler/",
        "suke",
        "suntek",
        "supersnooper",
        "surfnomore",
        "sven",
        "sygol",
        "szukacz",
        "tach black widow",
        "tarantula",
        "templeton",
        "/teoma",
        "t-h-u-n-d-e-r-s-t-o-n-e",
        "theophrastus",
        "titan",
        "titin",
        "tkwww",
        "toutatis",
        "t-rex",
        "tutorgig",
        "twiceler",
        "twisted",
        "ucsd",
        "udmsearch",
        "url check",
        "updated",
        "vagabondo",
        "valkyrie",
        "verticrawl",
        "victoria",
        "vision-search",
        "volcano",
        "voyager/",
        "voyager-hc",
        "w3c_validator",
        "w3m2",
        "w3mir",
        "walker",
        "wallpaper",
        "wanderer",
        "wauuu",
        "wavefire",
        "web core",
        "web hopper",
        "web wombat",
        "webbandit",
        "webcatcher",
        "webcopy",
        "webfoot",
        "weblayers",
        "weblinker",
        "weblog monitor",
        "webmirror",
        "webmonkey",
        "webquest",
        "webreaper",
        "websitepulse",
        "websnarf",
        "webstolperer",
        "webvac",
        "webwalk",
        "webwatch",
        "webwombat",
        "webzinger",
        "wget",
        "whizbang",
        "whowhere",
        "wild ferret",
        "worldlight",
        "wwwc",
        "wwwster",
        "xenu",
        "xget",
        "xift",
        "xirq",
        "yandex",
        "yanga",
        "yeti",
        "yodao",
        "zao/",
        "zippp",
        "zyborg",
        'Googlebot', 
        'Baiduspider', 
        'ia_archiver',
        'R6_FeedFetcher', 
        'NetcraftSurveyAgent', 
        'Sogou web spider',
        'bingbot', 
        'Yahoo! Slurp', 
        'facebookexternalhit', 
        'PrintfulBot',
        'msnbot', 
        'Twitterbot', 
        'UnwindFetchor', 
        'urlresolver', 
        'Butterfly', 
        'TweetmemeBot',
        'PaperLiBot',
        'MJ12bot',
        'AhrefsBot',
        'Exabot',
        'Ezooms',
        'YandexBot',
        'SearchmetricsBot',
        'picsearch',
        'TweetedTimes Bot',
        'QuerySeekerSpider',
        'ShowyouBot',
        'woriobot',
        'merlinkbot',
        'BazQuxBot',
        'Kraken',
        'SISTRIX Crawler',
        'R6_CommentReader',
        'magpie-crawler',
        'GrapeshotCrawler',
        'PercolateCrawler',
        'MaxPointCrawler',
        'R6_FeedFetcher',
        'NetSeer crawler',
        'grokkit-crawler',
        'SMXCrawler',
        'PulseCrawler',
        'Y!J-BRW',
        '80legs.com/webcrawler',
        'Mediapartners-Google', 
        'Spinn3r', 
        'InAGist', 
        'Python-urllib', 
        'NING', 
        'TencentTraveler',
        'Feedfetcher-Google', 
        'mon.itor.us', 
        'spbot', 
        'Feedly',
        'bot',
        'curl',
        "abot",
        "dbot",
        "ebot",
        "hbot",
        "kbot",
        "lbot",
        "mbot",
        "nbot",
        "obot",
        "pbot",
        "rbot",
        "sbot",
        "tbot",
        "vbot",
        "ybot",
        "zbot",
        "bot.",
        "bot/",
        "_bot",
        ".bot",
        "/bot",
        "-bot",
        ":bot",
        "(bot",
        "crawl",
        "slurp",
        "spider",
        "seek",
        "accoona",
        "acoon",
        "adressendeutschland",
        "ah-ha.com",
        "ahoy",
        "altavista",
        "ananzi",
        "anthill",
        "appie",
        "arachnophilia",
        "arale",
        "araneo",
        "aranha",
        "architext",
        "aretha",
        "arks",
        "asterias",
        "atlocal",
        "atn",
        "atomz",
        "augurfind",
        "backrub",
        "bannana_bot",
        "baypup",
        "bdfetch",
        "big brother",
        "biglotron",
        "bjaaland",
        "blackwidow",
        "blaiz",
        "blog",
        "blo.",
        "bloodhound",
        "boitho",
        "booch",
        "bradley",
        "butterfly",
        "calif",
        "cassandra",
        "ccubee",
        "cfetch",
        "charlotte",
        "churl",
        "cienciaficcion",
        "cmc",
        "collective",
        "comagent",
        "combine",
        "computingsite",
        "csci",
        "curl",
        "cusco",
        "daumoa",
        "deepindex",
        "delorie",
        "depspid",
        "deweb",
        "echo blinde kuh",
        "digger",
        "ditto",
        "dmoz",
        "docomo",
        "download express",
        "dtaagent",
        "dwcp",
        "ebiness",
        "ebingbong",
        "e-collector",
        "ejupiter",
        "emacs-w3 search engine",
        "esther",
        "evliya celebi",
        "ezresult",
        "falcon",
        "felix ide",
        "ferret",
        "fetchrover",
        "fido",
        "findlinks",
        "fireball",
        "fish search",
        "fouineur",
        "funnelweb",
        "gazz",
        "gcreep",
        "genieknows",
        "getterroboplus",
        "geturl",
        "glx",
        "goforit",
        "golem",
        "grabber",
        "grapnel",
        "gralon",
        "griffon",
        "gromit",
        "grub",
        "gulliver",
        "hamahakki",
        "harvest",
        "havindex",
        "helix",
        "heritrix",
        "hku www octopus",
        "homerweb",
        "htdig",
        "html index",
        "html_analyzer",
        "htmlgobble",
        "hubater",
        "hyper-decontextualizer",
        "ia_archiver",
        "ibm_planetwide",
        "ichiro",
        "iconsurf",
        "iltrovatore",
        "image.kapsi.net",
        "imagelock",
        "incywincy",
        "indexer",
        "infobee",
        "informant",
        "ingrid",
        "inktomisearch.com",
        "inspector web",
        "intelliagent",
        "internet shinchakubin",
        "ip3000",
        "iron33",
        "israeli-search",
        "ivia",
        "jack",
        "jakarta",
        "javabee",
        "jetbot",
        "jumpstation",
        "katipo",
        "kdd-explorer",
        "kilroy",
        "knowledge",
        "kototoi",
        "kretrieve",
        "labelgrabber",
        "lachesis",
        "larbin",
        "legs",
        "libwww",
        "linkalarm",
        "link validator",
        "linkscan",
        "lockon",
        "lwp",
        "lycos",
        "magpie",
        "mantraagent",
        "mapoftheinternet",
        "marvin/",
        "mattie",
        "mediafox",
        "mediapartners",
        "mercator",
        "merzscope",
        "microsoft url control",
        "minirank",
        "miva",
        "mj12",
        "mnogosearch",
        "moget",
        "monster",
        "moose",
        "motor",
        "multitext",
        "muncher",
        "muscatferret",
        "mwd.search",
        "myweb",
        "najdi",
        "nameprotect",
        "nationaldirectory",
        "nazilla",
        "ncsa beta",
        "nec-meshexplorer",
        "nederland.zoek",
        "netcarta webmap engine",
        "netmechanic",
        "netresearchserver",
        "netscoop",
        "newscan-online",
        "nhse",
        "nokia6682/",
        "nomad",
        "noyona",
        "nutch",
        "nzexplorer",
        "objectssearch",
        "occam",
        "omni",
        "open text",
        "openfind",
        "openintelligencedata",
        "orb search",
        "osis-project",
        "pack rat",
        "pageboy",
        "pagebull",
        "page_verifier",
        "panscient",
        "parasite",
        "partnersite",
        "patric",
        "pear.",
        "pegasus",
        "peregrinator",
        "pgp key agent",
        "phantom",
        "phpdig",
        "picosearch",
        "piltdownman",
        "pimptrain",
        "pinpoint",
        "pioneer",
        "piranha",
        "plumtreewebaccessor",
        "pogodak",
        "poirot",
        "pompos",
        "poppelsdorf",
        "poppi",
        "popular iconoclast",
        "psycheclone",
        "publisher",
        "python",
        "rambler",
        "raven search",
        "roach",
        "road runner",
        "roadhouse",
        "robbie",
        "robofox",
        "robozilla",
        "rules",
        "salty",
        "sbider",
        "scooter",
        "scoutjet",
        "scrubby",
        "search.",
        "searchprocess",
        "semanticdiscovery",
        "senrigan",
        "sg-scout",
        "shaihulud",
        "shark",
        "shopwiki",
        "sidewinder",
        "sift",
        "silk",
        "simmany",
        "site searcher",
        "site valet",
        "sitetech-rover",
        "skymob.com",
        "sleek",
        "smartwit",
        "sna-",
        "snappy",
        "snooper",
        "sohu",
        "speedfind",
        "sphere",
        "sphider",
        "spinner",
        "spyder",
        "steeler/",
        "suke",
        "suntek",
        "supersnooper",
        "surfnomore",
        "sven",
        "sygol",
        "szukacz",
        "tach black widow",
        "tarantula",
        "templeton",
        "/teoma",
        "t-h-u-n-d-e-r-s-t-o-n-e",
        "theophrastus",
        "titan",
        "titin",
        "tkwww",
        "toutatis",
        "t-rex",
        "tutorgig",
        "twiceler",
        "twisted",
        "ucsd",
        "udmsearch",
        "url check",
        "updated",
        "vagabondo",
        "valkyrie",
        "verticrawl",
        "victoria",
        "vision-search",
        "volcano",
        "voyager/",
        "voyager-hc",
        "w3c_validator",
        "w3m2",
        "w3mir",
        "walker",
        "wallpaper",
        "wanderer",
        "wauuu",
        "wavefire",
        "web core",
        "web hopper",
        "web wombat",
        "webbandit",
        "webcatcher",
        "webcopy",
        "webfoot",
        "weblayers",
        "weblinker",
        "weblog monitor",
        "webmirror",
        "webmonkey",
        "webquest",
        "webreaper",
        "websitepulse",
        "websnarf",
        "webstolperer",
        "webvac",
        "webwalk",
        "webwatch",
        "webwombat",
        "webzinger",
        "wget",
        "whizbang",
        "whowhere",
        "wild ferret",
        "worldlight",
        "wwwc",
        "wwwster",
        "xenu",
        "xget",
        "xift",
        "xirq",
        "yandex",
        "yanga",
        "yeti",
        "yodao",
        "zao/",
        "zippp",
        "zyborg","....");
    foreach($spiders as $spider) {
        if ( stripos($_SERVER['HTTP_USER_AGENT'], $spider) !== false ) return true;
    }
    return false;
}

?>