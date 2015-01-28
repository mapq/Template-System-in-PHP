<?php
// ex5.php

require_once("vendor/autoload.php");

function pageheader($params)
{
	return "<head><title>{$params['pagetitle']}</title></head>\n";
}

$symbols = array('name' => "Joe",
	'phones' => array( 
		array('type' => "home", 'number' => "555-1234"),
		array('type' => "cellular", 'number' => "555-2345")
		),
	'pagetitle' => "Hello there");
echo gen_template("ex5.tmpl", $symbols);
?>