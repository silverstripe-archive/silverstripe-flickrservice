<?php
 
class FlickrGallery extends Page {
 
   // define your database fields here - for example we have author
   static $db = array(
   	"User" => "Varchar",
   	"Method" => "Int",
   	"Photoset" => "Varchar",
   	"NumberToShow" => "Int",
   	"Tags" => "Varchar(200)"
   );
   
   static $defaults = array(
		"Method" => 2,
		"NumberToShow" => 20
	);
   
  static $icon = "flickrservice/images/flickr";
 
   // add custom fields for this flickr gallery page
   function getCMSFields($cms) {
   	  Requirements::javascript( 'flickrservice/javascript/FlickrGallery_CMS.js' );
   	  
      $fields = parent::getCMSFields($cms);
      $fields->addFieldToTab("Root.Content.Photos", new DropdownField("Method", "Select ", array(
				'1' => 'Photos taken by',
				'2' => 'Photos tagged with',
				'3' => 'Photos from photoset')));
      $fields->addFieldToTab("Root.Content.Photos", new TextField("User","Flickr User"));
      $fields->addFieldToTab("Root.Content.Photos", new TextField("Tags","Tags"));
      $fields->addFieldToTab("Root.Content.Photos", new TextField("Photoset","Photoset id"));
      $fields->addFieldToTab("Root.Content.Photos", new NumericField("NumberToShow","Photos per page", "20"));
      return $fields;
   }
   
   function FlickrPhotos(){
		$flickr = new FlickrService();
		$page = isset($_GET['page'])? $_GET['page']: 1;
		
		switch ($this->Method){
			case 1:
				$photos = $flickr->getPhotos($this->Tags, $this->User, $this->NumberToShow, $page);
				break;
			case 2:
				$photos = $flickr->getPhotos($this->Tags, NULL, $this->NumberToShow, $page);
				break;
			case 3:
				$photos = $flickr->getPhotoSet($this->Photoset, $this->User, $this->NumberToShow, $page);
				break;
			}
			
		//Debug::show($photos);
		$photoHTML = "<div class='flickr' style='float:left'>";
		foreach($photos->PhotoItems as $photo)
			$photoHTML .=  '<a href="http://farm1.static.flickr.com/'.$photo->image_path . '.jpg" class="lightwindow" rel="$JSMedia" title="'.htmlentities($photo->title).'"><img src="http://farm1.static.flickr.com/'.$photo->image_path.'_s.jpg" alt="'.htmlentities($photo->title).'"/></a>';
		
		$photoHTML .= "</div>";
		
	 if($photos->PhotoItems){
		$photoHTML .= "<div class='pages'><div class='paginator'>";
		$photoHTML .= $photos->getPages();
	$photoHTML .= "</div><span class='results'>(".$photos->getTotalPhotos()." Photos)</span></div>";
	}
	else {
	
	$photoHTML .= "<span>Sorry!  Gallery doesn't contain any images for this page.</span>";
	}
		
		return $photoHTML;
	}
}

class FlickrGallery_Controller extends Page_Controller {
	function init() {
      if(Director::fileExists(project() . "/css/FlickrGallery.css")) {
         Requirements::css(project() . "/css/FlickrGallery.css");
      }else{
         Requirements::css("flickrservice/css/FlickrGallery.css");
      }
      Requirements::javascript( "flickrservice/javascript/prototype.js" );
	  Requirements::javascript( "flickrservice/javascript/effects.js" );
	  Requirements::javascript( "flickrservice/javascript/lightwindow.js" );
      
      Requirements::css("flickrservice/css/lightwindow.css");
      
      if( $pos = strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'MSIE' ) ) {
      		$version = substr( $_SERVER[ 'HTTP_USER_AGENT' ], $pos + 5, 3 );
      		if( $version < 7 ) {
					Requirements::css( "flickrservice/css/lightwindowIE6.css" );
      		}
      	}
      
      parent::init();	
   }
   
   function Content(){
			return $this->Content.$this->FlickrPhotos();
   }

}


?>