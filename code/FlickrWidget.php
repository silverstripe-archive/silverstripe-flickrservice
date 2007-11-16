<?php

class FlickrWidget extends Widget {
	static $db = array(
		"Method" => "Int",
		"User" => "Varchar",
		"Photoset" => "Varchar",
		"Tags" => "Varchar",
		"NumberToShow" => "Int"
	);
	
	static $defaults = array(
		"Method" => 2,
		"NumberToShow" => 8
	);
	
	static $title = "Photos";
	static $cmsTitle = "Flickr Photos";
	static $description = "Shows Flickr photos.";
	
	function Photos() {
		Requirements::javascript("jsparty/prototype.js");
		Requirements::javascript("jsparty/scriptaculous/effects.js");
		Requirements::javascript("mashups/javascript/lightbox.js");
		Requirements::css("mashups/css/lightbox.css");
		Requirements::css("blog/css/flickrwidget.css");
		
		$flickr = new FlickrService();
		
		try {
			switch ($this->Method){
			case 1:
				$photos = $flickr->getPhotos($this->Tags, $this->User, $this->NumberToShow, 1);
				break;
			case 2:
				$photos = $flickr->getPhotos($this->Tags, NULL, $this->NumberToShow, 1);
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
			new NumericField("NumberToShow", "Number of photos")
		);
	}
}

?>