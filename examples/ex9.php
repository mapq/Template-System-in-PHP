<?php
// ex9.php
// Example using the <%data%> directive with CSV data

require_once("vendor/autoload.php");
require_once("Markdown.php");

$v = gen_template_from_csv("ex9.tmpl", "ex9.csv");

// //$v = gen_template_from_xml("ex9.tmpl", "ex9.xml");

// $content = Markdown(file_get_contents("file.md"));
// $v = gen_template_from_json("ex9.tmpl", array('content'=>$content));

echo $v;
?>
