<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================

===========================================================================
work.php
@version $Id$
@author : Dionysios G. Synodinos <synodinos@gmail.com>
@author : Evelthon Prodromou <eprodromou@upnet.gr>
============================================================================
@Description: Main script for the work tool
============================================================================
*/


$require_current_course = true;
$require_login = true;
$require_help = true;
$helpTopic = 'Work';

include '../../include/baseTheme.php';
include '../../include/lib/forcedownload.php';

$head_content = "
<script type='text/javascript'>
function confirmation (name)
{

    if (confirm(\"$langDelWarn1 \"+ name + \". $langWarnForSubmissions. $langDelSure \"))
        {return true;}
    else
        {return false;}
}
</script>
";

$head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(which, entry) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if (tempobj.name == entry) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langEmptyAsTitle");
		return false;
	} else {
		return true;
	}
}

</script>
hContent;

// For using with the pop-up calendar
include 'jscalendar.inc.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_ASSIGN');
/**************************************/

include('work_functions.php');
include('../group/group_functions.php');

$workPath = $webDir."courses/".$currentCourseID."/work";

if ($is_adminOfCourse) { //Only course admins can download assignments
  if (isset($_GET['get'])) {
	send_file($_GET['get']);
  }

  if (isset($_GET['download'])) {
	include "../../include/pclzip/pclzip.lib.php";
	download_assignments($_GET['download']);
  }
}

$nameTools = $langWorks;
mysql_select_db($currentCourseID);

include '../../include/lib/fileUploadLib.inc.php';
include '../../include/lib/fileManageLib.inc.php';

include '../../include/libchart/libchart.php';

//-------------------------------------------
// main program
//-------------------------------------------

if ($is_adminOfCourse) {
	if (isset($_POST['grade_comments'])) {
		$nameTools = $m['WorkView'];
		$navigation[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
		submit_grade_comments($_POST['assignment'], $_POST['submission'], $_POST['grade'], $_POST['comments']);
	} elseif (isset($_GET['add'])) {
		$nameTools = $langNewAssign;
		$navigation[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
		new_assignment();
	} elseif (isset($_POST['sid'])) {
		show_submission($_POST['sid']);
	} elseif (isset($_POST['new_assign'])) {
		add_assignment($_POST['title'], $_POST['desc'], "$_POST[WorkEnd]", $_POST['group_submissions']);
		show_assignments();
	} elseif (isset($_POST['grades'])) {
		$nameTools = $m['WorkView'];
		$navigation[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
		submit_grades($_POST['grades_id'], $_POST['grades']);
	} elseif (isset($_REQUEST['id'])) {
                $id = $_REQUEST['id'];
		if (isset($_REQUEST['choice'])) {
                        $choice = $_REQUEST['choice'];
			if ($choice == 'disable') {
				db_query("UPDATE assignments SET active = '0' WHERE id = '$id'");
				show_assignments($langAssignmentDeactivated);
			} elseif ($choice == 'enable') {
				db_query("UPDATE assignments SET active = '1' WHERE id = '$id'");
				show_assignments($langAssignmentActivated);
			} elseif ($choice == 'delete') {
				die("invalid option");
			} elseif ($choice == "do_delete") {
				$nameTools = $m['WorkDelete'];
				$navigation[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
				delete_assignment($id);
			} elseif ($choice == 'edit') {
				$nameTools = $m['WorkEdit'];
				$navigation[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
				show_edit_assignment($id);
			} elseif ($choice == 'do_edit') {
				$nameTools = $m['WorkView'];
				$navigation[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
				edit_assignment($id);
			} elseif ($choice = 'plain') {
				show_plain_view($id);
			}
		} else {
			$nameTools = $m['WorkView'];
			$navigation[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
			show_assignment($id);
		}
	} else {
		$nameTools = $m['WorkView'];
		$navigation[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
		show_assignments();
	}
} else {
	if (isset($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
		if (isset($_POST['work_submit'])) {
			$nameTools = $m['SubmissionStatusWorkInfo'];
			$navigation[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
			$navigation[] = array("url"=>"$_SERVER[PHP_SELF]?id=$id", "name"=>$m['WorkView']);
			submit_work($id);
		} else {
			$nameTools = $m['WorkView'];
			$navigation[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
			show_student_assignment($id);
		}
	} else {
		show_student_assignments();
	}
}

add_units_navigation(TRUE);
draw($tool_content, 2, '', $head_content.$local_head);

//-------------------------------------
// end of main program
//-------------------------------------

// Show details of a student's submission to professor
function show_submission($sid)
{
	global $tool_content, $langWorks, $langSubmissionDescr, $langNotice3;

	$nameTools = $langWorks;
	$navigation[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);

	if ($sub = mysql_fetch_array(db_query("SELECT * FROM assignment_submit WHERE id = '$sid'"))) {

		$tool_content .= "<p>$langSubmissionDescr".
		uid_to_name($sub['uid']).
		$sub['submission_date'].
		"<a href='$GLOBALS[urlServer]$GLOBALS[currentCourseID]".
		"/work/$sub[file_path]'>$sub[file_name]</a>";
		if (!empty($sub['comments'])) {
			$tool_content .=  " $langNotice3: $sub[comments]";
		}
		$tool_content .=  "</p>\n";
	} else {
		$tool_content .= "<p class=\"caution\">error - no such submission with id $sid</p>\n";
	}
}


// insert the assignment into the database
function add_assignment($title, $desc, $deadline, $group_submissions)
{
	global $tool_content, $workPath;

	$secret = uniqid("");
	db_query("INSERT INTO assignments
		(title, description, comments, deadline, submission_date, secret_directory,
			group_submissions) VALUES
		(".autoquote(trim($title)).", ".autoquote(trim($desc)).", ' ', ".autoquote($deadline).", NOW(), '$secret',
			".autoquote($group_submissions).")");
	mkdir("$workPath/$secret",0777);
}



function submit_work($id)
{
        global $tool_content, $workPath, $uid, $cours_id, $stud_comments,
                $langUploadSuccess, $langBack, $langWorks, $langUploadError, $currentCourseID,
                $langExerciseNotPermit, $langUnwantedFiletype;

        $submit_ok = FALSE; //Default do not allow submission
        if(isset($uid) && $uid) { //check if logged-in
                if ($GLOBALS['statut'] == 10) { //user is guest
                        $submit_ok = FALSE;
                } else { //user NOT guest
                        if(isset($_SESSION['status']) && isset($_SESSION['status'][$_SESSION["dbname"]])) {
                                //user is registered to this lesson
                                $res = db_query("SELECT (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days
                                        FROM assignments WHERE id = '$id'");
                                $row = mysql_fetch_array($res);
                                if ($row['days'] < 0) {
                                        $submit_ok = FALSE; //after assignment deadline
                                } else {
                                        $submit_ok = TRUE; //before deadline
                                }
                        } else {
                                //user NOT registered to this lesson
                                $submit_ok = FALSE;
                        }
                }
        } //checks for submission validity end here

        $res = db_query("SELECT title, group_submissions FROM assignments WHERE id = '$id'");
        $row = mysql_fetch_array($res);
        $group_sub = $row['group_submissions'];
        $nav[] = array('url' => $_SERVER['PHP_SELF'], 'name' => $langWorks);
        $nav[] = array('url' => "$_SERVER[PHP_SELF]?id=$id", 'name' => $row['title']);

        if ($submit_ok) { // if passed the above validity checks...
                if ($group_sub) {
                    $group_id = isset($_POST['gid'])? intval($_POST['gid']): -1;
                    $gids = user_group_info($uid, $cours_id);
                    $local_name = isset($gids[$group_id])? greek_to_latin($gids[$group_id]): '';
                } else {
                    $local_name = greek_to_latin(uid_to_name($uid));
                    $am = mysql_fetch_array(db_query("SELECT am FROM user WHERE user_id = '$uid'"));
                    if (!empty($am[0])) {
                            $local_name = "$local_name $am[0]";
                    }
                }
                $local_name = replace_dangerous_char($local_name);
                if (preg_match('/\.(ade|adp|bas|bat|chm|cmd|com|cpl|crt|exe|hlp|hta|' .'inf|ins|isp|jse|lnk|mdb|mde|msc|msi|msp|mst|pcd|pif|reg|scr|sct|shs|' .'shb|url|vbe|vbs|wsc|wsf|wsh)$/', $_FILES['userfile']['name'])) {
                        $tool_content .= "<p class=\"caution\">$langUnwantedFiletype: {$_FILES['userfile']['name']}<br />";
                        $tool_content .= "<a href=\"$_SERVER[PHP_SELF]?id=$id\">$langBack</a></p><br />";
                        return;
                }
                $secret = work_secret($id);
                $ext = get_file_extension($_FILES['userfile']['name']);
                $filename = "$secret/$local_name" . (empty($ext)? '': '.' . $ext);
                if (move_uploaded_file($_FILES['userfile']['tmp_name'], "$workPath/$filename")) {
                        @chmod("$workPath/$filename", 0644);
                        $msg2 = $langUploadSuccess;
                        if ($group_sub) {
                            if (array_key_exists($group_id, $gids)) {
                                    $msg1 = delete_submissions_by_uid(-1, $group_id, $id);
                                    db_query("INSERT INTO assignment_submit
                                        (uid, assignment_id, submission_date, submission_ip, file_path,
                                        file_name, comments, grade_comments, group_id)
                                        VALUES
                                        ($uid, $id, NOW(), " . quote($_SERVER['REMOTE_ADDR']) . ",
                                        " . quote($filename) . ", ". autoquote($_FILES['userfile']['name']) . ",
                                        " . autoquote($stud_comments) . ", '', $group_id)", $currentCourseID);    
                            }
                        } else {
                                $msg1 = delete_submissions_by_uid($uid, -1, $id);  
                                db_query("INSERT INTO assignment_submit
                                        (uid, assignment_id, submission_date, submission_ip, file_path,
                                        file_name, comments, grade_comments) VALUES ($uid, $id, NOW(), " . quote($_SERVER['REMOTE_ADDR']) . ",
                                        " . quote($filename) . ", " . autoquote($_FILES['userfile']['name']) . ",
                                        " . autoquote($stud_comments) . ", '')", $currentCourseID);
                        }
                        $tool_content .= "<p class='success'>$msg2<br />$msg1<br /><a href='$_SERVER[PHP_SELF]'>$langBack</a></p><br />";
                } else {
                        $tool_content .= "<p class='caution'>$langUploadError<br /><a href='$_SERVER[PHP_SELF]'>$langBack</a></p><br />";
                }
        } else { // not submit_ok
                $tool_content .="<p class=\"caution\">$langExerciseNotPermit<br /><a href='$_SERVER[PHP_SELF]'>$langBack</a></p></br>";
        }
}


//  assignment - prof view only
function new_assignment()
{
	global $tool_content, $m, $langAdd;
	global $urlAppend;
	global $desc;
	global $end_cal_Work;
	global $langBack;

	$day	= date("d");
	$month	= date("m");
	$year	= date("Y");


	$tool_content .= "
        <form action='$_SERVER[PHP_SELF]' method='post' onsubmit='return checkrequired(this, \"title\");'>
        <fieldset>
        <legend>$m[WorkInfo]</legend>
        <table class='tbl'>
        <tr>
          <th>$m[title]:</th>
          <td><input type='text' name='title' size='55' /></td>
        </tr>
        <tr>
          <th valign='top'>$m[description]:</th>
          <td>" . rich_text_editor('desc', 4, 20, $desc) . " </td>
        </tr>
        <tr>
          <th valign='top'>$m[deadline]:</th>
          <td>$end_cal_Work</td>
        </tr>
        <tr>
          <th valign='top'>$m[group_or_user]:</th>
          <td><input type='radio' name='group_submissions' value='0' checked='1' />$m[user_work]
          <br /><input type='radio' name='group_submissions' value='1' />$m[group_work]</td>
        </tr>
        <tr>
          <th>&nbsp;</th>
          <td><input type='submit' name='new_assign' value='$langAdd' /></td>
        </tr>
        </table>
        </fieldset>
      </form>
      <br />";
  	$tool_content .= "\n      <p align='right'><a href='$_SERVER[PHP_SELF]'>$langBack</a></p>";
}


function date_form($day, $month, $year)
{
	global $tool_content, $langMonthNames;
	$tool_content .=  "<select name='fday'>\n";
	for ($i = 1; $i <= 31; $i++) {
		if ($i == $day)
		$tool_content .= "<option value='$i' selected='1'>$i</option>\n";
		else
		$tool_content .= "<option value='$i'>$i</option>\n";
	}
	$tool_content .= "</select><select name='fmonth'>\n";
	for ($i = 1; $i <= 12; $i++) {
		if ($i == $month)
		$tool_content .= "<option value='$i' selected='1'>".$langMonthNames['long'][$i-1]."</option>\n";
		else
		$tool_content .= "<option value='$i'>".$langMonthNames['long'][$i-1]."</option>\n";
	}
	$tool_content .= "</select><select name='fyear'>\n";
	for ($i = date("Y"); $i <= date("Y") + 1; $i++) {
		if ($i == $year)
		$tool_content .= "<option value='$i' selected='1'>$i</option>\n";
		else
		$tool_content .= "<option value='$i'>$i</option>\n";
	}
	$tool_content .= "</select>\n";
}

//form for editing
function show_edit_assignment($id)
{
	global $tool_content, $m, $langEdit, $langWorks, $langBack;
	global $urlAppend;
	global $end_cal_Work_db;

	$res = db_query("SELECT * FROM assignments WHERE id = '$id'");
	$row = mysql_fetch_array($res);

	$nav[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
	$nav[] = array("url"=>"$_SERVER[PHP_SELF]?id=$id", "name"=> $row['title']);

	$deadline = $row['deadline'];

	$description = q($row['description']);
        $textarea = rich_text_editor('desc', 4, 20, $row['description']);
	$tool_content .= <<<cData
    <form action="$_SERVER[PHP_SELF]" method="post" onsubmit="return checkrequired(this, 'title');">
    <input type="hidden" name="id" value="$id" />
    <input type="hidden" name="choice" value="do_edit" />
    <fieldset>
    <legend>$m[WorkInfo]</legend>
    <table class='tbl'>
    <tr>
      <th>$m[title]:</th>
      <td><input type="text" name="title" size="45" value="${row['title']}" /></td>
    </tr>
    <tr>
      <th valign='top'>$m[description]:</th>
      <td>$textarea</td>
    </tr>
cData;
	$comments = trim($row['comments']);
        if (!empty($comments)) {
                $tool_content .= "
    <tr>
      <th>$m[comments]:</th>
      <td>" .  rich_text_editor('comments', 5, 65, $comments) .  "</td>
    </tr>";
        }

	if ($row['group_submissions'] == '0') {
                $group_checked_0 = ' checked="1"';
                $group_checked_1 = '';
        } else {
                $group_checked_0 = '';
                $group_checked_1 = ' checked="1"';
        }
        $tool_content .= "
    <tr>
      <th valign='top'>$m[deadline]:</th>
      <td>" .  getJsDeadline($deadline) .  "</td>
    </tr>
    <tr>
      <th valign='top'>$m[group_or_user]:</th>
      <td><input type='radio' name='group_submissions' value='0'$group_checked_0 />
          $m[user_work]<br />
          <input type='radio' name='group_submissions' value='1'$group_checked_1 />
          $m[group_work]</td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type='submit' name='do_edit' value='$langEdit' /></td>
    </tr>
    </table>
    </fieldset>
    </form>";

    $tool_content .= "\n   <br /><div align='right'><a href='$_SERVER[PHP_SELF]'>$langBack</ul></div>";
}

// edit assignment
function edit_assignment($id)
{

	global $tool_content, $langBackAssignment, $langEditSuccess, $langEditError, $langWorks, $langEdit;

	$nav[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
	$nav[] = array("url"=>"$_SERVER[PHP_SELF]?id=$id", "name"=> $_POST['title']);

        if (!isset($_POST['comments'])) {
                $comments = "''";
        } else {
                $comments = autoquote(trim($_POST['comments']));
        }
	if (db_query("UPDATE assignments SET title=".autoquote(trim($_POST['title'])).",
		description=".autoquote(trim($_POST['desc'])).", group_submissions=".autoquote($_POST['group_submissions']).",
		comments=$comments, deadline=".autoquote($_POST['WorkEnd'])." WHERE id='$id'")) {

        $title = autounquote($_POST['title']);
	$tool_content .="\n  <p class='success'>$langEditSuccess<br /><a href='$_SERVER[PHP_SELF]?id=$id'>$langBackAssignment '$title'</a></p><br />";
	} else {
	$tool_content .="\n  <p class='caution'>$langEditError<br /><a href='$_SERVER[PHP_SELF]?id=$id'>$langBackAssignment '$title'</a></p><br />";
	}
}


//delete assignment
function delete_assignment($id) {

	global $tool_content, $workPath, $currentCourseID, $webDir, $langBack, $langDeleted;

	$secret = work_secret($id);
	db_query("DELETE FROM assignments WHERE id='$id'");
	db_query("DELETE FROM assignment_submit WHERE assignment_id='$id'");
	@mkdir("$webDir/courses/garbage");
	@mkdir("$webDir/courses/garbage/$currentCourseID",0777);
	@mkdir("$webDir/courses/garbage/$currentCourseID/work",0777);
	move_dir("$workPath/$secret",
	"$webDir/courses/garbage/$currentCourseID/work/${id}_$secret");

	$tool_content .="\n  <p class=\"success\">$langDeleted<br /><a href=\"$_SERVER[PHP_SELF]\">".$langBack."</a></p>";
}


// show assignment - student
function show_student_assignment($id)
{
	global $tool_content, $m, $uid, $langSubmitted, $langSubmittedAndGraded, $langNotice3,
               $langWorks, $langUserOnly, $langBack, $langWorkGrade, $langGradeComments,
               $currentCourseID, $cours_id;

        $user_group_info = user_group_info($uid, $cours_id);

	$res = db_query("SELECT *, (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days
                                FROM `$currentCourseID`.assignments WHERE id = '$id'");
	$row = mysql_fetch_array($res);

	$nav[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);

	assignment_details($id, $row);

	if ($row['days'] < 0) {
		$submit_ok = FALSE;
	} else {
		$submit_ok = TRUE;
	}

	if (!$uid) {
		$tool_content .= "<p>$langUserOnly</p>";
		$submit_ok = FALSE;
	} elseif ($GLOBALS['statut'] == 10) {
		$tool_content .= "\n  <p class='alert1'>$m[noguest]</p>";
		$submit_ok = FALSE;
	} else {
                foreach (find_submissions($row['group_submissions'],
                                          $uid, $id, $user_group_info) as $sub) {
                    if (!empty($sub['grade'])) {
                        $submit_ok = false;
                    }
                    show_submission_details($sub['id']);
		}
	}
	if ($submit_ok) {
		show_submission_form($id, $user_group_info);
	}
	$tool_content .= "
    <br/>
    <p align=\"right\"><a href='$_SERVER[PHP_SELF]'>$langBack</a></p>";
}


function show_submission_form($id, $user_group_info)
{
        global $tool_content, $m, $langWorkFile, $langSendFile, $langSubmit, $uid, $langNotice3, $gid, $is_member,
               $urlAppend, $langGroupSpaceLink;

        $group_select_hidden_input = $group_select_form = '';
        $is_group_assignment = is_group_assignment($id);
        if ($is_group_assignment) {
                if (count($user_group_info) == 1) {
                        $gids = array_keys($user_group_info);
                        $group_link = $urlAppend . '/modules/group/document.php?gid=' . $gids[0];
                        $group_select_hidden_input = "<input type='hidden' name='gid' value='$gids[0]' />";
                } else {
                        $group_link = $urlAppend . '/modules/group/group.php';
                        $group_select_form = "<tr><th class='left'>$langGroupSpaceLink:</th><td>" .
                                             selection($user_group_info, 'gid') . "</td></tr>";
                }
                        $tool_content .= "<p class='alert1'>$m[this_is_group_assignment] <br />" .
                                sprintf(count($user_group_info)?
                                        $m['group_assignment_publish']:
                                        $m['group_assignment_no_groups'], $group_link) .
                                "</p>\n";
	}
        if (!$is_group_assignment or count($user_group_info)) {
                $tool_content .= "
                     <form enctype='multipart/form-data' action='$_SERVER[PHP_SELF]' method='post'>
                        <input type='hidden' name='id' value='$id' />$group_select_hidden_input
                        <fieldset>
                        <legend>$langSubmit</legend>
                        <table width='99%' class='tbl'>
                        $group_select_form 
                        <tr>
                          <th class='left' width='150'>$langWorkFile:</th>
                          <td><input type='file' name='userfile' /></td>
                        </tr>
                        <tr>
                          <th class='left'>$m[comments]:</th>
                          <td><textarea name='stud_comments' rows='5' cols='55'></textarea></td>
                        </tr>
                        <tr>
                          <th>&nbsp;</th>
                          <td><input type='submit' value='$langSubmit' name='work_submit' /><br />$langNotice3</td>
                        </tr>
                        </table>
                        </fieldset>
                     </form>
                     <p align='right'><small>$GLOBALS[langMaxFileSize] " .
                                ini_get('upload_max_filesize') . "</small></p>";
        }
}


// Print a box with the details of an assignment
function assignment_details($id, $row, $message = null)
{
	global $tool_content, $m, $langDaysLeft, $langDays, $langWEndDeadline, $langNEndDeadLine, $langNEndDeadline, $langEndDeadline;
	global $langDelAssign, $is_adminOfCourse, $langZipDownload, $langSaved ;

	if ($is_adminOfCourse) {
            $tool_content .= "
            <div id='operations_container'>
              <ul id='opslist'>
              <li><a href='$_SERVER[PHP_SELF]?id=$id&amp;choice=do_delete' onClick='return confirmation(\"" .
                js_escape($row['title']) . "\");'>$langDelAssign</a></li>
                <li><a href='$_SERVER[PHP_SELF]?download=$id'>$langZipDownload</a></li>
              </ul>
            </div>";
	}

	if (isset($message)) {
		$tool_content .="
                <table width=\"99%\" class='tbl'>
                <tr>
                  <td class=\"success\"><p><b>$langSaved</b></p></td>
                </tr>
                </table>
                <br/>";
            }
	$tool_content .= "
        <fieldset>
        <legend>".$m['WorkInfo']."</legend>
        <table width=\"99%\" class=\"tbl\">
        <tr>
          <th width='150'>$m[title]:</th>
          <td>$row[title]</td>
        </tr>";
        $tool_content .= "
        <tr>
          <th valign='top'>$m[description]:</th>
          <td>$row[description]</td>
        </tr>";
	if (!empty($row['comments'])) {
		$tool_content .= "
        <tr>
          <th class='left'>$m[comments]:</th>
          <td>$row[comments]</td>
        </tr>";
	}
	$tool_content .= "
        <tr>
          <th>$m[start_date]:</th>
          <td>".nice_format($row['submission_date'])."</td>
        </tr>
        <tr>
          <th valign='top'>$m[deadline]:</th>
          <td>".nice_format($row['deadline'])." <br />";
	if ($row['days'] > 1) {
		$tool_content .= "<span class=\"not_expired\">$langDaysLeft $row[days] $langDays</span></td>
        </tr>";
	} elseif ($row['days'] < 0) {
		$tool_content .= "<span class=\"expired\">$langEndDeadline</span></td>
        </tr>";
	} elseif ($row['days'] == 1) {
		$tool_content .= "<span class=\"expired_today\">$langWEndDeadline !</span></td>
        </tr>";
	} else {
		$tool_content .= "<span class=\"expired_today\"><b>$langNEndDeadLine</b> !!!</span></td>
        </tr>";
	}
	$tool_content .= "
        <tr>
          <th>$m[group_or_user]:</th>
          <td>";
	if ($row['group_submissions'] == '0') {
		$tool_content .= "$m[user_work]</td>
        </tr>";
	} else {
		$tool_content .= "$m[group_work]</td>
        </tr>";
	}
	$tool_content .= "
        </table>
        </fieldset>";
}

// Show a table header which is a link with the appropriate sorting
// parameters - $attrib should contain any extra attributes requered in
// the <th> tags
function sort_link($title, $opt, $attrib = '')
{
	global $tool_content;
	$i = '';
	if (isset($_REQUEST['id'])) {
		$i = "&id=$_REQUEST[id]";
	}
	if (@($_REQUEST['sort'] == $opt)) {
		if (@($_REQUEST['rev'] == 1)) {
			$r = 0;
		} else {
			$r = 1;
		}
		$tool_content .= "
      <td $attrib><a href='$_SERVER[PHP_SELF]?sort=$opt&rev=$r$i'>" ."$title</a></td>";
	} else {
		$tool_content .= "
      <td $attrib><a href='$_SERVER[PHP_SELF]?sort=$opt$i'>$title</a></td>";
	}
}


// show assignment - prof view only
// the optional message appears insted of assignment details
function show_assignment($id, $message = FALSE)
{
	global $tool_content, $m, $langBack, $langNoSubmissions, $langSubmissions, $mysqlMainDb, $langWorks;
	global $langEndDeadline, $langWEndDeadline, $langNEndDeadline, $langDays, $langDaysLeft, $langGradeOk;
	global $currentCourseID, $webDir, $urlServer, $nameTools, $langGraphResults, $m;

	$res = db_query("SELECT *, (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days FROM assignments WHERE id = '$id'");
	$row = mysql_fetch_array($res);

	$nav[] = array("url"=>"$_SERVER[PHP_SELF]", "name"=> $langWorks);
	if ($message) {
		assignment_details($id, $row, $message);
	} else {
		assignment_details($id, $row);
	}

	$rev = (@($_REQUEST['rev'] == 1))? ' DESC': '';
	if (isset($_REQUEST['sort'])) {
		if ($_REQUEST['sort'] == 'am') {
			$order = 'am';
		} elseif ($_REQUEST['sort'] == 'date') {
			$order = 'submission_date';
		} elseif ($_REQUEST['sort'] == 'grade') {
			$order = 'grade';
		} else {
			$order = 'nom';
		}
	} else {
		$order = 'nom';
	}

	$result = db_query("SELECT *
		FROM `$GLOBALS[code_cours]`.assignment_submit AS assign,
		`$mysqlMainDb`.user AS user
		WHERE assign.assignment_id='$id' AND user.user_id = assign.uid
		ORDER BY $order $rev");

	/*  The query is changed (AND assign.grade<>'' is appended) in order to constract the chart of 
	 * grades distribution according to the graded works only (works that are not graded are omitted). */
	$numOfResults = db_query("SELECT *
		FROM `$GLOBALS[code_cours]`.assignment_submit AS assign,
		`$mysqlMainDb`.user AS user
		WHERE assign.assignment_id='$id' AND user.user_id = assign.uid AND assign.grade<>''
		ORDER BY $order $rev");
	$num_resultsForChart = mysql_num_rows($numOfResults);
	
	$num_results = mysql_num_rows($result);
	if ($num_results > 0) {
		if ($num_results == 1) {
			$num_of_submissions = $m['one_submission'];
		} else {
			$num_of_submissions = sprintf("$m[more_submissions]", $num_results);
		}

                $chart = new PieChart(300, 200);
                $dataSet = new XYDataSet();
		$chart->setTitle("$langGraphResults");

		$gradeOccurances = array(); // Named array to hold grade occurances/stats
		$gradesExists = 0;
		while ($row = mysql_fetch_array($result)) {
			$theGrade = $row['grade'];
			if ($theGrade) {
				$gradesExists = 1;
			if (!isset($gradeOccurances[$theGrade])) {
					$gradeOccurances[$theGrade] = 1;
				} else {
					if ($gradesExists) {
						++$gradeOccurances[$theGrade];
					}
				}
			}
		}

		$result = db_query("SELECT *
                                FROM `$GLOBALS[code_cours]`.assignment_submit AS assign,
                                `$mysqlMainDb`.user AS user
                                WHERE assign.assignment_id='$id' AND user.user_id = assign.uid
                                ORDER BY $order $rev");

		$tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>
                <input type='hidden' name='grades_id' value='$id' />
                <br />
                <table class='tbl' width='99%'>
                <tr>
                  <th class='left' width='220'>$langSubmissions:</th>
                  <td>$num_of_submissions</td>
                </tr>
                </table>";
		$tool_content .= "<table width=\"99%\" class=\"tbl\">
                <tr>
                <td width=\"3\">&nbsp;</td>";
                sort_link($m['username'], 'nom', 'align=left');
                sort_link($m['am'], 'am', 'align=left');
                $tool_content .= "<td align=\"center\"><div class='left'><b>".$m['filename']."</b></div></td>";
                sort_link($m['sub_date'], 'date', 'align=center');
                sort_link($m['grade'], 'grade', 'align=center class=grade');
		$tool_content .= "</tr>";

		$i = 1;
		while ($row = mysql_fetch_array($result))
		{
			//is it a group assignment?
			if (!empty($row['group_id'])) {
				$subContentGroup = "($m[groupsubmit] ".
				"<a href='../group/group_space.php?group_id=$row[group_id]'>".
				"$m[ofgroup] ".gid_to_name($row['group_id'])."</a>)";
			} else $subContentGroup = "";

			//professor comments
			if (trim($row['grade_comments'] != '')) {
				$prof_comment = standard_text_escape($row['grade_comments']).
				" (<a href='grade_edit.php?assignment=$id&submission=$row[id]'>".
				"$m[edit]</a>)";
			} else {
				$prof_comment = "
				<a href='grade_edit.php?assignment=$id&submission=$row[id]'>".
				$m['comments']."</a> (+)";
			}
                        $uid_2_name = display_user($row['uid']);
			$stud_am = mysql_fetch_array(db_query("SELECT am from $mysqlMainDb.user WHERE user_id = '$row[uid]'"));
			$tool_content .= "
                        <tr>
                          <td align='right' width='4'>$i.</td>
                          <td>${uid_2_name} $subContentGroup</td>
                          <td width='75' align='left'>${stud_am[0]}</td>
                          <td width='180'><a href='$_SERVER[PHP_SELF]?get=$row[id]'>${row['file_name']}</a>";
                          
			if (trim($row['comments'] != '')) {
			    $tool_content .= "
                            <br />
                            <table align=\"left\" width=\"100%\"  class=\"tbl\">
                            <tbody>
                            <tr>
                              <td width=\"1\" class=\"left\"><img src='../../template/classic/img/forum_off.gif' alt='$m[comments]' title=\"$m[comments]\" /></td>
                              <td>$row[comments]</td>
                            <tr>
                            </tbody>
                            </table>";
			}
                    $tool_content .= "
                    </td>
                    <td width='75' align='center'>".nice_format($row['submission_date'])."</td>
                    <td width='180' align='left' class='grade'>
                        <div align='center'><input type='text' value='{$row['grade']}' maxlength='3' size='3' name='grades[{$row['id']}]' class='grade_input'></div>
                        <table align='left' width='100%' class='Info'>
                        <tbody>
                        <tr>
                          <td width='1' class='left'><img src='../../template/classic/img/forum_on.png' alt='$m[comments]' title='$m[comments]' /></td>
                          <td>$prof_comment</td>
                        <tr>
                        </table>
                    </td>
                  </tr>";
                $i++;
		} //END of While

	$tool_content .="</tbody></table>";

	$tool_content .= "<br />
            <table class='tbl' width='99%'>
            <tr>
              <th class='left' width='220'>&nbsp;</th>
              <td><input type='submit' name='submit_grades' value='$langGradeOk'></td>
            </tr>
            </tbody>
            </table>
            </form>";

		if ($gradesExists) {
			foreach ( $gradeOccurances as $gradeValue=>$gradeOccurance ) {
				/*  Changed by nikos. Only the number of works that are graded
				 * are taken into account to determine the grade distribution
				 * percentage. */
//				$percentage = 100*($gradeOccurance/$num_results);
				$percentage = 100*($gradeOccurance/$num_resultsForChart);
				$dataSet->addPoint(new Point("$gradeValue ($percentage)", $percentage));
			}

			$chart_path = 'courses/'.$currentCourseID.'/temp/chart_'.md5(serialize($chart)).'.png';
                        $chart->setDataSet($dataSet);
			$chart->render($webDir.$chart_path);
			$tool_content .= "
                        <table width='99%' class='tbl'>
                        <tr>
                          <td align='right'><img src='$urlServer$chart_path' /></td>
                        </tr>
                        </tbody>
                        </table>";
		}
	} else {
		$tool_content .= "<br /><table class='tbl' width='99%'>
                    <tr>
                <th class='left' width='220'>$langSubmissions:</th>
                <td class='empty'>$langNoSubmissions</td>
                </tr>
            </table>";
	}
	$tool_content .= "<br/><p align='right'><a href='$_SERVER[PHP_SELF]'>$langBack</a></p>";
}


// // show assignment - student view only
function show_student_assignments()
{
        global $tool_content, $m, $uid, $cours_id, $currentCourseID,
               $langDaysLeft, $langDays, $langNoAssign, $urlServer;

        $gids = user_group_info($uid, $cours_id);

        $result = db_query("SELECT *, (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days
                                   FROM `$currentCourseID`.assignments
                                   WHERE active = '1' ORDER BY submission_date");

        if (mysql_num_rows($result)) {
                $tool_content .= "<table class='tbl_alt' width='99%'>
                                  <tr>
                                      <th colspan='2'><div align='left'>&nbsp;&nbsp;$m[title]</div></th>
                                      <th><div align='left'>$m[deadline]</div></th>
                                      <th><div align='center'>$m[submitted]</div></th>
                                      <th>$m[grade]</th>
                                  </tr>";
                $k = 0;
                while ($row = mysql_fetch_array($result)) {
                        $title_temp = q($row['title']);
                        if ($k%2 == 0) {
                                $tool_content .= "\n
                                  <tr class='even'>";
                        } else {
                                $tool_content .= "\n
                                  <tr class='odd'>";
                        }
                        $tool_content .= "
                                    <td width='1'><img style='padding-top:3px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet' /></td>
                                    <td><a href='$_SERVER[PHP_SELF]?id=$row[id]'>$title_temp</a></td>
                                    <td width='30%'>".nice_format($row['deadline']);
                        if ($row['days'] > 1) {
                                $tool_content .= " (<span class='not_expired'>$m[in]&nbsp;$row[days]&nbsp;$langDays</span>";
                        } elseif ($row['days'] < 0) {
                                $tool_content .= " (<span class='expired'>$m[expired]</span>)";
                        } elseif ($row['days'] == 1) {
                                $tool_content .= " (<span class='expired_today'>$m[tomorrow]</span>)";
                        } else {
                                $tool_content .= " (<span class='expired_today'><b>$m[today]</b></span>)";
                        }
                        $tool_content .= "</td>
                                    <td width='25%' align='center'>";
                        
                        if ($submission = find_submissions(is_group_assignment($row['id']), $uid, $row['id'], $gids)) {
                            foreach ($submission as $sub) {
                                if (isset($sub['group_id'])) { // if is a group assignment
                                    $tool_content .= "<div style='padding-bottom: 5px;padding-top:5px;font-size:9px;'>($m[groupsubmit] ".
                                        "<a href='../group/group_space.php?group_id=$sub[group_id]'>".
                                        "$m[ofgroup] ".gid_to_name($sub['group_id'])."</a>)</div>";
                                }
                                $tool_content .= "<img src='../../template/classic/img/checkbox_on.gif' alt='$m[yes]' /><br />";
                            }
                        } else {
                                $tool_content .= "<img src='../../template/classic/img/checkbox_off.gif' alt='$m[no]' />";
                        }
                        $tool_content .= "</td>
                                    <td width='5%' align='center'>";
                        foreach ($submission as $sub) {
                            $grade = submission_grade($sub['id']);
                                if (!$grade) {                
                                    $grade = "<div style='padding-bottom: 5px;padding-top:5px;'> - </div>";
                                }
                            $tool_content .= "<div style='padding-bottom: 5px;padding-top:5px;'>$grade</div>";
                        }
                        $tool_content .= "</td>
                                  </tr>";
                        $k++;
                }
                $tool_content .= '
                                  </table>';
        } else {
                $tool_content .= "<p class='alert1'>$langNoAssign</p>";
        }
}


// show all the assignments
function show_assignments($message = null)
{
	global $tool_content, $m, $langNoAssign, $langNewAssign, $langCommands, $urlServer;

	$result = db_query("SELECT * FROM assignments ORDER BY id");

	if (isset($message)) {
		$tool_content .="<p class='success'>$message</p><br />";
	}

	$tool_content .="
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='$_SERVER[PHP_SELF]?add=1'>$langNewAssign</a></li>
      </ul>
    </div>";


	if (mysql_num_rows($result)) {

		$tool_content .= <<<cData

    <table width="99%" class="tbl_alt" align="left">
    <tr>
      <th colspan="2"><div align="left">&nbsp;&nbsp;&nbsp;&nbsp;${m['title']}</div></th>
      <th width="150">${m['deadline']}</th>
      <th width="110"><div align="right">$langCommands &nbsp;</div></th>
    </tr>
cData;
       $index = 0;
		while ($row = mysql_fetch_array($result)) {
			// Check if assignement contains unevaluatde (incoming) submissions
			$AssignementId = $row['id'];
			$result_s = db_query("SELECT COUNT(*) FROM assignment_submit WHERE assignment_id='$AssignementId' AND grade=''");
			$row_s = mysql_fetch_array($result_s);
			$hasUnevaluatedSubmissions = $row_s[0];
			if(!$row['active']) {
				$visibility_css = "style='color: #CAC3B5;'";
				$visibility_image = "arrow_red";
			} else {
				$visibility_css = "";
				$visibility_image = "arrow_grey";
			}
			    if ($index%2==0) {
	                       $tool_content .= "\n    <tr class='even' ".$visibility_css.">";
	                    } else {
	                       $tool_content .= "\n    <tr class='odd' ".$visibility_css.">";
                        }

			$tool_content .= "
       <td width='1'><img style='border:0px; padding-top:3px;' src='$urlServer/template/classic/img/$visibility_image.gif' title='bullet' /></td>
       <td ".$visibility_css."><a href='$_SERVER[PHP_SELF]?id=${row['id']}' ";
			$tool_content .= ">";
			$tool_content .= $row_title = q($row['title']);
			$tool_content .= "</a></td>
       <td class='center'>".nice_format($row['deadline'])."</td>
       <td class='right'><a href='$_SERVER[PHP_SELF]?id=$row[id]&amp;choice=edit'><img src='../../template/classic/img/edit.png' alt='$m[edit]' /></a> <a href='$_SERVER[PHP_SELF]?id=$row[id]&amp;choice=do_delete' onClick='return confirmation(\"".addslashes($row_title)."\");'><img src='../../template/classic/img/delete.png' alt='$m[delete]' /></a>";

			if ($row['active']) {
				$deactivate_temp = htmlspecialchars($m['deactivate']);
				$activate_temp = htmlspecialchars($m['activate']);
				$tool_content .= "<a href='$_SERVER[PHP_SELF]?choice=disable&amp;id=$row[id]'><img src='../../template/classic/img/visible.png' title='$deactivate_temp' /></a>";
			} else {
				$activate_temp = htmlspecialchars($m['activate']);
				$tool_content .= "<a href='$_SERVER[PHP_SELF]?choice=enable&amp;id=$row[id]'><img src='../../template/classic/img/invisible.png' title='$activate_temp' /></a>";
			}
			$tool_content .= "&nbsp;</td></tr>";
                        $index++;
                }
                $tool_content .= '
    </table>';
        } else {
                $tool_content .= "\n    <p class=\"alert1\">$langNoAssign</p>";
        }
}


// submit grade and comment for a student submission
function submit_grade_comments($id, $sid, $grade, $comment)
{
	global $tool_content, $langGrades, $langWorkWrongInput;
    
	$stupid_user = 0;

	/*  If check expression is changed by nikos, in order to give to teacher the ability to 
	 * assign comments to a work without assigning grade. */
	if (!is_numeric($grade) && '' != $grade ) {
		$tool_content .= $langWorkWrongInput;
		$stupid_user = 1;
	} else {
		db_query("UPDATE assignment_submit SET grade='$grade', grade_comments='$comment',
                    grade_submission_date=NOW(), grade_submission_ip='$_SERVER[REMOTE_ADDR]'
                    WHERE id = '$sid'");
	}
	if (!$stupid_user) {
		show_assignment($id, $langGrades);
	}
}


// submit grades to students
function submit_grades($grades_id, $grades)
{
	global $tool_content, $langGrades, $langWorkWrongInput;

	$stupid_user = 0;

	foreach ($grades as $sid => $grade) {
		$val = mysql_fetch_row(db_query("SELECT grade from assignment_submit WHERE id = '$sid'"));
		if ($val[0] != $grade) {
			/*  If check expression is changed by nikos, in order to give to teacher
			 * the ability to assign comments to a work without assigning grade. */
			if (!is_numeric($grade) && '' != $grade) {
        			$stupid_user = 1;
                        }
		}
	}

	if (!$stupid_user) {
		foreach ($grades as $sid => $grade) {
			$val = mysql_fetch_row(db_query("SELECT grade from assignment_submit WHERE id = '$sid'"));
			if ($val[0] != $grade) {
				db_query("UPDATE assignment_submit SET grade='$grade',
					    grade_submission_date=NOW(), grade_submission_ip='$_SERVER[REMOTE_ADDR]'
					    WHERE id = '$sid'");
			}
		}
		show_assignment($grades_id, $langGrades);
	} else {
		$tool_content .= $langWorkWrongInput;
	}
}

// functions for downloading
function send_file($id)
{
        global $tool_content, $currentCourseID;
        mysql_select_db($currentCourseID);
        $info = mysql_fetch_array(mysql_query("SELECT * FROM assignment_submit WHERE id = '$id'"));
        // Add quotes to filename if it contains spaces
        if (strpos($info[file_name], ' ') !== false) {
                $info['file_name'] = '"' . $info['file_name'] . '"';
        }
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=".basename($info['file_name']));
        ob_end_flush();
        readfile("$GLOBALS[workPath]/$info[file_path]");
        exit();
}


// Zip submissions to assignment $id and send it to user
function download_assignments($id)
{
	global $tool_content, $workPath;

	$secret = work_secret($id);
	$filename = "$GLOBALS[currentCourseID]_work_$id.zip";
	chdir($workPath);
	create_zip_index("$secret/index.html", $id);
	$zip = new PclZip($filename);
	$flag = $zip->create($secret, "work_$id", $secret);
	header("Content-Type: application/x-zip");
	header("Content-Disposition: attachment; filename=$filename");
	readfile($filename);
	unlink($filename);
	exit();
}


// Create an index.html file for assignment $id listing user submissions
// Set $online to TRUE to get an online view (on the web) - else the
// index.html works for the zip file
function create_zip_index($path, $id, $online = FALSE)
{
	global $tool_content, $charset, $m;

	$fp = fopen($path, "w");
	if (!$fp) {
		die("Unable to create assignment index file - aborting");
	}
	fputs($fp, '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">
	</head>
	<body>
		<table width="95%" class="tbl">
			<tr>
				<th>'.$m['username'].'</th>
				<th>'.$m['am'].'</th>
				<th>'.$m['filename'].'</th>
				<th>'.$m['sub_date'].'</th>
				<th>'.$m['grade'].'</th>
			</tr>');

	$result = db_query("SELECT * FROM assignment_submit
		WHERE assignment_id='$id' ORDER BY id");


	while ($row = mysql_fetch_array($result)) {
		$filename = basename($row['file_path']);
		fputs($fp, '
			<tr>
				<td>'.uid_to_name($row['uid']).'</td>
				<td>'.uid_to_am($row['uid']).'</td>
				<td align="center"><a href="'.$filename.'">'.
		htmlspecialchars($filename).'</a></td>
				<td align="center">'.$row['submission_date'].'</td>
				<td align="center">'.$row['grade'].'</td>
			</tr>');
		if (trim($row['comments'] != '')) {
			fputs($fp, "
			<tr><td colspan='6'><b>$m[comments]: ".
			"</b>$row[comments]</td></tr>");
		}
		if (trim($row['grade_comments'] != '')) {
			fputs($fp, "
			<tr><td colspan='6'><b>$m[gradecomments]: ".
			"</b>$row[grade_comments]</td></tr>");
		}
		if (!empty($row['group_id'])) {
			fputs($fp, "<tr><td colspan='6'>$m[groupsubmit] ".
                                   "$m[ofgroup] $row[group_id]</td></tr>\n");
		}
	}
	fputs($fp, ' </table></body></html>');
	fclose($fp);
}


// Show a simple html page with grades and submissions
function show_plain_view($id)
{
	global $tool_content, $workPath, $charset;
	$secret = work_secret($id);
	create_zip_index("$secret/index.html", $id, TRUE);
	header("Content-Type: text/html; charset=$charset");
	readfile("$workPath/$secret/index.html");
	exit;
}
