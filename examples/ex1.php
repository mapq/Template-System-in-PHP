<?php
// ex1.php
require_once("vendor/autoload.php");

$symbols = array('name' => "Joe");
$page = gen_template("ex1.tmpl", $symbols);
echo $page

?>