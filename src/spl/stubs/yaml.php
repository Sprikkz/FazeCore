<?php
 
/**
 * YAML extension stub file for code completion purposes
 *
 * WARNING: Do not include this file
 *
 */

const YAML_ANY_SCALAR_STYLE = 0;
const YAML_PLAIN_SCALAR_STYLE = 1;
const YAML_SINGLE_QUOTED_SCALAR_STYLE = 0;
const YAML_DOUBLE_QUOTED_SCALAR_STYLE = 0;
const YAML_LITERAL_SCALAR_STYLE = 0;
const YAML_FOLDED_SCALAR_STYLE = 0;

const YAML_NULL_TAG = "tag:yaml.org,2002:null";
const YAML_BOOL_TAG = "tag:yaml.org,2002:bool";
const YAML_STR_TAG = "tag:yaml.org,2002:str";
const YAML_INT_TAG = "tag:yaml.org,2002:int";
const YAML_FLOAT_TAG = "tag:yaml.org,2002:float";
const YAML_TIMESTAMP_TAG = "tag:yaml.org,2002:timestamp";
const YAML_SEQ_TAG = "tag:yaml.org,2002:seq";
const YAML_MAP_TAG = "tag:yaml.org,2002:map";
const YAML_PHP_TAG = "!php/object";

const YAML_ANY_ENCODING = 0;
const YAML_UTF8_ENCODING = 1;
const YAML_UTF16LE_ENCODING = 2;
const YAML_UTF16BE_ENCODING = 3;


const YAML_ANY_BREAK = 0;
const YAML_CR_BREAK = 1;
const YAML_LN_BREAK = 2;
const YAML_CRLN_BREAK = 3;

/**
 * Send the YAML representation of a value to a file
 *
 * @param string $filename
 * @param mixed  $data
 * @param int    $encoding
 * @param int    $linebreak
 * @param array  $callbacks
 *
 * @link http://www.php.net/manual/en/function.yaml-emit-file.php
 * @return bool
 */
function yaml_emit_file($filename, $data, $encoding = YAML_ANY_ENCODING, $linebreak = YAML_ANY_BREAK, array $callbacks = []){
}

/**
 * Returns the YAML representation of a value
 *
 * @param mixed $data
 * @param int   $encoding
 * @param int   $linebreak
 * @param array $callbacks
 *
 * @link http://www.php.net/manual/en/function.yaml-emit.php
 * @return string
 */
function yaml_emit($data, $encoding = YAML_ANY_ENCODING, $linebreak = YAML_ANY_BREAK, array $callbacks = []){
}

/**
 * Parse a YAML stream from a file
 *
 * @param string $filename
 * @param int    $pos
 * @param int    &$ndocs
 * @param array  $callbacks
 *
 * @link http://www.php.net/manual/en/function.yaml-parse-file.php
 * @return mixed
 */
function yaml_parse_file($filename, $pos = 0, &$ndocs = null, array $callbacks = []){
}

/**
 * Parse a YAML stream from a URL
 *
 * @param string $url
 * @param int    $pos
 * @param int    &$ndocs
 * @param array  $callbacks
 *
 * @link http://www.php.net/manual/en/function.yaml-parse-url.php
 * @return mixed
 */
function yaml_parse_url($url, $pos = 0, &$ndocs = null, array $callbacks = []){
}

/**
 * Parse a YAML stream
 *
 * @param string $input
 * @param int    $pos
 * @param int    &$ndocs
 * @param array  $callbacks
 *
 * @link http://www.php.net/manual/en/function.yaml-parse.php
 * @return mixed
 */
function yaml_parse($input, $pos = 0, &$ndocs = null, array $callbacks = []){
}