<?php

class WebInterface {
	
	private $uri = "";
	private $mime_type = "";
	private $xmlDOM = NULL;
	private $curl = NULL;
	
	
	
	function __construct($uri) {	
	
		require_once dirname(__FILE__) . '/curl.php';
		require_once dirname(__FILE__) . '/ganon.php';
		
		$this->uri = $uri;
		$this->curl = new Curl();
		
		$this->curl->setUserAgent('');
		
		
		$this->curl->get($uri);
		
		
		if ($this->curl->error) {
			throw new Exception('Failed to fetch URI.');
		}
		$this->xmlDOM = WebInterface::get_xml_DOM($this->curl->response);
	}
	
	public function is_html(){
		return ($this->xmlDOM !== false);
	}
	
	public function is_404() {
		#return ($this->curl->error_code == 404);
		return ($this->curl->error);
	}
	
	public function get_xml() {
		return $this->xmlDOM;
	}
	
	public function get_mime_type() {
		return $this->curl->response_headers['Content-Type'];
	}
	
	function replace_uri_domains_in_tags($old_domain, $new_domain, $tag_name, $attribute_name) {
		foreach($this->xmlDOM->getElementsByTagName($tag_name) as $tag) {
			$link_uri = $tag->getAttribute($attribute_name);			
		}
	}

	public static function filter_tag_URLs_for_local_host($haystack) {		
		global $static_electricity_settings;
		$host_domains = array();
		$hosts = $static_electricity_settings['multi_local_host_aliases'];		
		$host_domains[] = parse_url(home_url())['host'];
		$should_domains_be_replaced_in_links =  $static_electricity_settings['replace_uri_in_links'];
		$replacement_domain = $static_electricity_settings['replacement_uri_prefix'];
		
		foreach($hosts as $hu) {
			$host_domains[] = parse_url($hu)['host'];
		}
		
		$hosts[] = home_url();
		$retval = array();
		foreach ($haystack as $element) {
			$fixedUri = WebInterface::relative_to_absolute_uri($element, trailingslashit(home_url()));
	          $fixedUriParts = parse_url($fixedUri);
			if (array_search($fixedUriParts['host'], $host_domains) !== false){
				
				if ($should_domains_be_replaced_in_links) {
					foreach($hosts as $h){
						$fixedUri = str_ireplace($h,$replacement_domain,$fixedUri);
					}
				}
				array_push($retval, $fixedUri);
			}
			
				
		    
		}
		return $retval;
	}
	
	
	public function get_content(){
		return $this->curl->response;	
	}
	
	public function get_HTML_content() {
		return $this->xmlDOM->html();		
	}
	
	
	
///TODO: DRY this part
	
	public function get_local_linked_resources() {
		global $static_electricity_settings;
		$retval = array();
		$local = home_url();
		$localUrlParts = parse_url($local);

		
		if ($static_electricity_settings['scanning_options']['ahref'] == '1') {	
			$haystack = WebInterface::get_values_by_tag_attribute($this->xmlDOM, "a", "href");
			$retval = array_merge($retval, WebInterface::filter_tag_URLs_for_local_host($haystack));	
		}
	
		if ($static_electricity_settings['scanning_options']['img'] == '1') {	
			$haystack = WebInterface::get_values_by_tag_attribute($this->xmlDOM, "img", "src");
			$retval = array_merge($retval, WebInterface::filter_tag_URLs_for_local_host($haystack));	
		}
	
		if ($static_electricity_settings['scanning_options']['css'] == '1') {	
			$haystack = WebInterface::get_values_by_tag_attribute($this->xmlDOM, "link", "href");
			$retval = array_merge($retval, WebInterface::filter_tag_URLs_for_local_host($haystack));
		}
		
		if ($static_electricity_settings['scanning_options']['javascript'] == '1') {	
			$haystack = WebInterface::get_values_by_tag_attribute($this->xmlDOM, "script", "src");
			$retval = array_merge($retval, WebInterface::filter_tag_URLs_for_local_host($haystack));
		}
		return array_unique($retval);
	}
	
	
	
	private static function get_values_by_tag_attribute($xmlDOM, $tag_name, $attribute_name) {
		global $static_electricity_settings;
		$retval = array();
		$blag = $xmlDOM($tag_name);
		foreach($blag as $tag) {
				$value = $tag->$attribute_name;
				$u = strtok($value, "#" );
				$add_uploads = $static_electricity_settings['skip_index_files_in_uploads_folder'];
				$is_an_uploaded_file = strpos(parse_url($u)['path'], '/upload/') !== FALSE;
				$is_an_index_page = basename(parse_url($u)['path']) != $static_electricity_settings['index_page_filename'];
				
				$it_should_be_added = false;
				
				if ($add_uploads)
					$it_should_be_added = true;
					
				if (!$is_an_index_page)
					$it_should_be_added = true;
					
				if ($is_an_index_page && !$is_an_uploaded_file )
					$it_should_be_added = true;
					
				if ($it_should_be_added)
					array_push($retval, $u);
				}
		return $retval;
	}
	
	public static function get_xml_DOM($content) {
		$retval = str_get_dom($content);
		return $retval;
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
