<?php
// ex7.php
// Using the <%data%> command in the template

require_once("template.php");
$symbols = array('last' => "Perez");
echo gen_template("ex7.tmpl", $symbols);

?>
