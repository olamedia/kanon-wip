<?php

namespace Kanon;

use \Kanon\ServerGlobals;

/**
 *
 * Provides following:
 * - HTTP protocol
 * - HTTP headers (Including useragent etc)
 * - HTTP method (GET/POST etc)
 * - Requested URI
 * - Is secure (HTTPS)
 * @author olamedia
 *
 */
class HttpRequest{
	protected $_protocol = 'HTTP/1.0';
	protected $_isSecure = false;
	protected $_method = 'GET';
	protected $_host;
	protected $_port = 80;
	protected $_uriString = '/';
	public static function create(){
		return new self();
	}
	public static function fromGlobals(){
		return self::create()
		->setProtocol(ServerGlobals::getProtocol())
		->setSecure(ServerGlobals::isSecure()) // FIXME
		->setHost(ServerGlobals::getHost())
		->setPort(ServerGlobals::getPort())
		->setMethod(ServerGlobals::getMethod())
		->setUriString(ServerGlobals::getRequestUriString());
	}
	public static function forceSsl(){
		header('Status-Code: 301');
		header('Location: https://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);
	}
	public function setProtocol($protocol){
		$this->_protocol = $protocol;
		return $this;
	}
	public function getProtocol(){
		return $this->_protocol;
	}
	public function setSecure($isSecure = true){
		$this->_isSecure = $isSecure;
		return $this;
	}
	public function isSecure(){
		return $this->_isSecure;
	}
	public function getScheme(){
		return $this->isSecure()?'https':'http';
	}
	public function setHost($host){
		$this->_host = $host;
		return $this;
	}
	public function getHost(){
		return $this->_host;
	}
	public function setPort($port){
		$this->_port = $port;
		return $this;
	}
	public function getPort(){
		return $this->_port;
	}
	public function setMethod($method){
		$this->_method = $method;
		return $this;
	}
	public function getMethod(){
		return $this->_method;
	}
	public function setUriString($uriString){
		$this->_uriString = $uriString;
		return $this;
	}
	public function getUriString(){
		return $this->_uriString;
	}

}
