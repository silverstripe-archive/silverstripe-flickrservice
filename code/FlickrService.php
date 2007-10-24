<?php

/**
 * Used to connect to Flickr API via REST interface.
 */
class FlickrService extends RestfulService {
	private static $api_key;
	
	function __construct(){
		$this->baseURL = 'http://www.flickr.com/services/rest/';
		$this->checkErrors = true;
	}
	
	/*
	This will return API specific error messages.
	*/
	function errorCatch($response){
		$err_msg = $this->getAttribute($response, "err", Null, "msg");
	 if($err_msg)
		//user_error("Flickr Service Error : $err_msg", E_USER_ERROR);
	 	throw new Exception("Flickr Service Error : $err_msg");
	 else
	 	return $response;
	}
	
	static function setAPIKey($key){
		self::$api_key = $key;
	}
	
	function getAPIKey(){
		return self::$api_key;
	}
	
	function getPhotos($tags=NULL,$user_id="",$per_page=500, $page=1){
		$params = array(
			'method' => 'flickr.photos.search',
			'tags' => $tags,
			'user_id' => $user_id == "" ? "" : $this->User($user_id),
			'per_page' => $per_page,
			'page' => $page,
			'api_key' => $this->getAPIKey()
			);
		
		$this->setQueryString($params);
		$conn = $this->connect();
		
		$results = new Photos();
		$results->PhotoItems = $this->getAttributes($conn, 'photos', 'photo');	
		if((int)$results->PhotoItems->Count() > 0)
				$results->Paginate($this->getAttributes($conn, 'photos'));
					
		$results->addImageUrl($results->PhotoItems);
		$results->addImagePageUrl($results->PhotoItems); //gets individual image page url
		
		//Debug::show($results->Pagination);
		return $results;
	}
	
	function User($username){
		$params = array(
			'method' => 'flickr.people.findByUsername',
			'username' => $username,
			'api_key' => $this->getAPIKey()
		);
		$this->setQueryString($params);
		$conn = $this->connect();
		$result = $this->getAttribute($conn, 'user', NULL, 'nsid');
		return $result;
	}
	
	function getPhotoTitle($id){
		$params = array(
			'method' => 'flickr.photos.getInfo',
			'photo_id' => $id,
			'api_key' => $this->getAPIKey()
		);
		$this->setQueryString($params);
		$conn = $this->connect();
		$result = $this->getValue($conn, photo, title);
		return $result;
	}
	
	function getPhotoSet($id, $user, $per_page=500, $page=1){
		$params = array(
			'method' => 'flickr.photosets.getPhotos',
			'photoset_id' => $id,
			'per_page' => $per_page,
			'page' => $page,
			'api_key' => $this->getAPIKey()
		);
		$this->setQueryString($params);
		$conn = $this->connect();
		
		$results = new Photos();
		$results->PhotoItems = $this->getAttributes($conn, 'photoset', 'photo');	
		if((int)$results->PhotoItems->Count() > 0)
			$results->Paginate($this->getAttributes($conn, 'photoset'));
							
		$results->addImageUrl($results->PhotoItems);
		$results->addImagePageUrl($results->PhotoItems, $user); //gets individual image page url
		
		return $results;
	}
		
}

class Photos extends ViewableData {
	public $Photolist;
	private $Pagelist;
	private $TotalPhotos;
	
	function Paginate($pagination){
	$current_url = Director::currentURLSegment();

		foreach($pagination as $page)
		$current_page = $page->getField('page');
		$last_page = $page->getField('pages');
		$this->TotalPhotos = $page->getField('total');
		
		
		if($current_page > 1){
			$qs = http_build_query(array('page' => $current_page - 1));
			$this->Pagelist = "<a href='$current_url?$qs' class='prev'>&lt; Previous</a>";
		}
		
		if($current_page < 6)
			$start = 0;
		else
			$start = $current_page - 5;
		
		$end = $last_page < 10 ? $last_page : $start+10;
		
		for($i=$start; $i < $end ; $i++){
			$pagenum = $i + 1;
			if($pagenum != $current_page){
				$qs = http_build_query(array('page' => $pagenum));
				$page_item = "<a href='$current_url?$qs'>$pagenum</a>";
			}
			else 
				$page_item = "<span class='currentPage'>$pagenum</span>";
				
			$this->Pagelist .= $page_item;
		}
		
		if ($current_page < $last_page){
			$qs = http_build_query(array('page' => $current_page + 1));
			$this->Pagelist .= "<a href='$current_url?$qs' class='next'>Next &gt;</a>";
		}
			
		
		//Debug::show($pagination);
		//return $pages;
	}
	
	function getTotalPhotos(){
		return $this->TotalPhotos;
	}
	
	function getPages(){
		return $this->Pagelist;
	}
			
	protected function buildImageUrl($params){
		$image_path = self::buildImagePath($params);
		return "http://farm1.static.flickr.com/{$image_path}";
	}
	
	static function buildImagePath($params){
		return "{$params['server']}/{$params['id']}_{$params['secret']}";
	}
	
	protected function buildImagePageUrl($params){
		return "http://www.flickr.com/photos/{$params['owner']}/{$params['id']}";
		}
	
	function addImageUrl($results){
		foreach($results as $result){
			$urlinfo = array(
				"id" => $result->getField('id'),
				"server" => $result->getField('server'),
				"secret" => $result->getField('secret')
				);
			
			$result->setField('image_path', self::buildImagePath($urlinfo));
			
		}
	}
	
	function addImagePageUrl($results, $owner=""){
		foreach($results as $result){
			$urlinfo = array(
				"id" => $result->getField('id'),
				"owner" => $owner == "" ? $result->getField('owner') : $owner
				);
			
			$result->setField('page_url', $this->buildImagePageUrl($urlinfo));
			}
	}
		 
	}
?>