<?php
/* 
 * Used to extract Yara and Snort Signatures from a text string
 */
// App::uses('Sanitize', 'Utility');
App::uses('Inflector', 'Core');

class SignatureBehavior extends ModelBehavior 
{
	public $settings = array();
	
	private $_defaults = array();
	
	private $sig_type = false;
	
	private $allowed_sig_types = array(
		'yara',
		'snort',
	);
	
	private $string = false;
    
    public $snort_direction_map = array(
    	'in' => '->',
    	'out' => '<-',
    	'both' => '<>',
    );
    
    private $yara_encode_map = array(
    	'\\\\' => '&slash;',
    );
	
	public function setup(Model $Model, $config = array()) 
	{
	/*
	 * Set everything up
	 */
		// merge the default settings with the model specific settings
		$this->settings[$Model->alias] = array_merge($this->_defaults, $config);
	}
	
	// set the settings
	public function Sig_setType(Model $Model, $sig_type = false)
	{
		$sig_type = strtolower(trim($sig_type));
		if(!$sig_type)
		{
			$Model->modelError = __('Unknown %s type (%s)', __('Signature'), __('1'));
			return false;
		}
		
		if(!in_array($sig_type, $this->allowed_sig_types))
		{
			$Model->modelError = __('Unknown %s type (%s)', __('Signature'), __('2'));
			return false;
		}
		
		$this->sig_type = $sig_type;
		return true;
	}
	
	// set the string that we will be parsing
	public function Sig_setString(Model $Model, $string = false)
	{
		$string = trim($string);
		if(!$string)
		{
			$Model->modelError = __('No %s were provided', __('Signatures'));
			return false;
		}
		
		$this->string = $string;
	}
	
	public function Sig_getSignatures(Model $Model)
	{
		$signatures = array();
		switch ($this->sig_type)
		{
			case "yara":
				$signatures = $this->_parseYara($Model);
				break;
			case "snort":
				$signatures = $this->_parseSnort($Model);
				break;
			default:
				$Model->modelError = __('Unknown %s type: %s - (%s)', __('Signature'), $this->sig_type, __('3'));
		}
		
		return $signatures;
	}
	
	private function _getString(Model $Model)
	{
		return $this->string;
	}
	
	private function _parseYara(Model $Model)
	{
		$signatures = array();
		$string = $string_raw = $this->_getString($Model);
		$string = $this->_cleanupYaraString($Model, $string);
		
		$indent = 0;
		$rules = array();
		
		$rules_default = array(
			'name' => false,
			'title' => false,
			'scope' => false,
			'tags' => false,
			'meta' => false,
			'strings' => false,
			'condition' => false,
			'raw_compiled' => false,
			'raw' => false,
			'hash' => false,
		);
		
		$rule_sections = array('meta', 'strings', 'condition');
		$section = false;
		$previous_line = false;
		$previous_string = false;
		$rule_raw_compiled = array();
		$rule_count = $sig_key = 0;
		$rule_line = 0;
		$line_match = false;
		$line_tokens = false;
		
		$lines = explode("\n", $string);
		
		foreach($lines as $i => $line)
		{
			if($line_match)
			{
				$i_prev = ($i - 1);
				unset($lines[$i_prev]);
			}
			
			$rule_line++;
			$line_match = false;
			$actual_open = false;
			$actual_close = false;
			$line_tokens = token_get_all('<'.'?php '. $line. '?'.'>');
			
			
			// match the end of group
			if(preg_match('/{/', $line))
			{
				$actual_open = false;
				foreach($line_tokens as $line_token)
				{
					if(!is_array($line_token) and $line_token == '{')
					{
						$actual_open = true;
					}
				}
			}
			if($actual_open)
			{
				$indent++;
				$line_match = true;
				// start of the rule
				if($indent == 1)
				{
					// reset the rule specific settings
					$rule = $rules_default;
					$rule_raw_compiled = array();
					$rule_line = 1;
				}
			}
			
			// match the end of group
			if(preg_match('/}/', $line))
			{
				$actual_close = false;
				foreach($line_tokens as $line_token)
				{
					if(!is_array($line_token) and $line_token == '}')
					{
						$actual_close = true;
					}
				}
			}
			if($actual_close)
			{
				$indent--;
				$line_match = true;
				// end of the rule
				if($indent == 0)
				{
					if(!$rule['condition'] and $previous_string != $previous_line) 
					{
						$rule['condition'] = array($previous_line);
					}
					$rule_raw_compiled[] = '}';
					$rule_raw_compiled = $this->_yaraDecodeString($Model, $rule_raw_compiled);
					$rule['raw_compiled'] = implode("\n", $rule_raw_compiled);
					if(isset($rule['name']))
					{
						$sig_key = ($rule['scope']?$rule['scope'].'_':false). $rule['name'];
						$sig_key = Inflector::slug($sig_key);
						$rule['title'] = Inflector::humanize($rule['name']);
					}
					else
					{
						$rule_count++;
						$sig_key = $rule_count;
					}
					$signatures[$sig_key] = $rule;
					$section = false;
					
					// get rid of the empty rules
					if($rule_line == 2)
					{
						unset($signatures[$sig_key]);
					}
					
					$rule_line = 0;
				}
			}
			
			if($indent < 0)
			{
				$Model->modelError = __('There is an error in your %s, maybe and extra "}"? ', __('Signatures'));
				return false;
			}
			
			// in the top of the rule
			if($indent == 1)
			{
				$rule_raw_compiled[] = $line;
				// private rule | public rule | rule
				$matches = array();
				if(preg_match('/^(private|global)?(\s+)?rule(.*){/i', trim($line), $matches))
				{
					$line_match = true;
					$rule_scope = (isset($matches[1])?$matches[1]:false);
					$rule_name = (isset($matches[3])?$matches[3]:false);
					
					if($rule_scope)
					{
						$rule['scope'] = trim($rule_scope);
					}
					
					if($rule_name)
					{
						$rule_name = trim($rule_name);
						$rule_tags = array();
						
						if(stripos($rule_name, ':'))
						{
							list($rule_name, $rule_tags) = explode(':', $rule_name);
							$rule_tags = strtolower($rule_tags);
							$rule_tags = preg_split('/\s+/', $rule_tags);
							
							foreach($rule_tags as $j => $rule_tag)
							{
								if(!trim($rule_tag)) unset($rule_tags[$j]);
								$rule_tag = strtolower($rule_tag);
								$rule_tag = Inflector::slug($rule_tag);
								$rule_tags[$j] = $rule_tag;
							}
							sort($rule_tags);
						}
						$rule_name = trim($rule_name);
						$rule['name'] = $rule_name;
						$rule['tags'] = implode(',', $rule_tags);
					}
				}
				
				// meta information
				if(preg_match('/^meta\:/i', $line))
				{
					$line_match = true;
					$section = 'meta';
					continue;
				}
				elseif(preg_match('/^strings\:/i', $line))
				{
					$line_match = true;
					$section = 'strings';
					continue;
				}
				elseif(preg_match('/^condition\:/i', $line))
				{
					$line_match = true;
					$section = 'condition';
					continue;
				}
				elseif(preg_match('/^\$(\w+)?(\s+)?\=/i', $line) and $section != 'condition')
				{
					$line_match = true;
					$section = 'strings';
				}
				
				$matches = array();
				if($section == 'meta')
				{
					list($meta_key, $meta_val) = explode('=', $line);
					$meta_key = trim($meta_key);
					$meta_key = $this->_yaraDecodeString($Model, $meta_key);
					
					$meta_val = trim($meta_val);
					$meta_val = $this->_yaraDecodeString($Model, $meta_val);
					
					$rule['meta'][$meta_key] = $meta_val;
					$line_match = true;
				}
				elseif($section == 'strings' and preg_match('/^\$(\w+)?(\s+)?\=(.*)/', $line, $matches))
				{
					$string_key = (isset($matches[1])?$matches[1]:false);
					$string_key = trim($string_key);
					$string_key = trim($string_key, '$');
					$string_key = $this->_yaraDecodeString($Model, $string_key);
					
					$string_val = (isset($matches[3])?$matches[3]:false);
					$string_val = trim($string_val);
					$string_val = $this->_yaraDecodeString($Model, $string_val);
					
					// fixes issue with string line looking like: '$ = "text"'
					if(!$string_key)
					{
						$string_key = Inflector::slug(strtolower($string_val));
					}
					
					$rule['strings'][$string_key] = $string_val;
					$previous_string = trim($line);
					$line_match = true;
				}
				elseif($section == 'condition')
				{
					$line = trim($line);
					$line = $this->_yaraDecodeString($Model, $line);
					$rule['condition'][$line] = $line;
					$line_match = true;
				}
			}
			$previous_line = $line;
		}
		
		// place the open { on the same line as the rule line
		$string_raw = preg_replace('/(\s+)?\n+(\s+)?\{/', ' {', $string_raw);
		
		$rule_scope = false;
		$rule_name = false;
		$rule_raw = array();
		$indent = 0;
		$sig_key = false;
		foreach(explode("\n", $string_raw) as $i => $line)
		{
			$actual_open = false;
			$actual_close = false;
			$line_tokens = token_get_all('<'.'?php '. $line. '?'.'>');
			
			// match the end of group
			if(preg_match('/{/', $line))
			{
				$actual_open = false;
				foreach($line_tokens as $line_token)
				{
					if(!is_array($line_token) and $line_token == '{')
					{
						$actual_open = true;
					}
				}
			}
			if($actual_open)
			{
				$indent++;
				// start of the rule
				if($indent == 1)
				{
					$rule = $rules_default;
					$matches = array();
					
					if(preg_match('/^(private|global)?(\s+)?rule(.*){/i', trim($line), $matches))
					{
						$rule_scope = (isset($matches[1])?$matches[1]:false);
						$rule_name = (isset($matches[3])?$matches[3]:false);
						
						if($rule_scope)
						{
							$rule['scope'] = trim($rule_scope);
						}
						
						if($rule_name)
						{
							$rule_name = trim($rule_name);
							$rule_tags = array();
							
							if(stripos($rule_name, ':'))
							{
								list($rule_name, $rule_tags) = explode(':', $rule_name);
								$rule_tags = strtolower($rule_tags);
								$rule_tags = preg_split('/\s+/', $rule_tags);
								
								foreach($rule_tags as $j => $rule_tag)
								{
									if(!trim($rule_tag)) unset($rule_tags[$j]);
									$rule_tag = strtolower($rule_tag);
									$rule_tag = Inflector::slug($rule_tag);
									$rule_tags[$j] = $rule_tag;
								}
								sort($rule_tags);
							}
							$rule_name = trim($rule_name);
							$rule['name'] = $rule_name;
							$rule['tags'] = implode(',', $rule_tags);
						}
						
						$sig_key = ($rule['scope']?$rule['scope'].'_':false). $rule['name'];
						$sig_key = Inflector::slug($sig_key);
					}
				}
			}
			
			// match the end of group
			if(preg_match('/}/', $line))
			{
				$actual_close = false;
				foreach($line_tokens as $line_token)
				{
					if(!is_array($line_token) and $line_token == '}')
					{
						$actual_close = true;
					}
				}
			}
			if($actual_close)
			{
				$indent--;
				if($indent == 0)
				{
					$rule_scope = false;
					$rule_name = false;
					$rule_raw[$sig_key][] = $line;
				}
			}
			
			if($indent == 1 and $sig_key)
			{
				$rule_raw[$sig_key][] = $line;
			}
		}
		
		// rehash the signature and remove duplicates
		$_signatures = array();
		foreach($signatures as $k => $signature)
		{
			// order the strings and meta in alphabetical order
			if(isset($signature['meta']) and is_array($signature['meta']))
				ksort($signature['meta']);
			if(isset($signature['strings']) and is_array($signature['strings']))
				ksort($signature['strings']);
			
			$compiled = $this->Sig_YaraCompileSignature($Model, $signature, false);
			$hash = $this->Sig_getHash($Model, $compiled);
			$_signatures[$hash] = $signature;
			$_signatures[$hash]['hash'] = $hash;
			$_signatures[$hash]['compiled'] = $compiled;
			// compile the signature in a proper format
			if(isset($rule_raw[$k]))
			{
				$signatures[$k]['raw'] = implode("\n", $rule_raw[$k]);
			}
		}
		$signatures = $_signatures;
		unset($_signatures);
		
		return $signatures;
	}
	
	private function _cleanupYaraString(Model $Model, $string = false)
	{
		// remove all coments
		$string = $this->_stringRemoveComments($Model, $string);
		
		$string = $this->_yaraEncodeString($Model, $string);
		
		// place the open { on the same line as the rule line
		$string = preg_replace('/(\s+)?\n+(\s+)?\{/', ' {', $string);
		
		// encode some of the strings
		
		// place the signatures that are \ line split on their proper line
		$string = preg_replace('/\\\(\s+)?/i', ' ', $string);

		$lines = explode("\n", $string);
		
		foreach($lines as $i => $line)
		{
			// remove all empty lines and excess spaces
			$lines[$i] = trim($lines[$i]);
			if(!trim($lines[$i]))
			{
				unset($lines[$i]);
				continue;
			}
		}
		$string = implode("\n", $lines);
		
		return $string;
	}
	
	private function _yaraEncodeString(Model $Model, $string)
	{
		foreach($this->yara_encode_map as $toEncode => $encoded)
		{
			$string = str_replace($toEncode, $encoded, $string);
		}
		return $string;
	}
	
	private function _yaraDecodeString(Model $Model, $string)
	{
		foreach($this->yara_encode_map as $toEncode => $encoded)
		{
			$string = str_replace($encoded, $toEncode, $string);
		}
		return $string;
	}
	
	public function Sig_YaraCompileSignature(Model $Model, $data = array())
	{
		// $data should be a single signature in the format from the this::_parseYara();
		$lines = array();
		
		// first line that defines the rule with the tags
		
		$string = ($data['scope']?$data['scope'].' ':'').'rule '. $data['name'];
		
		$tags = array();
		if(isset($data['tags']) and $data['tags'])
		{
			$string .= ' : ';
			$tags = explode(',', $data['tags']);
			$tags = array_flip($tags);
			$tags = array_flip($tags);
			sort($tags);
			
			$string .= implode(' ', $tags);
		}
		$string .= ' {';
		
		$lines[] = $string;
		$string = '';
		
		
		// the meta data
		if(isset($data['meta']) and !empty($data['meta']))
		{	
			$lines[] = "\tmeta:";
			foreach($data['meta'] as $key => $value)
			{
				$lines[] = "\t\t". $key. ' = "'. trim(trim($value, '"')). '"';
			}
		}
		
		// the Strings
		if(isset($data['strings']) and !empty($data['strings']))
		{
			$lines[] = "\tstrings:";
			foreach($data['strings'] as $key => $value)
			{
				$lines[] = "\t\t$". $key. ' = '. trim($value);
			}
		}
		
		// the Conditions
		if(isset($data['condition']) and !empty($data['condition']))
		{
			$lines[] = "\tcondition:";
			foreach($data['condition'] as $condition)
			{
				$lines[] = "\t\t". trim($condition);
			}
		}
		
		$lines[] = '}';
		
		$lines = implode("\n", $lines);
		
		return $lines;
	}
	
	private function _parseSnort(Model $Model)
	{
		$signatures = array();
		$string = $this->_getString($Model);
		$string = $this->_cleanupSnortString($Model, $string);
		
		$rule_defaults = array(
			'name' => false,
			'hash' => false,
			'action' => false,
			'protocol' => false,
			'src_ip' => false,
			'src_port' => false,
			'direction' => false,
			'dest_ip' => false,
			'dest_port' => false,
			'options' => array(),
			'raw' => false,
		);
		
		$lines = explode("\n", $string);
		
		foreach($lines as $i => $line)
		{
			// each rule SHOULD be on it's own line
			// Action Protocol SrcIP SrcPort Direction DestIP DestPort (rule options)
			$matches = array();
			$line = trim($line);

			if(preg_match('/^(\w+)\s+(\w+)\s+(\S+)\s+(\$?[\w\d,\[\]]+)\s+([\<\-\>]+)\s+(\S+)\s+(\$?[\w\d,\[\]]+)\s+(\(.*\))?/', $line, $matches))
			{
				$hash = $this->Sig_getHash($Model, $line);
				$signatures[$hash] = $rule_defaults;
				$signatures[$hash]['hash'] = $hash;
				$signatures[$hash]['raw'] = $line;
				$signatures[$hash]['action'] = (isset($matches[1])?$matches[1]:false);
				$signatures[$hash]['protocol'] = (isset($matches[2])?$matches[2]:false);
				$signatures[$hash]['src_ip'] = (isset($matches[3])?$matches[3]:false);
				$signatures[$hash]['src_port'] = (isset($matches[4])?$matches[4]:false);
				$signatures[$hash]['dest_ip'] = (isset($matches[6])?$matches[6]:false);
				$signatures[$hash]['dest_port'] = (isset($matches[7])?$matches[7]:false);
				
				// figure out the direction
				
				if(isset($matches[5]))
				{
					$direction = trim($matches[5]);
					if( in_array($direction, array('>', '->')) )
					{
						$direction = 'in';
					}
					elseif( in_array($direction, array('<', '<-')) )
					{
						$direction = 'out';
					}
					elseif($direction == '<>')
					{
						$direction = 'both';
					}
					$signatures[$hash]['direction'] = $direction;
				}
				
				// figure out the options that are between the '()'
				if(isset($matches[8]))
				{
					$option_string = $matches[8];
					$options_string = preg_replace('/^\(/', '', $option_string);
					$options_string = preg_replace('/\)$/', '', $option_string);
					
					$tokens = token_get_all('<'.'?php '. $option_string. ' ?'.'>');
					$options = array();
					
					$option_key = false;
					$option_value = false;
					$option_sep = false;
					$option_count = 0;
					foreach($tokens as $ti => $token)
					{
						if((!is_array($token) and $token == ';') or $token[0] == 370)
						{
							$option_count++;
							if($option_key)
							{
								$option_key = strtolower(trim($option_key));
								if(isset($options[$option_key]))
								{
									if(!is_array($options[$option_key]))
									{
										$options[$option_key] = array($options[$option_key]);
									}
									$options[$option_key][] = trim($option_value);
								}
								else
								{
									$options[$option_key] = trim($option_value);
								}
							}
							$option_key = false;
							$option_value = false;
							$option_sep = false;
							continue;
						}
						elseif(!is_array($token) and $token == ':')
						{
							$option_sep = true;
							continue;
						}
							
						if(!$option_sep and is_array($token) and $token[0] == T_STRING)
						{
							$option_key = $token[1];
						}
						if($option_key and $option_sep)
						{
							$option_value .= (is_array($token)?$token[1]:$token);
						}
					}
					
					// format out the options besed on their type
					foreach($options as $k => $v)
					{
						// metadata
						//  Multiple keys are separated by a comma, while keys and values are separated by a space. 
						if($k == 'metadata' and is_string($v) and strpos($v, ',') !== false)
						{
							$v = explode(',', $v);
							foreach($v as $x => $y){ $v[$x] = trim($y); }
							sort($v);
						}
						
						if(is_array($v))
						{
							// for things like nocase;
							foreach($v as $x => $y)
							{
								if(!trim($y)) unset($v[$x]);
							}
							$options[$k] = implode('^^', $v);
						}
					}
					ksort($options);
					
					$signatures[$hash]['options'] = $options;
					if(!$signatures[$hash]['name'])
					{
						if(isset($options['msg']))
						{
							$signatures[$hash]['name'] = str_replace(array('"', "'"), '', $options['msg']);
						}
						elseif(isset($options['sid']))
						{
							$signatures[$hash]['name'] = 'sid:'. $options['sid'];
						}
						
						if(isset($options['rev']))
						{
							if($signatures[$hash]['name'])
							{
								$signatures[$hash]['name'] .= ' - ';
							}
							$signatures[$hash]['name'] .= 'rev:'. $options['rev'];
						}
					}
				}
			}
		}
		
		// rehash the signature and remove duplicates
		$_signatures = array();
		foreach($signatures as $k => $signature)
		{
			$compiled = $this->Sig_SnortCompileSignature($Model, $signature, false);
			$hash = $this->Sig_getHash($Model, $compiled);
			$_signatures[$hash] = $signature;
			$_signatures[$hash]['hash'] = $hash;
			$_signatures[$hash]['compiled'] = $compiled;
		}
		$signatures = $_signatures;
		unset($_signatures);
		
		return $signatures;
	}
	
	public function Sig_SnortCompileSignature(Model $Model, $data = array(), $make_pretty = false)
	{
		$rule_template = "%s %s %s %s %s %s %s (%s)";
		
		$direction = $data['direction'];
		$direction = (isset($this->snort_direction_map[$direction])?$this->snort_direction_map[$direction]:$direction);
		
		$options_template_key = "%s:%s;";
		$options_template_nokey = "%s;";
		
		$options = array();
		if(isset($data['options']))
		{
			ksort($data['options']);
			
			if($make_pretty and isset($data['options']['msg']))
			{
				$options[] = __($options_template_key, 'msg', $data['options']['msg']);
				unset($data['options']['msg']);
			}
			if($make_pretty and isset($data['options']['reference']))
			{
				$reference = $data['options']['reference'];
				unset($data['options']['reference']);
				
				$data['options'] = array_merge(array('reference' => $reference), $data['options']);
			}
			if($make_pretty and isset($data['options']['metadata']))
			{
				$metadata = $data['options']['metadata'];
				unset($data['options']['metadata']);
				
				$data['options'] = array_merge(array('metadata' => $metadata), $data['options']);
			}
		
			foreach($data['options'] as $k => $v)
			{
				if(strpos($v, '^^') != false)
				{
					$v = explode('^^', $v);
				}
				if(is_array($v))
				{
					// remove any possible duplicates
					$v = array_flip($v);
					$v = array_flip($v);
					
					foreach($v as $x) $options[] = __($options_template_key, $k, $x);
				}
				else
				{
					$v = trim($v);
					if($v or strlen((string) $v)) // incase $v is literally '0'
						$options[] = __($options_template_key, $k, $v);
					else
						$options[] = __($options_template_nokey, $k);
				}
			}
		}
		
		// remove any possible duplicates
		$options = array_flip($options);
		$options = array_flip($options);
		
		if($make_pretty)
		{
			$options = implode(" \ \n \t\t", $options);
		}
		else
		{
			$options = implode(' ', $options);
		}
		
		$rule = __($rule_template, 
			$data['action'],
			$data['protocol'],
			$data['src_ip'],
			$data['src_port'],
			$direction,
			$data['dest_ip'],
			$data['dest_port'],
			$options
		);
		return $rule;
	}
	
	private function _cleanupSnortString(Model $Model, $string = false)
	{
		// place the signatures that are \ line split on their proper line
		$string = preg_replace('/\\\(\s+)?/i', ' ', $string);

		$lines = explode("\n", $string);
		
		foreach($lines as $i => $line)
		{
			// remove all empty lines and excess spaces
			$lines[$i] = trim($lines[$i]);
			if(!trim($lines[$i]))
			{
				unset($lines[$i]);
				continue;
			}
		}
		$string = implode("\n", $lines);
		
		return $string;
	}
	
	private function _stringRemoveComments(Model $Model, $string = false)
	{
		$commentTokens = array(T_COMMENT);
		if (defined('T_DOC_COMMENT')) $commentTokens[] = T_DOC_COMMENT; // PHP 5
		if (defined('T_ML_COMMENT')) $commentTokens[] = T_ML_COMMENT;  // PHP 4
		
		// being lazy, so my editor will correctly parse this file/function
		$tokens = token_get_all('<'.'?php'. $string. '?'.'>');
		$comments = array();
		
		// exceptions 
		$exceptions = array(T_CONSTANT_ENCAPSED_STRING, T_STRING);
		
		$previous_token_type = false;
		
		foreach ($tokens as $token) 
		{
			if (is_array($token)) 
			{
				if (in_array($token[0], $commentTokens))
				{
					// allow things like hash tags in urls
					if($token[1]{0} == '#' and in_array($previous_token_type, $exceptions))
					{
						continue;
					}
					$comments[] = $token[1];
				}
				$previous_token_type = $token[0];
			}
		}
		
		foreach($comments as $comment)
		{
			$string = str_replace($comment, "\n", $string);
		}
		return $string;
	}
	
	public function Sig_getHash(Model $Model, $signature = false)
	{
		if($signature)
		{
			$signature = trim($signature);
			return sha1($signature);
		}
		return false;
	}
}