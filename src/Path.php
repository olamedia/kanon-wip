<?php

namespace Kanon;

/**
 *
 * Usage:
 * $path = new Path('/about/me', '/');
 * $path = new Path($filename); // DIRECTORY_SEPARATOR will be used
 * 
 */
class Path implements \Countable, \IteratorAggregate, \ArrayAccess{

	protected $_op = false;
	protected $_segments = array();
	protected $_ed = false;
	protected $_delimiter = '/';

	public function __construct($path = null, $delimiter = null) {
		if (null === $delimiter){
			$delimiter = \DIRECTORY_SEPARATOR;
		}
		$this->_delimiter = $delimiter;
		if (\is_string($path)){
			$this->loadString($path);
		}else{
			$this->_segments = array();
		}
	}
	
	public static function create($path, $delimiter = null){
		return new self($path, $delimiter);
	}
	
	public static function fromString($path, $delimiter = null){
		return self::create($path, $delimiter);
	}

	public function loadString($path){
		$a = \explode($this->_delimiter, $path);
		$this->_op = ('' === \reset($a));
		$this->_ed = ('' === \end($a));
		if ($this->_op){
			\array_shift($a);
		}
		if ($this->_ed){
			\array_pop($a);
		}
		$this->_segments = $a;
		return $this;
	}

	public function getIterator(){
		return new \ArrayIterator($this->_segments); // SPL, PHP 5.0
	}
	
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->_segments[] = $value;
		} else {
			$this->_segments[$offset] = $value;
		}
	}
	
	public function offsetExists($offset) {
		return isset($this->_segments[$offset]);
	}
	
	public function offsetUnset($offset) {
		unset($this->_segments[$offset]);
	}
	
	public function offsetGet($offset) {
		return \array_key_exists($offset, $this->_segments)?$this->_segments[$offset]:null;
	}

	public function count(){
		return \count($this->_segments);
	}

	public function get($index){
		return $this->_segments[$index];
	}
	
	public function setSegments($segments = array()){
		$this->_segments = $segments;
	}

	public function getSegments(){
		return $this->_segments;
	}

	public function __toString(){
		return ($this->_op?$this->_delimiter:'').\implode($this->_delimiter, $this->_segments->getArrayCopy()).($this->_ed?$this->_delimiter:'');
	}

}
