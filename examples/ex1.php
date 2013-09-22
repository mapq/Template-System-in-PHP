<?php
// ex1.php
require_once("../template.php");

$symbols = array('name' => "Joe");
$page = gen_template("ex1.tmpl", $symbols);
echo $page

?>