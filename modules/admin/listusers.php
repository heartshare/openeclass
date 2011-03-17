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

	listusers.php
	@last update: 27-06-2006 by Karatzidis Stratos
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
*/

$require_admin = TRUE;
include '../../include/baseTheme.php';
include 'admin.inc.php';

$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$nameTools = $langListUsersActions;

define ('USERS_PER_PAGE', 15);

// get the incoming values
$search = isset($_GET['search'])? $_GET['search']: '';
$c = isset($_REQUEST['c'])? $_REQUEST['c']:'';
$user_surname = isset($_REQUEST['user_surname'])?$_REQUEST['user_surname']:'';
$user_firstname = isset($_REQUEST['user_firstname'])?$_REQUEST['user_firstname']:'';
$user_username = isset($_REQUEST['user_username'])?$_REQUEST['user_username']:'';
$user_am = isset($_REQUEST['user_am'])?$_REQUEST['user_am']:'';
$user_type = isset($_REQUEST['user_type'])?$_REQUEST['user_type']:'';
$user_email = isset($_REQUEST['user_email'])?$_REQUEST['user_email']:'';
$user_registered_at_flag = isset($_REQUEST['user_registered_at_flag'])?$_REQUEST['user_registered_at_flag']:'';
$hour = isset($_REQUEST['hour'])?$_REQUEST['hour']:'';
$minute = isset($_REQUEST['minute'])?$_REQUEST['minute']:'';

switch($c)	// get the case for each different listing
{
	case '': $view = 1; break; // normal listing
	case 'inactive': $view = 1; break; // normal listing. Display the inactive accounts
	case 'searchlist': $view = 2;	break; // search listing (search_user.php)
	default: $c = intval($c); $view = 3; break; // list per course
}

if($view == 2)	// coming from search_user.php(search with criteria)
{
	if((!empty($search)) && ($search="yes"))
	{
	  	$date = explode('-',  $_POST['date']);
		if (array_key_exists(1, $date)) {
    			$day = $date[0];
		    	$month = $date[1];
    			$year = $date[2];
		    	$mytime = mktime($hour, $minute, 0, $month, $day, $year);
		 } else {
		    	$mytime = mktime($hour, $minute, 0, 0, 0, 0);
		}
		if(!empty($mytime)) {
			$user_registered_at = $mytime;
		} else {
			$user_registered_at = '';
		}
		// end format date/time
	}
}

// Display Actions Toolbar
$tool_content .= "
  <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='search_user.php'>$langSearchUser</a></li>
      <li><a href='listusers.php?c=inactive'>$langInactiveUsers</a></li>
    </ul>
  </div>";


/***************
Criteria/Filters
***************/
$criteria = 0;
// surname search
if(!empty($user_surname))
{
	$user_surname_qry = " nom LIKE " . autoquote('%' . $user_surname . '%');
	$criteria++;
}
else
{
	$user_surname_qry = "";
}

// first name search
if(!empty($user_firstname))
{
	if($criteria!=0)
	{
		$user_firstname_qry = " AND";
	}
	else
	{
		$user_firstname_qry = "";
	}
	$criteria++;
	$user_firstname_qry .= " prenom LIKE " . autoquote('%' . $user_firstname . '%');
}
else
{
	$user_firstname_qry = "";
}

// username search
if(!empty($user_username))
{
	if($criteria!=0)
	{
		$user_username_qry = " AND";
	}
	else
	{
		$user_username_qry = "";
	}
	$criteria++;
	$user_username_qry .= " username LIKE " . autoquote('%' . $user_username . '%');
}
else
{
	$user_username_qry = "";
}

//user am search
if(!empty($user_am))
{
	if($criteria!=0)
	{
		$user_am_qry = " AND";
	}
	else
	{
		$user_am_qry = "";
	}
	$criteria++;
	$user_am_qry .= " am=" . autoquote($user_am);
}
else
{
	$user_am_qry = "";
}

// user type search
if(!empty($user_type))
{
	if($criteria!=0)
	{
		$user_type_qry = " AND";
	}
	else
	{
		$user_type_qry = "";
	}
	$criteria++;
	$user_type_qry .= " statut=" . intval($user_type);
}
else
{
	$user_type_qry = "";
}


// email search
if(!empty($user_email))
{
	if($criteria!=0)
	{
		$user_email_qry = " AND";
	}
	else
	{
		$user_email_qry = "";
	}
	$criteria++;
	$user_email_qry .= " email LIKE " . autoquote('%' . $user_email . '%');
}
else
{
	$user_email_qry = "";
}

// join the above with registered at search
if(!empty($user_registered_at_flag))
{
	if($criteria!=0)
	{
		$user_registered_at_qry = " AND";
		$criteria++;
	}
	else
	{
		$user_registered_at_qry = "";
	}
	$user_registered_at_qry .= " registered_at";
	switch($user_registered_at_flag)
	{
		case 1:	$user_registered_at_qry .= " >="; break;
		case 2: $user_registered_at_qry .= " <="; break;
		default: $user_registered_at_qry .= " <="; break;
	}
	if(!empty($user_registered_at))
	{
		$user_registered_at_qry .= $user_registered_at;
	}
	else
	{
		$user_registered_at_qry = "";
	}
}
else
{
	$user_registered_at_qry = "";
}


if($c=='inactive')
{
	if($criteria!=0)
	{
		$users_active_qry = " AND";
		$criteria++;
	}
	else
	{
		$users_active_qry = "";
	}
	$users_active_qry .= " expires_at<".time()." AND user_id<>1";
}
else
{
	$users_active_qry = "";
}
// end filter/criteria

$ord = isset($_GET['ord'])?$_GET['ord']:'';
$limit = isset($_GET['limit'])?$_GET['limit']:0;

if(!empty($ord)) {
	switch ($ord) {
		case "s": $order = "statut,prenom,nom"; break;
		case "n": $order = "nom,prenom,statut"; break;
		case "p": $order = "prenom,nom,statut"; break;
		case "u": $order = "username,statut,prenom"; break;
		default: $order = "statut,prenom,nom"; break;
	}
} else {
	$order = "statut";
}

$caption = "";

if($view == 3) { // users per course
	$qry = "SELECT a.user_id, a.nom, a.prenom, a.username, a.email, b.statut
		FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
		WHERE b.cours_id = $c";
		if((!empty($user_surname_qry)) || (!empty($user_firstname_qry)) || (!empty($user_username_qry)) 
			|| (!empty($user_am_qry)) || (!empty($user_type_qry)) || (!empty($user_registered_at_qry)) 
			|| (!empty($user_email_qry)) || (!empty($users_active_qry)))
		{
			$qry .= " WHERE".$user_surname_qry.$user_firstname_qry.$user_username_qry.$user_am_qry.$user_type_qry.$user_email_qry.$user_registered_at_qry.$users_active_qry;
		}
} else {
	// Count users, with or without criteria/filters
	$qry = "SELECT user_id,nom,prenom,username,email,statut FROM user";
	if((!empty($user_surname_qry)) || (!empty($user_firstname_qry)) || (!empty($user_username_qry)) 
		|| (!empty($user_am_qry)) || (!empty($user_type_qry)) || (!empty($user_registered_at_qry)) 
		|| (!empty($user_email_qry)) || (!empty($users_active_qry)))
	{
		$qry .= " WHERE".$user_surname_qry.$user_firstname_qry.$user_username_qry.$user_am_qry.$user_type_qry.$user_email_qry.$user_registered_at_qry.$users_active_qry;
	}
}

$sql = db_query($qry);

if($sql) {
	$countUser = mysql_num_rows($sql);
	$teachers = 0;
	$students = 0;
	$visitors = 0;
	$other = 0;
	while($numrows = mysql_fetch_array($sql, MYSQL_ASSOC))
	{
		switch ($numrows['statut'])
		{
			case 1:	$teachers++; break;
 			case 5:	$students++; break;
			case 10: $visitors++; break;
 			default: $other++; break;
		}
	}

	if($countUser>0)
	{
		$caption .= "$langThereAre: <b>$teachers</b> $langTeachers, <b>$students</b> $langStudents 
			$langAnd <b>$visitors</b> $langVisitors<br />";
		$caption .= "$langTotal: <b>$countUser</b> $langUsers<br />";
		if($c == 'inactive') {
			$caption .= "&nbsp;$langAsInactive<br />";
			$caption .= "<a href='updatetheinactive.php?activate=1'>".$langAddSixMonths."</a><br />";
		} else {
			$caption .= " ";
		}
		
		@$str = "&amp;user_surname=$user_surname&amp;user_firstname=$user_firstname&amp;user_username=$user_username&amp;user_am=$user_am&amp;user_email=$user_email&amp;user_type=$user_type&amp;user_registered_at_flag=$user_registered_at_flag";

		if ($countUser >= USERS_PER_PAGE) { // display navigation links if more than USERS_PER_PAGE
			$tool_content .= show_paging($limit, USERS_PER_PAGE, $countUser, "$_SERVER[PHP_SELF]", "$str");
		}  

		if($view == 3) {
			$qry = "SELECT a.user_id,a.nom, a.prenom, a.username, a.email, b.statut
			FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
			WHERE b.cours_id=$c";
			if((!empty($user_surname_qry)) || (!empty($user_firstname_qry))
				|| (!empty($user_username_qry)) || (!empty($user_am_qry))
				|| (!empty($user_type_qry)) || (!empty($user_registered_at_qry))
				|| (!empty($user_email_qry)) || (!empty($users_active_qry)))
			{
				$qry .= " WHERE".$user_surname_qry.$user_firstname_qry.$user_username_qry.$user_am_qry.$user_type_qry.$user_email_qry.$user_registered_at_qry.$users_active_qry;
			}
		} else {
			$qry = "SELECT user_id,nom,prenom,username,email,statut FROM user";
			if((!empty($user_surname_qry)) || (!empty($user_firstname_qry))
				|| (!empty($user_username_qry)) || (!empty($user_am_qry))
				|| (!empty($user_type_qry)) || (!empty($user_registered_at_qry))
				|| (!empty($user_email_qry)) || (!empty($users_active_qry)))
			{
				$qry .= " WHERE".$user_surname_qry.$user_firstname_qry.$user_username_qry.$user_am_qry.$user_type_qry.$user_email_qry.$user_registered_at_qry.$users_active_qry;
			}
		}

		$qry .= " ORDER BY $order LIMIT $limit, ".USERS_PER_PAGE."";
		mysql_free_result($sql);
		$sql = db_query($qry);
		
		if ($view == 3) {
			$str .= "&amp;c=$c";	
		}
		$tool_content .= "
		<table class='tbl_alt' width='100%'>
		<tr>
		  <th colspan='10'><div align='right'>".$caption."</div></th>
		</tr>
		<tr>
		  <th width='1'>&nbsp;</th>
		  <th scope='col'><div align='left'><a href='$_SERVER[PHP_SELF]?ord=n$str'>$langSurname</a></div></th>
		  <th><div align='left'><a href='$_SERVER[PHP_SELF]?ord=p$str'>$langName</a></div></th>
		  <th><div align='left'><a href='$_SERVER[PHP_SELF]?ord=u$str'>$langUsername</a></div></th>
		  <th scope='col'>$langEmail</th>
		  <th scope='col'><a href='$_SERVER[PHP_SELF]?ord=s$str'>$langProperty</a></th>
		  <th scope='col' colspan='4'>$langActions</th>
		</tr>";
        	$k = 0;
		for ($j = 0; $j < mysql_num_rows($sql); $j++) {
			while($logs = mysql_fetch_array($sql, MYSQL_ASSOC)) {
				if ($k%2 == 0) {
		              		$tool_content .= "\n      <tr class='even'>";
	            		} else {
		                	$tool_content .= "\n      <tr class='odd'>";
	            		}
				$tool_content .= "<td width='1'>
					<img style='border:0px; margin:4px;' src='${urlServer}/template/classic/img/arrow.png' title='bullet' /></td>
					<td>".htmlspecialchars($logs['nom'])."</td>
					<td>".htmlspecialchars($logs['prenom'])."</td>
					<td>".htmlspecialchars($logs['username'])."</td>
					<td>".htmlspecialchars($logs['email'])."</td>
					<td align='center'>";
				switch ($logs['statut'])
				{
					case 1:	$tool_content .= "<img src='../../template/classic/img/teacher.png' title='$langTeacher' />";break;
	   				case 5:	$tool_content .= "<img src='../../template/classic/img/student.png' title='$langStudent' />";break;
					case 10: $tool_content .= "<img src='../../template/classic/img/guest.png' title='$langVisitor' />";break;
	   				default: $tool_content .= "$langOther ($logs[6])";break;
				}
				$tool_content .= "</td>
					<td><a href=\"edituser.php?u=".$logs['user_id']."\">
					<img src='../../template/classic/img/edit.png' title='$langEdit' /></a>
					</td>
					<td><a href='unreguser.php?u=".$logs['user_id']."'>
					<img src='../../template/classic/img/delete.png' title='$langDelete' />
					</a></td>
					<td align='center'><a href='userstats.php?u=".$logs['user_id']."'>
					<img src='../../template/classic/img/platform_stats.png' title='$langStat' /></a>
					</td>
					<td align='center'><a href='change_user.php?username=".$logs['username']."'>
					<img src='../../template/classic/img/log_as.png' title='$langChangeUserAs $logs[username]' /></a>
					</td>\n";
				$tool_content .= "</tr>";
				$k++;
			}
		}
		$tool_content .= "</table>";
		if ($countUser >= USERS_PER_PAGE) { // display navigation links if more than USERS_PER_PAGE
			$tool_content .= show_paging($limit, USERS_PER_PAGE, $countUser, "$_SERVER[PHP_SELF]", "$str");
		}  
	} else {
		$tool_content .= "<p class='caution'>$langNoSuchUsers</p>";
	}
} else {
	$tool_content .= "<br />$langNoUserList<br />";
}
$tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";

draw($tool_content, 3);
