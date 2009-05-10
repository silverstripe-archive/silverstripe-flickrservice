<?php

class FlickrWidget extends Widget {
	static $db = array(
		"User" => "Varchar",
		"NumberToShow" => "Int",
		"Sortby" => "Varchar"
	);
	
	static $defaults = array(
		"Method" => 2,
		"NumberToShow" => 8,
		"Sortby" => "date-posted-desc"
	);
	
	static $title;
	static $cmsTitle;
	static $description;
	
	function __construct() {
		$title = _t('FlickrWidget.PHOTOS','Photos');
		$cmsTitle = _t('FlickrWidget.FLICKRPHOTOS','Flickr Photos');
		$description = _t('FlickrWidget.FLICKRPHOTOSDESCRIPTION','Display your Flickr photos.');
	}
	
	function Photos() {
		Requirements::javascript( "flickrservice/javascript/prototype.js" );
	  	Requirements::javascript( "flickrservice/javascript/effects.js" );
	  	Requirements::javascript( "flickrservice/javascript/lightwindow.js" );
	  	
	  	Requirements::css("flickrservice/css/FlickrGallery.css");
		Requirements::css("flickrservice/css/lightwindow.css");
      
      	if( $pos = strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'MSIE' ) ) {
      		$version = substr( $_SERVER[ 'HTTP_USER_AGENT' ], $pos + 5, 3 );
      		if( $version < 7 ) {
					Requirements::css( "flickrservice/css/lightwindowIE6.css" );
      		}
      	}
		
		$flickr = new FlickrService();
		
		try {
		$photos = $flickr->getPhotos(NULL, $this->User, $this->NumberToShow, 1, $this->Sortby);			
		
		}
		catch(Exception $e) {
			return false;
		}
		
		$output = new DataObjectSet();
		foreach($photos->PhotoItems as $photo) {
			$output->push(new ArrayData(array(
				"Title" => htmlentities($photo->title),
				"Link" => "http://farm1.static.flickr.com/" . $photo->image_path .".jpg",
				"Image" => "http://farm1.static.flickr.com/" .$photo->image_path. "_s.jpg"
			)));
		}
		
		return $output;
	}

	function getCMSFields() {
		return new FieldSet(
			new TextField("User",  _t('FlickrWidget.FLICKRUSERNAME','Flickr username')),
			new NumericField("NumberToShow", _t('FlickrWidget.NUMBEROFPHOTOS','Number of photos')),
			new DropdownField("Sortby", _t('FlickrGallery.SORTBY', 'Sort By'), array(
				'date-posted-desc' =>  _t('FlickrGallery.MOSTRECENT', 'Most Recent'),
				'interestingness-desc' => _t('FlickrGallery.MOSTINTERESTING', 'Most Interesting')))
		);
	}
}

?>