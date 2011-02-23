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
* =========================================================================*/

if (isset($c) && ($c!="")) {
	session_start();
	$require_admin = TRUE;
	$_SESSION['dbname'] = $c;
}

$require_current_course = TRUE;
$require_prof = TRUE;
include '../../include/baseTheme.php';
include '../../include/lib/fileManageLib.inc.php';

$nameTools = $langArchiveCourse;
$navigation[] = array("url" => "infocours.php", "name" => $langModifInfo);
$tool_content = "";
$archiveDir = "/courses/archive";

if (extension_loaded("zlib")) {
	include("../../include/pclzip/pclzip.lib.php");
}

if ($is_adminOfCourse) {
        $basedir = "${webDir}courses/archive/$currentCourseID";
	mkpath($basedir);
        cleanup($basedir, 60);

	$backup_date = date("Y-m-d-H-i-(B)-s");
	$backup_date_short = date("YzBs"); // YEAR - Day in Year - Swatch - second

	$archivedir = $basedir . '/' . $backup_date;
	mkpath($archivedir);

	$zipfile = $basedir . "/archive.$currentCourseID.$backup_date_short.zip";
	$tool_content .= "<table class='tbl' align='center'><tbody><tr><th align='left'><ol>\n";

	// creation of the sql queries will all the data dumped
	create_backup_file($archivedir . '/backup.php');

    	$htmldir = $archivedir . '/html';

	$tool_content .= "<li>".$langBUCourseDataOfMainBase."  ".$currentCourseID."</li>\n";

	// Copy course files
	$nbFiles = copydir("../../courses/$currentCourseID", $htmldir);
        $tool_content .= "<li>$langCopyDirectoryCourse<br />
                              (<strong>$nbFiles</strong> $langFileCopied)</li>
                          <li>$langBackupOfDataBase $currentCourseID</li></ol></th>
                          <td>&nbsp;</td></tr></tbody></table>";

        // create zip file
	$zipCourse = new PclZip($zipfile);
	if ($zipCourse->create($archivedir, PCLZIP_OPT_REMOVE_PATH, $webDir) == 0) {
		$tool_content .= "Error: ".$zipCourse->errorInfo(true);
		draw($tool_content, 2);
		exit;
	} else {
		$tool_content .= "<br /><p class='success_small'>$langBackupSuccesfull</p><div align=\"left\"><a href='$urlAppend/courses/archive/$currentCourseID/archive.$currentCourseID.$backup_date_short.zip'>$langDownloadIt <img src='../../template/classic/img/download.png' title='$langDownloadIt' alt=''></a></div>";
	}

	$tool_content .= "<p align=\"right\">";
	if (isset($c) && ($c!="")) {
		if (isset($search) && ($search=="yes")) $searchurl = "&search=yes";
		else $searchurl = "";
		$tool_content .= "<a href=\"../admin/editcours.php?c=".$c."".$searchurl."\">$langBack</a>";
	} else {
		$tool_content .= "<a href=\"infocours.php\">$langBack</a>";
	}
	$tool_content .= "</p>";

	draw($tool_content, 2, 'course_info');
}	// end of isadminOfCourse
else
{
	$tool_content .= "<center><p>$langNotAllowed</p></center>";
	draw($tool_content, 2, 'course_info');
	exit;
}

// ---------------------------------------------
// useful functions
// ---------------------------------------------

function copydir($origine, $destination) {

	$dossier=opendir($origine);
	if (file_exists($destination))
	{
		return 0;
	}
	mkdir($destination, 0755);
	$total = 0;

	while ($fichier = readdir($dossier))
	{
		$l = array('.', '..');
		if (!in_array( $fichier, $l))
		{
			if (is_dir($origine."/".$fichier))
			{
				$total += copydir("$origine/$fichier", "$destination/$fichier");
			}
			else
			{
				copy("$origine/$fichier", "$destination/$fichier");
                                touch("$destination/$fichier", filemtime("$origine/$fichier"));
				$total++;
			}
		}
	}
	return $total;
}



function create_backup_file($file) {
	global $currentCourseID, $cours_id, $mysqlMainDb;

	$f = fopen($file,"w");
	if (!$f) {
		die("Error! Unable to open output file: '$f'\n");
	}
	list($ver) = mysql_fetch_array(db_query("SELECT `value` FROM `$mysqlMainDb`.config WHERE `key`='version'"));
	fputs($f, "<?php\n\$eclass_version = '$ver';\n\$version = 2;\n\$encoding = 'UTF-8';\n");
	backup_course_details($f, $currentCourseID);
	backup_annonces($f, $cours_id);
	backup_course_units($f);
	backup_users($f, $cours_id);
	backup_course_db($f, $currentCourseID);
	fputs($f, "?>\n");
	fclose($f);
}

function backup_annonces($f, $cours_id) {
	global $mysqlMainDb;

	$res = db_query("SELECT * FROM `$mysqlMainDb`.annonces
				    WHERE cours_id = $cours_id");
	while($q = mysql_fetch_array($res)) {
		fputs($f, "announcement(".
			inner_quote($q['contenu']).",\n".
			inner_quote($q['temps']).", ".
			inner_quote($q['ordre']).", ".
			inner_quote($q['title']).");\n");
	}
}

function backup_course_units($f) {
	global $mysqlMainDb, $cours_id;
	
	$res = db_query("SELECT * FROM `$mysqlMainDb`.course_units
				    WHERE course_id = $cours_id");
	while($q = mysql_fetch_array($res)) {
		fputs($f, "course_units(".
			inner_quote($q['title']).", ".
			inner_quote($q['comments']).", ".
			inner_quote($q['visibility']).", ".
			inner_quote($q['order']).", array(");
		$res2 = db_query("SELECT * FROM unit_resources WHERE unit_id = $q[id]", $mysqlMainDb);
		$begin = true;
		while($q2 = mysql_fetch_array($res2)) {
			if ($begin) {
				$begin = !$begin;
				fputs($f, "\n");
			} else {
				fputs($f, ",\n");
			}
			fputs($f, "array(".
			inner_quote($q2['title']).", ".
			inner_quote($q2['comments']).", ".
			inner_quote($q2['res_id']).", ".
			inner_quote($q2['type']).", ".
			inner_quote($q2['visibility']).", ".
			inner_quote($q2['order']).", ".
			inner_quote($q2['date']).")");
		}
		fputs($f,"));\n");
	}
}


function backup_groups($f) {
	$res = db_query("SELECT * FROM user_group");
		while($row = mysql_fetch_assoc($res)) {
		fputs($f, "group(".
			$row['user'].", ".
			$row['team'].", ".
			$row['status'].", ".
			inner_quote($row['role']).");\n");
	}
}

function backup_assignment_submit($f) {
	$res = db_query("SELECT * FROM assignment_submit");
		while($row = mysql_fetch_assoc($res)) {
		$values = array();
		foreach (array('assignment_id', 'submission_date',
			'submission_ip', 'file_path', 'file_name', 'comments',
			'grade', 'grade_comments', 'grade_submission_date',
			'grade_submission_ip') as $field) {
			$values[] = inner_quote($row[$field]);
		}
		fputs($f, "assignment_submit($row[uid], ".
			join(", ", $values).
			");\n");
	}
}


function backup_dropbox_file($f) {
	$res = db_query("SELECT * FROM dropbox_file");
	while ($row = mysql_fetch_array($res)) {
		fputs ($f, "dropbox_file(".
			inner_quote($row['uploaderId']).", ".
			inner_quote($row['filename']).", ".
			inner_quote($row['filesize']).", ".
			inner_quote($row['title']).", ".
			inner_quote($row['description']).", ".
			inner_quote($row['author']).", ".
			inner_quote($row['uploadDate']).", ".
			inner_quote($row['lastUploadDate']).");\n");
		}
}

function backup_dropbox_person($f) {
	$res = db_query("SELECT * FROM dropbox_person");
	while ($row = mysql_fetch_array($res)) {
		fputs ($f, "dropbox_person(".
			inner_quote($row['fileId']).", ".
			inner_quote($row['personId']).");\n");
		}
}

function backup_dropbox_post($f) {
	$res = db_query("SELECT * FROM dropbox_post");
	while ($row = mysql_fetch_array($res)) {
		fputs ($f, "dropbox_post(".
			inner_quote($row['fileId']).", ".
			inner_quote($row['recipientId']).");\n");
	}
}


function backup_users($f, $cours_id) {
	global $mysqlMainDb;

	$res = db_query("SELECT user.*, cours_user.statut as cours_statut
		FROM `$mysqlMainDb`.user, `$mysqlMainDb`.cours_user
		WHERE user.user_id=cours_user.user_id
		AND cours_user.cours_id = $cours_id");
	while($q = mysql_fetch_array($res)) {
		fputs($f, "user(".
			inner_quote($q['user_id']).", ".
			inner_quote($q['nom']).", ".
			inner_quote($q['prenom']).", ".
			inner_quote($q['username']).", ".
			inner_quote($q['password']).", ".
			inner_quote($q['email']).", ".
			inner_quote($q['cours_statut']).", ".
			inner_quote($q['phone']).", ".
			inner_quote($q['department']).", ".
			inner_quote($q['registered_at']).", ".
			inner_quote($q['expires_at']).");\n");
	}
}

function backup_course_db($f, $course) {
	mysql_select_db($course);

	$res_tables = db_query("SHOW TABLES FROM `$course`");
	while ($r = mysql_fetch_row($res_tables)) {
		$tablename = $r[0];
		fwrite($f, "query(\"DROP TABLE IF EXISTS `$tablename`\");\n");
		$res_create = mysql_fetch_array(db_query("SHOW CREATE TABLE $tablename"));
		$schema = $res_create[1];
		fwrite($f, "query(\"$schema\");\n");
		if ($tablename == 'user_group') {
			backup_groups($f);
		} elseif ($tablename == 'assignment_submit') {
			backup_assignment_submit($f);
		} elseif ($tablename == 'dropbox_file') {
			backup_dropbox_file($f);
		} elseif ($tablename == 'dropbox_person') {
			backup_dropbox_person($f);
		} elseif ($tablename == 'dropbox_post') {
			backup_dropbox_post($f);
		} else {
			$res = db_query("SELECT * FROM $tablename");
			if (mysql_num_rows($res) > 0) {
				$fieldnames = "";
				$num_fields = mysql_num_fields($res);
				for($j = 0; $j < $num_fields; $j++) {
					$fieldnames .= "`".mysql_field_name($res, $j)."`";
					if ($j < ($num_fields - 1)) {
						$fieldnames .= ', ';
					}
				}
				$insert = "query(\"INSERT INTO `$tablename` ($fieldnames) VALUES\n";
				$counter = 1;
				while($rowdata = mysql_fetch_row($res)) {
					if (($counter % 30) == 1) {
						if ($counter > 1) {
							fputs($f, "\n\");\n");
						}
						fputs($f, $insert."\t(");
					} else {
						fputs($f, ",\n\t(");
					}
					$counter++;
					for ($j = 0; $j < $num_fields; $j++) {
						fputs($f, inner_quote($rowdata[$j]));
						if ($j < ($num_fields - 1)) {
							fputs($f, ', ');
						}
					}
					fputs($f, ')');
				}
				fputs($f, "\n\");\n");
			}
		}
	}
}


function backup_course_details($f, $course) {
	global $mysqlMainDb;

	$res = db_query("SELECT * FROM `$mysqlMainDb`.cours
                                  WHERE code = '$course'");
	$q = mysql_fetch_array($res);
	fputs($f, "course_details('$course',\t// Course code\n\t".
		inner_quote($q['languageCourse']).",\t// Language\n\t".
		inner_quote($q['intitule']).",\t// Title\n\t".
		inner_quote($q['description']).",\t// Description\n\t".
		inner_quote($q['faculte']).",\t// Faculty\n\t".
		inner_quote($q['visible']).",\t// Visible?\n\t".
		inner_quote($q['titulaires']).",\t// Professor\n\t".
		inner_quote($q['type']).");\t// Type\n");
}


function inner_quote($s)
{
        return "'" . str_replace(array('\\', '\'', '"', "\0"),
                array('\\\\', '\\\'', '\\"', "\\\0"),
                $s) . "'";
}

// Delete everything in $basedir older than $age seconds
function cleanup($basedir, $age)
{
        if ($handle = opendir($basedir)) {
                while (($file = readdir($handle)) !== false) {
                        $entry = "$basedir/$file";
                        if ($file != '.' and $file != '..' and
                            (time() - filemtime($entry) > $age)) {
                                if (is_dir($entry)) {
                                        removeDir($entry);
                                } else {
                                        unlink($entry);
                                }
                        }
                }
        }
}
