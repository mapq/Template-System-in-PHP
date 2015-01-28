<?php
// ex8c.php
// Example using the <%data%> directive with CSV data

require_once("vendor/autoload.php");
$symbols = array('last' => "Perez");
echo gen_template("ex8c.tmpl", $symbols);

?>
