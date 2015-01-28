<?php
// template.php
// manuel a perez-quinones
// computer science @ virginia tech
// 2012-2013
//
// full implementation of a template language for HTML in PHP
// https://github.com/mapq/Template-System-in-PHP
// Notes

define("TEXT_STMT", 1);
define("IF_STMT", 2);
define("UNLESS_STMT", 10);
define("REPEAT_STMT", 3);
define("CALL_STMT", 4);
define("DATA_STMT", 5);
define("JSON_DATA", 6);
define("XML_DATA", 7);
define("CSV_DATA", 8);
define("INCLUDE_STMT", 9);
define("LAYOUT_STMT", 11);


// ---------------------------------------------------------------------------
function startsWith($string, $prefix) 
{	return (strncmp($string, $prefix, strlen($prefix)) == 0);	}

// ---------------------------------------------------------------------------
function object2array($object)
{
	$r = null;
	
	if (is_array($object)) {
		foreach ($object as $key => $value)
			$r[$key] = object2array($value);
	}
	else {	// not an array
		//First, process the object instance variables
		if (gettype($object) != "string") {
			$var = get_object_vars($object);			// error message here, a warning... fix
			if ($var) {		// if it has object vars, do them
				foreach ($var as $key => $value) {
					$r[$key] = object2array($value);
				}
			}
		}

		// Next, process the XML attributes
		if ($object instanceof SimpleXMLElement) {
			// if it is and XML element and has properties, do them
			foreach($object->attributes() as $name => $value) {
				$r[$name] = $value."";		// force to string
			}
			
			// if at this stage we have not processed anything
			// (i.e. r == null), then we have a <![DATA >
			if ($r == null)
				$r = $object."";
		}
		
		else {
			return $object;
		}
	}
	return $r;
}

// ---------------------------------------------------------------------------
function get_template_vars($tmpl_file)
{

	if (!file_exists($tmpl_file))
		return false;

	// Read external file
	$content = file_get_contents($tmpl_file);

	// Split the file by {variables} blocks
	preg_match_all("|\{([a-zA-Z0-9\/\.\-\_]+)\}|", $content, $matches);
	return $matches[1];
}

// ---------------------------------------------------------------------------
function gen_template_from_json($tmpl_file, $json)
{
	if (file_exists($json)) {
		$c = file_get_contents($json);
		$data = json_decode($c, true);
		return gen_template($tmpl_file, $data);
	}
	else
		return false;
}

// ---------------------------------------------------------------------------
function gen_template_from_csv($tmpl_file, $csv)
{
	if (file_exists($csv)) {
		if (($handle = fopen($csv, "r")) !== FALSE) {
			$head = fgetcsv($handle, 2000, ",");
			$d = array();
			while (($line = fgetcsv($handle, 2000, ",")) !== FALSE) {
				$num = count($line);
				$row = array();
				for ($c=0; $c < $num; $c++) {
					$row[$head[$c]] = $line[$c];
				}
				$d[] = $row;
			}
			$data['csv'] = $d;
			fclose($handle);
			return gen_template($tmpl_file, $data);
		}
		else
			return false;
	}
	else
		return false;
}

// ---------------------------------------------------------------------------
function gen_template_from_xml($tmpl_file, $xmlfile)
{
	if (file_exists($xmlfile)) {
		$x = file_get_contents($xmlfile);
		$xml = new SimpleXMLElement($x);
		// Convert XML to associative array
		$data = array();
		$arraydata = object2array($xml);
		foreach ($arraydata as $key => $val) {
			$data[$key] = $val;
		}
		return gen_template($tmpl_file, $data);
	}
	else
		return false;
}


// ---------------------------------------------------------------------------
function gen_template($tmpl_file, $variables = array())
{

	if (!file_exists($tmpl_file))
		return false;

	// Read external file
	$content = file_get_contents($tmpl_file);
	return gen_template_from_memory($content, $variables);
}

// ---------------------------------------------------------------------------
function gen_template_from_memory($content, $variables = array())
{

	// Split the file by <% ... %> blocks
	$tokens = preg_split("|(\<\%[ \t]*([a-zA-Z]+)[ \t]*\{?([a-zA-Z0-9\/\.\-\_]+)?\}?[ \t]*\%\>)|", 
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
Parsing the file: Several types of nodes, text, if, repeat, call, data
	array('node'=>[text|if|repeat|include|call], stuff)
		text:	 stuff is the text
		if:	 array('cond' => "{condition}",
						'then' => node,
						'else' => node);
		repeat: array('collection' => "{collection}",
					'block' => node)
		call: ...
		include: filename
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
			else if (startsWith($type, "unless")) {
				$cond = $stream[$i++];			// {condition} if it exists
				$node = array('node'=>UNLESS_STMT, 'cond'=>$cond);
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
			else if (startsWith($type, "include")) {
				$fname = $stream[$i++];			// {filename}
				$node = array('node'=>INCLUDE_STMT, 'fname'=>$fname);
				$output[] = $node;
			}
			else if (startsWith($type, "layout")) {
				$fname = $stream[$i++];			// {filename}
				$node = array('node'=>LAYOUT_STMT, 'fname'=>$fname);
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

	$haveLayout = false;
	$output = "";
	// loop while there are more tokens
	for ($i = 0; $i < count($parsed); $i++) {
		// echo $parsed[$i]['node']."\n";
		if ($parsed[$i]['node'] == TEXT_STMT) {
			$temp = $parsed[$i]['content'];

			// turn variables into regular expression patterns
			// but only if they are simple variables, not arrays
			//$patterns = array_keys($variables);
			$patterns = array();
			$replacements = array();
			foreach($variables as $key => $value) {
				if (!is_array($value)) {
					$patterns[] = "/\{".$key."\}/";
					$replacements[] = $value;
				}
			}

			// debug_print($patterns);
			// debug_print($replacements);

			// the next three replacements were replace which is no
			// defined...
			for ($j = 0; $j < count($replacements); $j++) {
				if (is_array($replacements[$j]))
					$replacements[$j] = "";
			}

			// And then do the replacement
			$results = preg_replace($patterns, $replacements, $temp);
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

		else if ($parsed[$i]['node'] == UNLESS_STMT) {
			$cond = $parsed[$i]['cond'];
			// lets evaluate the condition... 
			if (isset($variables[$cond]) && $variables[$cond]) {
				// if the condition is true (we are processing an unless)
				// then we execute the 'else' if it exists
				if ($parsed[$i]['else']) {	// if there is an else block
					$output .= evaluate_tmpl($parsed[$i]['else'], $variables);
				}
				// if there is no else but the condition was true,
				// do nothing
			}
			else {
				// if the condition if false, then evaluate the then clause
				$output .= evaluate_tmpl($parsed[$i]['then'], $variables);
			}
		}

		else if ($parsed[$i]['node'] == CALL_STMT) {
			$functionName = $parsed[$i]['fname'];
			$output .= call_user_func($functionName, $variables);
		}

		else if ($parsed[$i]['node'] == INCLUDE_STMT) {
			$fileName = $parsed[$i]['fname'];
			// generate the included file using the current set of variables...
			// and concatenate the output into the output here
			$output .= gen_template($fileName, $variables);
		}
		else if ($parsed[$i]['node'] == LAYOUT_STMT) {
			$layoutName = $parsed[$i]['fname'];	// layout file
			// save the layout name, to be processed later
			$variables['layout_template'] = $parsed[$i]['fname'];
			$haveLayout = true;
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
				$xmldata = $parsed[$i]['data'][0]['content'];
				$xml = new SimpleXMLElement($xmldata);
				// Convert XML to associative array
				$arraydata = object2array($xml);
				// print_r ($arraydata);
				
				// The semantics of this section is that the environment
				// (variables) must be extended so that it is used in
				// any following recursive call.. the search patterns
				// and replacements are computed in the TEXT_STMT
				
				foreach ($arraydata as $key => $val) {
					$variables[$key] = $val;
				}
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
				
			$collection = $variables[$parsed[$i]['collection']];
			$last = count($collection);
			$variables['looplast'] = ($last == 1);
			$variables['loophasmore'] = ($last > 1);
			if ($collection == null) {
				$output .= "<!-- ERROR: Collection used in repeat statement is not defined. -->\n";
			}
			else {
				foreach($collection as $item) {
					// extending the environment, it works!
					// Here we extend the environment (variables) and then we call
					// evaluate_tmpl recursively.  The variables patterns and replace
					// are recalculated when the routine enters... contrast this with
					// the data section above...
					$toclear = array();
					foreach ($item as $key => $val) {
						$variables[$key] = $val;
						$toclear[] = $key;
					}

					$output .= evaluate_tmpl($parsed[$i]['body'], $variables);

					// get rid of local variables from inside of the loop
					foreach($toclear as $key)
						unset($variables[$key]);
					// update other variables
					$variables['loopfirst'] = false;	// not first anymore
					$variables['loopcount']++;
					$variables['loopeven'] = !$variables['loopeven'];
					$variables['loopodd'] = !$variables['loopodd'];
					if ($variables['loopcount'] >= $last) {
						$variables['looplast'] = true;
						$variables['loophasmore'] = false;
					}
				}
			}
		}
	}

	// or if (isset($variables['layout_template']))
	if ($haveLayout) {		// if we picked up a layout
		$variables['content'] = $output;
		$output = gen_template($variables['layout_template'], 
			$variables);
	}

	return $output;	// return output
}

// the end
?>
