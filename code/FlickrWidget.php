<?php

class FlickrWidget extends Widget {
	static $db = array(
		"User" => "Varchar",
		"Photoset" => "Varchar",
		"Tags" => "Varchar",
		"NumberToShow" => "Int"
	);
	
	static $defaults = array(
		"NumberToShow" => 8
	);
	
	static $title = "Photos";
	static $cmsTitle = "Flickr Photos";
	static $description = "Shows flickr photos.";
	
	function Photos() {
		Requirements::javascript("jsparty/prototype.js");
		Requirements::javascript("jsparty/scriptaculous/effects.js");
		Requirements::javascript("mashups/javascript/lightbox.js");
		Requirements::css("mashups/css/lightbox.css");
		Requirements::css("blog/css/flickrwidget.css");
		
		$flickr = new FlickrService();
		
		try {
			if($this->Photoset == "") {
				$photos = $flickr->getPhotos($this->Tags, $this->User, $this->NumberToShow, 1);
			} else {
				$photos = $flickr->getPhotoSet($this->Photoset, $this->User, $this->NumberToShow, 1);
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
		return new FieldSet(
			new TextField("User", "User"),
			new TextField("PhotoSet", "Photo Set"),
			new TextField("Tags", "Tags"),
			new NumericField("NumberToShow", "Number to Show")
		);
	}
}

?>