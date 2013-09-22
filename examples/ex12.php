<?php
// ex12.php
// Example using the <%data%> directive with CSV data

require_once("../template.php");
$symbols = array('last' => "Perez");
echo gen_template("ex12.tmpl", $symbols);

?>
