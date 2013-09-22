<?php
// ex4.php
// Example showing a nested loop in an html file
require_once("../template.php");
$symbols = array(
	'people' => array(
		array('name' => "Joe",
			'phones' => array( 
					array('type' => "home", 'number' => "555-1234"),
					array('type' => "cellular", 'number' => "555-2345"))),
		array('name' => "Manuel",
			'phones' => array( 
					array('type' => "home", 'number' => "555-9874"),
					array('type' => "office", 'number' => "231-2646")))
		));

echo gen_template("ex4.tmpl", $symbols);
?>