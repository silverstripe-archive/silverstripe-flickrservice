<?php

/**
 * Used to connect to Flickr API via REST interface.
 */
class FlickrService extends RestfulService {
	private static $api_key;
	
	/**
 	* Creates a new FlickrService object.
 	* @param expiry - Set the cache expiry time or TTL of the response
 	*/
	function __construct($expiry=NULL){
		parent::__construct('http://www.flickr.com/services/rest/', $expiry);
		$this->checkErrors = true;
	}
	
	/**
	* This will return API specific error messages. 
	* @param response - Response with the error message
	*/
	function errorCatch($response){
		$err_msg = $this->getAttribute($response, "err", Null, "msg");
		$err_code = $this->getAttribute($response, "err", Null, "code");
	 if($err_msg){
	 	if($err_code == 100){
	 		user_error("You need to set the Flickr API key so that your SilverStripe website is permitted to commnicate with Flickr.<br/>Get one at http://www.flickr.com/services/api/keys/apply/ and add the code to mysite/_config.php, e.g: <br/> FlickrService::setAPIKey('YOUR-KEY-HERE'); ", E_USER_ERROR);
	 	}
	 	else {
	 		user_error("Flickr Service Error : $err_msg", E_USER_ERROR);
	 		}
	 	}
	 else {
	 	return $response;
	 	}
	}
	
	/**
	* Sets the Flickr API key 
	* @param key - User defined flickr key
	*/
	static function setAPIKey($key){
		self::$api_key = $key;
	}
	
	/**
	* Gets the Flickr API key
	*/
	function getAPIKey(){
		return self::$api_key;
	}
	
	/**
	* Get Photos based on defined criteria 
	* @param tags - Tags to retrive the photos from
	* @param user_id - Flickr User ID of the person, whom you want to get the photos
	* @param per_page - Photos per page. Defaults to flickr default 500
	* @param page - Page to retrive
	* @param sort - Sorting method. Deafults to date-posted-desc. The possible values are: date-posted-asc, date-posted-desc, date-taken-asc, date-taken-desc, interestingness-desc, interestingness-asc, and relevance. 
	*/
	function getPhotos($tags=NULL,$user_id="",$per_page=500, $page=1, $sort="date-posted-desc"){
		$params = array(
			'method' => 'flickr.photos.search',
			'tags' => $tags,
			'user_id' => $user_id == "" ? "" : $this->User($user_id),
			'per_page' => $per_page,
			'page' => $page,
			'sort' => $sort,
			'api_key' => $this->getAPIKey()
			);
		
		$this->setQueryString($params);
		$conn = $this->connect();
		
		$results = new FlickrService_Photos();
		$results->PhotoItems = $this->getAttributes($conn, 'photos', 'photo');	
		if((int)$results->PhotoItems->Count() > 0)
				$results->Paginate($this->getAttributes($conn, 'photos'));
					
		$results->addImageUrl($results->PhotoItems);
		$results->addImagePageUrl($results->PhotoItems, $user_id); //gets individual image page url
		
		//Debug::show($results->Pagination);
		return $results;
	}
	
	/**
	* Retrives a Flickr User ID of based on the user's public name 
	* @param username - Flickr's public name of the user
	*/
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
	
	/**
	* Retrives the title of a photo 
	* @param id - photo id
	*/
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
	
	/**
	* Retrives a Flickr photoset 
	* @param id - Id of the photoset. Usually you can find from the photoset URL eg: http://www.flickr.com/photos/userid/sets/PHOTOSETID/
	* @param per_page - Photos per page
	* @param page - page to display
	*/
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
		
		$results = new FlickrService_Photos();
		$results->PhotoItems = $this->getAttributes($conn, 'photoset', 'photo');	
		if((int)$results->PhotoItems->Count() > 0)
			$results->Paginate($this->getAttributes($conn, 'photoset'));
							
		$results->addImageUrl($results->PhotoItems);
		$results->addImagePageUrl($results->PhotoItems, $user); //gets individual image page url
		
		return $results;
	}
		
}

class FlickrService_Photos extends ViewableData {
	public $Photolist;
	private $Pagelist;
	private $TotalPhotos;
	
	/**
	* Paginate the photo results 
	* @param pagination
	*/
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
	
	/**
	* Returns the total photos found in query
	*/
	function getTotalPhotos(){
		return $this->TotalPhotos;
	}
	
	/**
	* Returns the number of pages available
	*/
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
	
	/**
	* Create the static image URL of each photo returned. 
	* @param results - returned flickr response
	*/
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
	
	/**
	* Create the URL of flickr page of each image returned 
	* @param results - returned flickr response
	* @param owner - Flickr ID of the owner of the photo
	*/
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