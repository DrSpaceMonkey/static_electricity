<?php

class WebInterface {
	
	private $uri = "";
	private $content = "";
	private $xmlDOM = NULL;
	
	function __construct($uri) {
		$this->uri = $uri;
		
		$this->content = WebInterface::get_uri_content($uri);
		if ($this->content === false) {
			throw new Exception('Failed to fetch URI.');
			}

		$this->xmlDOM = WebInterface::get_xml_DOM($this->content);
		if ($this->xmlDOM === false) {
			throw new Exception('Failed to parse URI.');
			}
		
			
	}
	
	function replace_uri_domains_in_links($old_domain, $new_domain) {
		foreach($xml->getElementsByTagName('a') as $tag) {
			$link_uri = $tag->getAttribute('href');			
		}
	}


	public static function filter_tag_URLs_by_host($haystack, $local) {
		$retval = array();
		$localUrlParts = parse_url($local);
		foreach ($haystack as $element) {
			$fixedUri = WebInterface::relative_to_absolute_uri($element, $local . '/');
	               	$fixedUriParts = parse_url($fixedUri);
			if (strcasecmp($fixedUriParts['host'],$localUrlParts['host']) == 0) {
				array_push($retval, $fixedUri);
		    }
		}

		
		return $retval;
	}

	
	public function get_local_linked_resources() {
		$retval = array();
		$local = home_url();		
		$localUrlParts = parse_url($local);

		$haystack = WebInterface::get_values_by_tag_attribute($this->xmlDOM, "a", "href");
		$retval = array_merge($retval, WebInterface::filter_tag_URLs_by_host($haystack, $local));
	
	
		$haystack = WebInterface::get_values_by_tag_attribute($this->xmlDOM, "img", "src");
		$retval = array_merge($retval, WebInterface::filter_tag_URLs_by_host($haystack, $local));
	
	
		$haystack = WebInterface::get_values_by_tag_attribute($this->xmlDOM, "link", "href");
		$retval = array_merge($retval, WebInterface::filter_tag_URLs_by_host($haystack, $local));


		
					
		return $retval;
		
	}
	
	private static function get_values_by_tag_attribute($xmlDOM, $tag_name, $attribute_name) {
		$retval = array();
		
		foreach($xmlDOM->getElementsByTagName($tag_name) as $tag) {
			array_push($retval, $tag->getAttribute($attribute_name));
		}
		return $retval;
	}
	
	public static function get_xml_DOM($content) {
		$retval = new DOMDocument();
		$result = @$retval->loadHTML($content);
		
		if ($result === false)
			return false;
		
		return $retval;
	}
	
	public static function get_uri_content($uri) {

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $uri);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$resp = curl_exec($curl);

		curl_close($curl);

		return $resp;
	}
	
	public static function relative_to_absolute_uri($rel, $base)
  {
		if(strpos($rel,"//")===0)
		{
		return "http:".$rel;
		}
		/* return if  already absolute URL */
		if  (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;
		/* queries and  anchors */
		if ($rel[0]=='#'  || $rel[0]=='?') return $base.$rel;
		/* parse base URL  and convert to local variables:
		$scheme, $host,  $path */
		extract(parse_url($base));
		/* remove  non-directory element from path */
		$path = preg_replace('#/[^/]*$#',  '', $path);
		/* destroy path if  relative url points to root */
		if ($rel[0] ==  '/') $path = '';
		/* dirty absolute  URL */
		$abs =  "$host$path/$rel";
		/* replace '//' or  '/./' or '/foo/../' with '/' */
		$re =  array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
		for($n=1; $n>0;  $abs=preg_replace($re, '/', $abs, -1, $n)) {}
		/* absolute URL is  ready! */
		return  $scheme.'://'.$abs;
  }
}
