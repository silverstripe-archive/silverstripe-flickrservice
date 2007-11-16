<?php

class FlickrWidget extends Widget {
	static $db = array(
		"Method" => "Int",
		"User" => "Varchar",
		"Photoset" => "Varchar",
		"Tags" => "Varchar",
		"NumberToShow" => "Int",
		"Sortby" => "Varchar"
	);
	
	static $defaults = array(
		"Method" => 2,
		"NumberToShow" => 8,
		"Sortby" => "date-posted-desc"
	);
	
	static $title = "Photos";
	static $cmsTitle = "Flickr Photos";
	static $description = "Shows Flickr photos.";
	
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
			switch ($this->Method){
			case 1:
				$photos = $flickr->getPhotos($this->Tags, $this->User, $this->NumberToShow, 1, $this->Sortby);
				break;
			case 2:
				$photos = $flickr->getPhotos($this->Tags, NULL, $this->NumberToShow, 1, $this->Sortby);
				break;
			case 3:
				$photos = $flickr->getPhotoSet($this->Photoset, $this->User, $this->NumberToShow, 1);
				break;
			}
			
		} catch(Exception $e) {
			return false;
		}
		
		$output = new DataObjectSet();
		foreach($photos->PhotoItems as $photo) {
			$output->push(new ArrayData(array(
				"Title" => $photo->title,
				"Link" => "http://farm1.static.flickr.com/" . $photo->image_path .".jpg",
				"Image" => "http://farm1.static.flickr.com/" .$photo->image_path. "_s.jpg"
			)));
		}
		
		return $output;
	}

	function getCMSFields() {
	Requirements::javascript( 'flickrservice/javascript/FlickrWidget_CMS.js' );
	
		return new FieldSet(
			new DropdownField("Method", "Select ", array(
				'1' => 'Photos taken by',
				'2' => 'Photos tagged with',
				'3' => 'Photos from photoset')),
			new TextField("User", "User"),
			new TextField("PhotoSet", "Photo Set"),
			new TextField("Tags", "Tags"),
			new NumericField("NumberToShow", "Number of photos"),
			new DropdownField("Sortby", "Sort by ", array(
				'date-posted-desc' => 'Most recent',
				'interestingness-desc' => 'Most interesting'))
		);
	}
}

?>