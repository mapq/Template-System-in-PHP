#!/usr/bin/php -f
<?php
// maintemplate.php
// manuel a perez-quinones
// computer science @ virginia tech
// 2012-2013
//
// full implementation of a template language for HTML in PHP
// https://github.com/mapq/Template-System-in-PHP
// Notes

require_once("template.php");

// ---------------------------------------------------------------------------

// To call it...
// if (defined('STDIN')) {
	$data = array();
	// echo "Parms = {$argc}\n";
	// this code will run from the command line for testing purposes
	// it runs by taking either a file name without extension or
	// a template file name followed by many files of data
	if ($argc == 2) {
		// one file only
		$template = "{$argv[1]}.tmpl";
		$datafile = "{$argv[1]}";
	}
	else if ($argc == 3) {
		$template = "{$argv[1]}.tmpl";
		$datafile = "{$argv[2]}";
	}
	else {
		$template = "index.tmpl";
		$datafile = $template;
	}

	// echo "gen_template({$template}, {$datafile});\n";

	// using file names from above, try it...
	if ($p = gen_template_from_xml($template, $datafile.".xml"))
		echo $p;
	else if ($p = gen_template_from_csv($template, $datafile.".csv"))
		echo $p;
	else if ($p = gen_template_from_xml($template, $datafile.".xml"))
		echo $p;
	else {
		$data = array();
		echo "Neither {$argv[1]}.json nor {$argv[1]}.csv nor {$argv[1]}.xml exist.\n";
		echo "Running without external file.\n";
		echo gen_template($template, $data);
	}

	// if (file_exists($datafile.".json")) {
	// 	echo "Reading json file\n";
	// 	$c = file_get_contents($datafile.".json");
	// 	$data = json_decode($c, true);
	// 	// print_r($data);
	// }
	// else if (file_exists($datafile.".csv")) {
	// 	echo "Reading csv file\n";
	// 	$file = $datafile.".csv";
	// 	if (($handle = fopen($file, "r")) !== FALSE) {
	// 		$head = fgetcsv($handle, 1000, ",");
	// 		$d = array();
	// 		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	// 			$num = count($data);
	// 			$row = array();
	// 			for ($c=0; $c < $num; $c++) {
	// 				$row[$head[$c]] = $data[$c];
	// 			}
	// 			$d[] = $row;
	// 		}
	// 		$data['csv'] = $d;
	// 		fclose($handle);
	// 	}
	// }
	// else if (file_exists($datafile.".xml")) {
	// 	echo "Reading xml file\n";
	// 	$file = $datafile.".xml";
	// 	$x = file_get_contents($file);
	// 	$xml = new SimpleXMLElement($x);
	// 	// Convert XML to associative array
	// 	$data = array();
	// 	$arraydata = object2array($xml);
	// 	foreach ($arraydata as $key => $val) {
	// 		$data[$key] = $val;
	// 	}
	// }
	// else {
	// 	$data = array();
	// 	echo "Neither {$argv[1]}.json nor {$argv[1]}.csv nor {$argv[1]}.xml exist.\n";
	// 	echo "Running without external file.\n";
	// }
	// 
	// // echo "Processing\n";
	// // print_r($data);
	// echo gen_template($template, $data);
// }

?>