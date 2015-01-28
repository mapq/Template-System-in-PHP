<?php
// ex8.php
// Example using the <%data%> directive with CSV data

require_once("vendor/autoload.php");
$symbols = array('last' => "Perez");
echo gen_template("ex8.tmpl", $symbols);

?>
