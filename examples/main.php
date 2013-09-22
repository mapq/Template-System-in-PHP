#!/usr/bin/php -f
<?php
// main.php
// manuel a perez-quinones
// computer science @ virginia tech
// 2012-2013
//
// full implementation of a template language for HTML in PHP
// https://github.com/mapq/Template-System-in-PHP
//
// Notes
// Run it like this:
/*
$ ./main.php example	# will run 

*/
require_once("../template.php");

function addExtension($filename, $extension)
{
	// if filename has extension, just return it unchanged
	if (strpos($filename, ".") === false)
		return "{$filename}.$extension";
	else // else add extension
		return $filename;
}

// ---------------------------------------------------------------------------

	$extensions = array("xml", "csv", "json", "yaml");
	$process['xml'] = "gen_template_from_xml";
	$process['csv'] = "gen_template_from_csv";
	$process['json'] = "gen_template_from_json";
	// $process['yaml'] = gen_template_from_yaml;

	// this code will run from the command line for testing purposes
	// it runs by taking either a file name without extension or
	// a template file name followed a data file

	$data = array();
	if ($argc == 2) {
		// one file only
		$template = addExtension($argv[1], "tmpl");
		$datafile = $argv[1];
	}
	else if ($argc == 3) {
		$template = addExtension($argv[1], "tmpl");
		$datafile = $argv[2];
	}
	else {
		echo "# usage: ./main.php template [datafile]\n";
		exit();
	}

	echo "Template: $template\n";

	if (file_exists($datafile)) {
		echo "Datafile: $datafile\n";
		$path_parts = pathinfo($datafile);
		echo call_user_func($process[$path_parts['extension']],
			$template, $datafile);
	}
	else {
		foreach($extensions as $ext) {
			$file = addExtension($datafile, $ext);
			if (file_exists($file)) {
				echo "Datafile: $file\n";
				echo call_user_func($process[$ext], $template, $file);
				exit();
			}
		}

		// if we get here it is because the datafile was not found
		echo "Neither {$datafile}.json nor {$datafile}.csv nor {$datafile}.xml exist.\n";
		echo "Running without external file.\n";
		echo gen_template($template, $data);
	}
?>