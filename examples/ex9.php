<?php
// ex9.php
// Example using the <%data%> directive with CSV data

require_once("../template.php");
echo gen_template_from_csv("ex9.tmpl", "ex9.csv");
?>
