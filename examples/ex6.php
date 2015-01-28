<?php
// ex5.php
// Example using PHP's keys
// Consider a single dimention array as we had in Ex2.php
require_once("vendor/autoload.php");

function getURL2CSS()
{
	return "css/";
}
function getURL2Javascript()
{
	return "javascript/";
}
echo gen_template("ex6.tmpl", 	array(
		'SemesterYear' => "Spring 2013",
		'CourseName' => "Usability Engineering",
		'URL2CSS' => getURL2CSS(),
		'URL2JS' => getURL2Javascript(),
		'CourseLinks' => array(
			array('label'=>"Syllabus", 'link'=>"syllabus.php"),
			array('label'=>"Piazza", 'link'=>"http://piazza.com")),
		'ActiveCourses' => array(
			array('name'=>"CS5774", 'shortname'=>"cs5774", 'description'=>"GUI Course"),
			array('name'=>"CS5714", 'shortname'=>"cs5714", 'description'=>"UE Course")),
		// 'menu'=>'site_menu',
		// 'contents'=>'site_content',
		// 'sidebar'=>'site_sidebar')
		));
?>
