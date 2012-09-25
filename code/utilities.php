<?php
// Dispatch for MVC system
// CS 5774 UISW
// manuel a perez quinones
// computer science-virginia tech

// ---------------------------------------------------------------------------
function startsWith($string, $prefix) 
{	return (strncmp($string, $prefix, strlen($prefix)) == 0);	}

// ---------------------------------------------------------------------------
function debug_print($var)
{
	if (is_array($var)) {
		echo "<pre>";
		print_r($var);
		echo "</pre>";
	}
	else
		echo "{$var}\n";
}

// ---------------------------------------------------------------------------
function streq($a, $b)
{
	return strcmp($a, $b) == 0;
}
?>