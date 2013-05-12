#!/usr/bin/php -f
<?php
// template.php
// manuel a perez-quinones
// computer science @ virginia tech
// 2012
//
// full implementation of a template language for HTML
// Notes

define("TEXT_STMT", 1);
define("IF_STMT", 2);
define("REPEAT_STMT", 3);
define("CALL_STMT", 4);
define("DATA_STMT", 5);
define("JSON_DATA", 6);
define("XML_DATA", 7);
define("CSV_DATA", 8);

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

// ---------------------------------------------------------------------------
function gen_template($page, $variables)
{

	// Read external file
	$content = file_get_contents($page);

	// Split the file by <cond: ... </cond> blocks
	$tokens = preg_split("/(\<\%[ ]*([a-zA-Z]+)[ ]*\{?([a-zA-Z]+)?\}?[ ]*\%\>)/", 
		$content, 0, PREG_SPLIT_DELIM_CAPTURE);

	// Now, lets parse the string chunks...
	$i = 0;
	$parsed = parse_tmpl($tokens, $i, 0);

	// This must be recursive too
	$output = evaluate_tmpl($parsed, $variables);

	return $output;
}

// ---------------------------------------------------------------------------
/*
Parsing the file: Five types of nodes, text, if, repeat, call, data
	array('node'=>[text|if|repeat], stuff)
		text:	 stuff is the text
		if:	 array('cond' => "{condition}",
						'then' => node,
						'else' => node);
		repeat: array('collection' => "{collection}",
					'block' => node)
		call: ...
		data: array('type' => {json | xml | csv}, 'content'=>text)
*/

function parse_tmpl($stream, &$i, $level)
{
	// arg $level is not used, can we eliminate it?

	$output = array();
	// loop while there are more tokens
	while ($i < count($stream)) {
		// echo "Processing (\$i = $i) token first 5 chars(".substr($stream[$i], 0, 5)."...)\n";
		// process $stream[$i]
		$token = $stream[$i++];
		if (startsWith($token, "<%")) {
			// do something
			$type = $stream[$i++];			// type: if, else, repeat, end, call, data

			if (startsWith($type, "if")) {
				$cond = $stream[$i++];			// {condition} if it exists
				$node = array('node'=>IF_STMT, 'cond'=>$cond);
				$node['then'] = parse_tmpl($stream, $i, $level+1);
				// returns when else or end is found
				if (startsWith($stream[$i-1], "end"))
					$node['else'] = false;
				else {
					$node['else'] = parse_tmpl($stream, $i, $level+1);
				}
				$output[] = $node;
			}
			else if (startsWith($type, "repeat")) {
				$cond = $stream[$i++];			// {condition} if it exists
				$node = array('node'=> REPEAT_STMT, 'collection'=>$cond);
				$node['body'] = parse_tmpl($stream, $i, $level+1);
				$output[] = $node;
			}
			else if (startsWith($type, "call")) {
				$fname = $stream[$i++];			// {functionname}
				$node = array('node'=>CALL_STMT, 'fname'=>$fname);
				$output[] = $node;
			}
			else if (startsWith($type, "data")) {
				$type = $stream[$i++];			// {json | xml | csv}
				$node = array('node'=>DATA_STMT, 'type' => strtolower($type));
				// returns when end is found
				$node['data'] = parse_tmpl($stream, $i, $level+1);
				$output[] = $node;
			}
			else if (startsWith($type, "else")) {
				return $output;
			}
			else if (startsWith($type, "end")) {
				return $output;
			}
			else
				echo "Token $token\n";	// Error case?
		}
		else {
			// Text
			$node = array('node'=>TEXT_STMT, 'content' => $token);
			$output[] = $node;
		}
	}
	return $output;	// return empty array	
}

// ---------------------------------------------------------------------------
function evaluate_tmpl($parsed, $variables)
{

	$output = "";
	// loop while there are more tokens
	for ($i = 0; $i < count($parsed); $i++) {
		// echo $parsed[$i]['node']."\n";
		if ($parsed[$i]['node'] == TEXT_STMT) {
			$temp = $parsed[$i]['content'];

			// turn variables into regular expression patterns
			$patterns = array_keys($variables);
			for ($j = 0; $j < count($patterns); $j++) {
				$key = $patterns[$j];
				$patterns[$j] = "/\{".$key."\}/";
			}

			// build array with values (replacements)
			$replace = array_values($variables);

			// And then do the replacement
			$results = preg_replace($patterns, $replace, $temp);
			if (is_array($results))
				foreach($results as $r)
					$output .= $r;
			else
				$output .= $results;
		}

		else if ($parsed[$i]['node'] == IF_STMT) {
			$cond = $parsed[$i]['cond'];
			// lets evaluate the condition... 
			if (isset($variables[$cond]) && $variables[$cond]) {
				$output .= evaluate_tmpl($parsed[$i]['then'], $variables);
			}
			else if ($parsed[$i]['else']) {	// if there is an else block
				$output .= evaluate_tmpl($parsed[$i]['else'], $variables);
			}
		}

		else if ($parsed[$i]['node'] == CALL_STMT) {
			$functionName = $parsed[$i]['fname'];
			$output .= call_user_func($functionName, $variables);
		}

		else if ($parsed[$i]['node'] == DATA_STMT) {
			// We have data in JSON, XML or CSV, process accordingly
			if ($parsed[$i]['type'] == 'json') {
				// data is not evaluated, just parsed for json content
				$jsondata = $parsed[$i]['data'][0]['content'];
				$jsonparsed = json_decode($jsondata, true);
		
				// The semantics of this section is that the environment
				// (variables) must be extended so that it is used in
				// any following recursive call.. the search patterns
				// and replacements are computed in the TEXT_STMT
				
				foreach ($jsonparsed as $key => $val) {
					$variables[$key] = $val;
				}
			}
			else if ($parsed[$i]['type'] == 'xml') {
				$output .= "<!--- XML not implemented -->";
			}
			else if ($parsed[$i]['type'] == 'csv') {
				$csvdata = trim($parsed[$i]['data'][0]['content']);

				// need to get the headers
				$rows = explode("\n", $csvdata); //parse the rows
				$head = str_getcsv($rows[0], ","); //parse the items in rows
				$extension = array();
				for($j = 1; $j < count($rows); $j++) {
					$r = str_getcsv($rows[$j], ","); //parse the items in rows 
					// connect the $row with
					$row = array();
					for ($k = 0; $k < count($head); $k++)
						$row[$head[$k]] = $r[$k];
					$extension[] = $row;
				}

				// just one array added to variables and it is in the format of
				$variables['csv'] = $extension;
			}
		
			$output .= "<!--- Processed data -->";
		}

		else if ($parsed[$i]['node'] == REPEAT_STMT) {
			$variables['loopfirst'] = true;
			$variables['loopodd'] = true;
			$variables['loopeven'] = !$variables['loopodd'];
			$variables['loopcount'] = 1;
			$variables['looplast'] = false;
				
			$collection = $variables[$parsed[$i]['collection']];
			$last = count($collection);
			foreach($collection as $item) {
				// extending the environment, it works!
				// Here we extend the environment (variables) and then we call
				// evaluate_tmpl recursively.  The variables patterns and replace
				// are recalculated when the routine enters... contrast this with
				// the data section above...
				foreach ($item as $key => $val) {
					$variables[$key] = $val;
				}

				$output .= evaluate_tmpl($parsed[$i]['body'], $variables);
				// update other variables
				$variables['loopfirst'] = false;
				$variables['loopcount']++;
				$variables['loopeven'] = !$variables['loopeven'];
				$variables['loopodd'] = !$variables['loopodd'];
				if ($variables['loopcount'] == $last)
					$variables['looplast'] = true;
			}
		}
	}
	return $output;	// return empty array
}


// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------

// To call it...
if (defined('STDIN') ) {
	// this code will run from the command line for testing purposes
	// it runs by taking either a file name without extension or
	// a template file name followed by many files of data
	if ($argc == 2) {
		// one file only
		$template = "$argv[1].tmpl";
		if (file_exists("{$argv[1]}.json")) {
			$c = file_get_contents("{$argv[1]}.json");
			$data = json_decode($c, true);
		}
		else if (file_exists("{$argv[1]}.csv")) {
			$file = "{$argv[1]}.csv";
			if (($handle = fopen($file, "r")) !== FALSE) {
				$head = fgetcsv($handle, 1000, ",");
				$d = array();
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					$num = count($data);
					$row = array();
					for ($c=0; $c < $num; $c++) {
						$row[$head[$c]] = $data[$c];
					}
					$d[] = $row;
				}
				$data['csv'] = $d;
				fclose($handle);
			}
		}
		else {
			$data = array();
			// echo "Neither {$argv[1]}.json nor {$argv[1]}.csv exist.\n";
			// exit();
		}
		echo gen_template($template, $data);
	}
	else if ($argc == 3) {
		$template = "$argv[1].tmpl";
	}
}

?>