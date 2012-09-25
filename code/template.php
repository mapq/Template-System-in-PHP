<?php
// template.php
// manuel a perez-quinones
// computer science @ virginia tech
// 2012
//
// full implementation of a template language for HTML
// Notes
// * add later a <% call {funct} %> block

require_once("utilities.php");

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
	$output = evaluate_parse($parsed, $variables);

	return $output;
}

// ---------------------------------------------------------------------------
function evaluate_parse($parsed, $variables)
{
	// turn patterns into regular expression patterns
	$patterns = array_keys($variables);
	for ($i = 0; $i < count($patterns); $i++) {
		$key = $patterns[$i];
		$patterns[$i] = "/\{".$key."\}/";
	}

	// build array with values (replacements)
	$replace = array_values($variables);

	$output = "";
	// loop while there are more tokens
	for ($i = 0; $i < count($parsed); $i++) {
		if ($parsed[$i]['node'] == "text") {
			$temp = $parsed[$i]['content'];
			$results = preg_replace($patterns, $replace, $temp);
			if (is_array($results))
				foreach($results as $r)
					$output .= $r;
			else
				$output .= $results;
		}
		else if ($parsed[$i]['node'] == "if") {
			$cond = $parsed[$i]['cond'];
			// lets evaluate the condition... 
			if (isset($variables[$cond]) && $variables[$cond]) {
				$output .= evaluate_parse($parsed[$i]['then'], $variables);
			}
			else if ($parsed[$i]['else']) {	// if there is an else block
				$output .= evaluate_parse($parsed[$i]['else'], $variables);
			}
		}
		else if ($parsed[$i]['node'] == "repeat") {
			$variables['loopfirst'] = true;
			$variables['loopodd'] = true;
			$variables['loopeven'] = !$variables['loopodd'];
			$variables['loopcount'] = 1;
			$variables['looplast'] = false;

			$collection = $variables[$parsed[$i]['collection']];
			$last = count($collection);
			foreach($collection as $item) {
				// extending the environment, it works!
				foreach ($item as $key => $val) {
					$variables[$key] = $val;
				}
				//print_r($variables);

				$output .= evaluate_parse($parsed[$i]['body'], $variables);
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
/*
Parsing the file: Three types of nodes, text, if, repeat
	array('node'=>[text|if|repeat], stuff)
		text:	 stuff is the text
		if:	 array('cond' => "{condition}",
						'then' => node,
						'else' => node);
		repeat: array('collection' => "{collection}",
					'block' => node)
*/

function parse_tmpl($stream, &$i, $level)
{
	$output = array();
	// loop while there are more tokens
	while ($i < count($stream)) {
		// echo "Processing (\$i = $i) token first 5 chars(".substr($stream[$i], 0, 5)."...)\n";
		// process $stream[$i]
		$token = $stream[$i++];
		if (startsWith($token, "<%")) {

			// do something
			$type = $stream[$i++];			// type: if, else, repeat, end

			if (startsWith($type, "if")) {
				$cond = $stream[$i++];			// {condition} if it exists
				$node = array('node'=>'if', 'cond'=>$cond);
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
				$node = array('node'=>'repeat', 'collection'=>$cond);
				$node['body'] = parse_tmpl($stream, $i, $level+1);
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
			$node = array('node'=>'text', 'content' => $token);
			$output[] = $node;
		}
	}
	return $output;	// return empty array	
}

// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------

// To call it...
if (false) {
	echo gen_template($argv[1], 
		array(
			'name'=>'Manuel',
			'student' => false,
			'degree'=>'PhD',
			'slo' => 'perez',
			'emails' => array(
				array('email'=>"personal@me.com", 'label'=>"Home", 'stuff'=>"whoknows"),
				array('email'=>"work@me.com", 'label' => "Work", 'stuff'=>"whoknows")
			)));
}

?>