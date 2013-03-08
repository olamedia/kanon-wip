<?php
/*
 * This file is part of the kanon package.
 * Copyright (c) 2011 olamedia <olamedia@gmail.com>
 *
 * Licensed under The MIT License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kanon;

/**
 * Helper for importing $_SERVER globals
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class ServerGlobals{
	public static function get($name, $default = null){
		if (\func_num_args() > 2){
			// simplify multiple keys checking
			// Note: $default is required!
			// example getServerParameter('DOCUMENT_URI', 'REQUEST_URI', '');
			$args = \func_get_args();
			$default = \array_pop($args);
			foreach ($args as $name){
				if (\array_key_exists($name, $_SERVER)){
					return $_SERVER[$name];
				}
			}
		}
		return \array_key_exists($name, $_SERVER)?$_SERVER[$name]:$default;
	}
	public static function getHttpHeader($name, $default = null){
		return self::get('HTTP_'.\strtoupper(\strtr($name, '-', '_')), $default);
	}
	public static function getProtocol($default = null){
		return self::get('SERVER_PROTOCOL', $default);
	}
	public static function isSecure(){
		// Set to a non-empty value if the script was queried through the HTTPS protocol.
		// Note that when using ISAPI with IIS, the value will be off if the request was not made through the HTTPS protocol.
		return 'on' === \strtolower(self::get('HTTPS')) || 1 == self::get('HTTPS');
	}
	public static function getPort($default = null){
		return \intval(self::get('SERVER_PORT', $default));
	}
	public static function getMethod($default = null){
		$method = \strtoupper(self::get('REQUEST_METHOD', $default));
		if ('POST' === $method){
			// http://code.google.com/apis/gdata/docs/2.0/basics.html#UpdatingEntry
			$over = self::getHttpHeader('X-HTTP-Method-Override');
			if (null !== $over){
				$method = \strtoupper($over);
			}
		}
		return $method;
	}
	public static function isAjax(){
		return 'XMLHttpRequest' === self::getHttpHeader('X-Requested-With');
	}
	public static function getReferer($default = null){
		return self::getHttpHeader('Referer', $default);
	}
	public static function getUserAgent($default = null){
		return self::getHttpHeader('User-Agent', $default);
	}
	private static function removeQuery($path){
		if (false !== ($p = \strpos($path, '?'))){
			return \substr($path, 0, $p);
		}
		return $path;
	}
	private static function removeSchemeAndHost($path){
		// FIXME
/*	$schemeAndHttpHost = $this->getSchemeAndHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }*/
		return $path;
	}
	public static function getPath(){
		return self::removeQuery(self::getPathDraft());
	}
	private static function getPathDraft(){
		if (false !== \stripos(\PHP_OS, 'WIN')){
			// Check for IIS
			$uri = self::getHttpHeader(
				'X-Original-URL', // IIS with Microsoft Rewrite Module
				'X-Rewrite-URL', // IIS with ISAPI_Rewrite
				null
			);
			if (null !== $uri){
				return $uri;
			}
			if (1 == self::get('IIS_WasUrlRewritten')){
				// IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
				$uri = self::get('UNENCODED_URL', '');
				if ('' !== $uri){
					return $uri;
				}
			}
		}
		// Nginx SSI
		$uri = self::get('DOCUMENT_URI'); // Without query
		if (null !== $uri){
			return $uri;
		}
		// STANDARD CASE
		$uri = self::get('REQUEST_URI'); // With query
		if (null !== $uri){
			// HTTP proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
			return self::removeSchemeAndHost($uri);
		}
		// IIS 5.0, PHP as CGI
		// The IIS 5.0 doesn't have a rewrite mechanism, but it can deal with PATHINFO.
		// Also there is no REQUEST_URI in $_SERVER.
		// But there is an ORIG_PATH_INFO that could be combined with QUERY_STRING to get the same information as in REQUEST_URI.
		// Original version of 'PATH_INFO' before processed by PHP.
		return self::get('ORIG_PATH_INFO'); // Without query
    }
	/**
	 * Note: Nginx passes / as REQUEST_URI for SSI requests, use DOCUMENT_URI instead
	 * @param string $default
	 */
	public static function getUriString($default = null){
		$qs = self::getQueryString();
		return self::getPath().(empty($qs)?'':'?'.$qs);
	}
	public static function getTime($default = null){
		return self::get('REQUEST_TIME_FLOAT', 'REQUEST_TIME', $default);
	}
	public static function getQueryString($default = null){
		return self::get('QUERY_STRING', $default);
	}
	public static function getServerAddr($default = null){
		return self::get('SERVER_ADDR',	$default);
	}
	public static function getServerName(){
		$ip = self::getServerAddr();
		// host is lowercase as per RFC 952/2181
		$host = \strtolower(self::get('HTTP_HOST', 'SERVER_NAME'));
		if (null !== $host){
			if (false !== ($p = \strpos($host, ':'))){
				$host = \trim(\substr($host, 0, $p));
			}
			$host = \trim($host);
			// as the host can come from the user (HTTP_HOST and depending on the configuration, SERVER_NAME too can come from the user)
			// check that it does not contain forbidden characters (see RFC 952 and RFC 2181)
			
		}else{
			$host = $ip;
		}
		return $host;
	}
	public static function getDomainName(){
		$n = self::getHost();
		if (\strpos($n, 'www.') === 0){
			return \substr($n, 4);
		}
		return $n;
	}
	public static function getScriptFilename($default = null){
		return self::get('SCRIPT_FILENAME', 'ORIG_SCRIPT_NAME',	$default);
	}
	public static function dumpHtml(){
		echo '<style type="text/css">
			.serverGlobalsDump{font-family: sans-serif;font-size: 12px;border-collapse: collapse; border: solid 1px #666;background: #fff;}
			.serverGlobalsDump td{font-family: inherit;font-size: inherit;padding: 3px 10px; border-bottom: solid 1px #ccc;}
			.serverGlobalsDump th{font-family: inherit;font-size: inherit;padding: 3px 10px;text-align: right; border-right: solid 1px #666; border-bottom: solid 1px #ccc;background: #f4f4f4;}</style>';
		echo '<table class="serverGlobalsDump">';
		echo '<tr><th>PHP</th><td>'.\htmlspecialchars(\PHP_VERSION).'</td></tr>';
		echo '<tr><th>Protocol</th><td>'.\htmlspecialchars(self::getProtocol('')).'</td></tr>';
		echo '<tr><th>Secure</th><td>'.(self::isSecure()?'true':'false').'</td></tr>';
		echo '<tr><th>Server Name</th><td>'.\htmlspecialchars(self::getServerName('')).'</td></tr>';
		echo '<tr><th>Server Addr</th><td>'.\htmlspecialchars(self::getServerAddr('')).'</td></tr>';
		echo '<tr><th>Port</th><td>'.\htmlspecialchars(self::getPort('')).'</td></tr>';
		echo '<tr><th>Method</th><td>'.\htmlspecialchars(self::getMethod('')).'</td></tr>';
		echo '<tr><th>Uri</th><td>'.\htmlspecialchars(self::getUriString('')).'</td></tr>';
		echo '<tr><th>Path</th><td>'.\htmlspecialchars(self::getPath('')).'</td></tr>';
		echo '<tr><th>Query String</th><td>'.\htmlspecialchars(self::getQueryString('')).'</td></tr>';
		echo '<tr><th>Time</th><td>'.\htmlspecialchars(self::getTime('')).'</td></tr>';
		echo '<tr><th>Referer</th><td>'.\htmlspecialchars(self::getReferer('')).'</td></tr>';
		echo '<tr><th>User-Agent</th><td>'.\htmlspecialchars(self::getUserAgent('')).'</td></tr>';
		echo '<tr><th><hr /></th><td><hr /></td></tr>';
		foreach ($_SERVER as $k => $v){
			echo '<tr><th>'.\htmlspecialchars($k).'</th><td>'.\htmlspecialchars(\is_array($v)?print_r($v, true):$v).'</td></tr>';
		}
		echo '</table>';
	}
}
