<?php
// ex1.php
require_once("template.php");
$symbols = array('name' => "Joe",
	'phones' => array( 
		array('type' => "home", 'number' => "555-1234"),
		array('type' => "cellular", 'number' => "555-2345")
		));
// you can try this one with ex3.tmpl too
$page = gen_template("ex2.tmpl", $symbols);
echo $page

?>