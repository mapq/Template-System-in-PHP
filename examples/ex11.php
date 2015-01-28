<?php
// ex11.php
// Example using the <%data%> directive with XML data

require_once("vendor/autoload.php");
$symbols = array('last' => "Perez");
echo gen_template("ex11.tmpl", $symbols);

?>
