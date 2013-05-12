<?php
// ex5.php
// Example using PHP's keys
// Consider a single dimension array as we had in Ex2.php

require_once("template.php");
$symbols = array('name' => "Joe",
	'phones' => array( 
		array('type' => "home", 'number' => "555-1234"),
		array('type' => "cellular", 'number' => "555-2345")
		),
	'pagetitle' => "Hello there");

function pageheader($params)
{
	return "<head><title>{$params['pagetitle']}</title></head>\n";
}

$symbols['keys'] = array_keys($symbols['phones'][0]);
//print_r($symbols);
echo gen_template("ex5.tmpl", $symbols);
?>