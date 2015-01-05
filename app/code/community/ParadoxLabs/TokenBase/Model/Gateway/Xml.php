<?php
/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 * 
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 * 
 * Want to customize or need help with your store?
 *  Phone: 717-431-3330
 *  Email: sales@paradoxlabs.com
 *
 * @category	ParadoxLabs
 * @package		TokenBase
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

/**
 * Integrates Array2XML and XML2Array for XML parsing.
 * Classes are modified from their original form to match conventions and use case.
 * 
 * Array2XML: A class to convert array in PHP to XML
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.8 (02 May 2012)
 * 
 * XML2Array: A class to convert XML to array in PHP
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-xml-to-array-in-php-xml2array
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.2 (04 Mar 2012)
 */

class ParadoxLabs_TokenBase_Model_Gateway_Xml
{
	private static $xml      = null;
	private static $encoding = 'UTF-8';
	
	/**
	 * Initialize the root XML node [optional]
	 * @param $version
	 * @param $encoding
	 * @param $format_output
	 */
	public static function init( $version = '1.0', $encoding = 'UTF-8', $format_output = true )
	{
		self::$xml = new DomDocument($version, $encoding);
		self::$xml->formatOutput = $format_output;
		self::$encoding = $encoding;
	}
	
	/**
	 * Convert an Array to XML
	 * @param string $node_name - name of the root node to be converted
	 * @param array $arr - aray to be converterd
	 * @return DomDocument
	 */
	public static function &createXML( $node_name, $arr=array() )
	{
		$xml = self::_getXMLRoot();
		$xml->appendChild(self::convertArrayToXml($node_name, $arr));
		
		self::$xml = null;
		return $xml;
	}
	
	/**
	 * Convert an Array to XML
	 * @param string $node_name - name of the root node to be converted
	 * @param array $arr - aray to be converterd
	 * @return DOMNode
	 */
	private static function &convertArrayToXml( $node_name, $arr=array() )
	{
		$xml = self::_getXMLRoot();
		$node = $xml->createElement($node_name);
		
		if(is_array($arr)){
			// get the attributes first.;
			if(isset($arr['@attributes'])) {
				foreach($arr['@attributes'] as $key => $value) {
					if(!self::_isValidTagName($key)) {
						throw new Exception('[Array2XML] Illegal character in attribute name. attribute: '.$key.' in node: '.$node_name);
					}
					$node->setAttribute($key, self::_bool2str($value));
				}
				unset($arr['@attributes']); //remove the key from the array once done.
			}
			
			// check if it has a value stored in @value, if yes store the value and return
			// else check if its directly stored as string
			if(isset($arr['@value'])) {
				$node->appendChild($xml->createTextNode(self::_bool2str($arr['@value'])));
				unset($arr['@value']);    //remove the key from the array once done.
				//return from recursion, as a note with value cannot have child nodes.
				return $node;
			} else if(isset($arr['@cdata'])) {
				$node->appendChild($xml->createCDATASection(self::_bool2str($arr['@cdata'])));
				unset($arr['@cdata']);    //remove the key from the array once done.
				//return from recursion, as a note with cdata cannot have child nodes.
				return $node;
			}
		}
		
		//create subnodes using recursion
		if(is_array($arr)){
			// recurse to get the node for that key
			foreach($arr as $key=>$value){
				if(!self::_isValidTagName($key)) {
					throw new Exception('[Array2XML] Illegal character in tag name. tag: '.$key.' in node: '.$node_name);
				}
				if(is_array($value) && is_numeric(key($value))) {
					// MORE THAN ONE NODE OF ITS KIND;
					// if the new array is numeric index, means it is array of nodes of the same kind
					// it should follow the parent key name
					foreach($value as $k=>$v){
						$node->appendChild(self::convertArrayToXml($key, $v));
					}
				} else {
					// ONLY ONE NODE OF ITS KIND
					$node->appendChild(self::convertArrayToXml($key, $value));
				}
				unset($arr[$key]); //remove the key from the array once done.
			}
		}
		
		// after we are done with all the keys in the array (if it is one)
		// we check if it has any text value, if yes, append it.
		if(!is_array($arr)) {
			$node->appendChild($xml->createTextNode(self::_bool2str($arr)));
		}
		
		return $node;
	}
	
	/**
	 * Convert XML to Array
	 * @param string $node_name - name of the root node to be converted
	 * @param array $arr - aray to be converterd
	 * @return DOMDocument
	 */
	public static function &createArray( $input_xml )
	{
		$xml = self::_getXMLRoot();
		if(is_string($input_xml)) {
			$parsed = @$xml->loadXML($input_xml);
			if(!$parsed) {
				throw new Exception('[XML2Array] Error parsing the XML string.');
			}
		}
		else {
			if(get_class($input_xml) != 'DOMDocument') {
				throw new Exception('[XML2Array] The input XML object should be of type: DOMDocument.');
			}
			$xml = self::$xml = $input_xml;
		}
		
		$array = self::convertXmlToArray($xml->documentElement);
		self::$xml = null;
		
		return $array;
	}
	
	/**
	 * Convert XML to array
	 * @param mixed $node - XML as a string or as an object of DOMDocument
	 * @return mixed
	 */
	private static function &convertXmlToArray( $node )
	{
		$output = array();
		
		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
				$output['@cdata'] = trim($node->textContent);
				break;
				
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;
				
			case XML_ELEMENT_NODE:
				
				// for each child node, call the covert function recursively
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
					$child = $node->childNodes->item($i);
					$v = self::convertXmlToArray($child);
					if(isset($child->tagName)) {
						$t = $child->tagName;
						
						// assume more nodes of same kind are coming
						if(!isset($output[$t])) {
							$output[$t] = array();
						}
						$output[$t][] = $v;
					} else {
						//check if it is not an empty text node
						if($v !== '') {
							$output = $v;
						}
					}
				}
				
				if(is_array($output)) {
					// if only one node of its kind, assign it directly instead if array($value);
					foreach ($output as $t => $v) {
						if(is_array($v) && count($v)==1) {
							$output[$t] = $v[0];
						}
					}
					if(empty($output)) {
						$output = '';
					}
				}
				
				// loop through the attributes and collect them
				if($node->attributes->length) {
					$a = array();
					foreach($node->attributes as $attrName => $attrNode) {
						$a[$attrName] = (string) $attrNode->value;
					}
					// if its an leaf node, store the value in @value instead of directly storing it.
					if(!is_array($output)) {
						$output = array('@value' => $output);
					}
					$output['@attributes'] = $a;
				}
				break;
		}
		
		return $output;
	}
	
	/*
	 * Get the root XML node, if there isn't one, create it.
	 */
	private static function _getXMLRoot()
	{
		if(empty(self::$xml)) {
			self::init();
		}
		
		return self::$xml;
	}
	
	/*
	 * Get string representation of boolean value
	 */
	private static function _bool2str( $v )
	{
		$v = $v === true ? 'true' : $v;
		$v = $v === false ? 'false' : $v;
		
		return $v;
	}
	
	/*
	 * Check if the tag name or attribute name contains illegal characters
	 * Ref: http://www.w3.org/TR/xml/#sec-common-syn
	 */
	private static function _isValidTagName( $tag )
	{
		$pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
		
		return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
	}
}
