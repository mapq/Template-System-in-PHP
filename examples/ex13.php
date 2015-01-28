<!-- ex13.php -->
<% data xml %>
<xml>
	<title>Ex7</title>
	<name>manuel</name>
	<last>perez</last>
	<looping>
		<name>var1</name>
		<value>10</value>
	</looping>
	<looping>
		<name>var2</name>
		<value>20</value>
	</looping>
</xml>
<% end %>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>{title}</title>
    <meta name="author" content="MAPQ">
  </head>
  <body>
	<h1>{title}</h1>
	<p>My name is {last}, {name}.</p>
   <ul>
		<% repeat {looping} %>
         <li>{name} = {value}</li>
		<% end %>
   </ul>
  </body>
</html>
<?php
// ex13.php
require_once("vendor/autoload.php");
$symbols = array('last' => "Perez");
echo gen_template(__FILE__, $symbols);

?>
