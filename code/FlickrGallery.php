<?php
 
class FlickrGallery extends Page {
 
   // define your database fields here - for example we have author
   static $db = array(
   	"User" => "Varchar",
   	"Photoset" => "Varchar",
   	"PerPage" => "Int",
   	"Tags" => "Varchar(200)"
   );
   
  static $icon = "mashups/images/treeicons/flickr";
 
   // add custom fields for this flickr gallery page
   function getCMSFields($cms) {
      $fields = parent::getCMSFields($cms);
      $fields->addFieldToTab("Root.Content.Main", new TextField("User","Flickr User"));
      $fields->addFieldToTab("Root.Content.Main", new TextField("Tags","Tags"));
      $fields->addFieldToTab("Root.Content.Main", new TextField("Photoset","Photoset id"));
      $fields->addFieldToTab("Root.Content.Main", new NumericField("PerPage","Photos per page", "25"));
      return $fields;
   }
   
   function FlickrPhotos(){
		$flickr = new FlickrService();
		$page = isset($_GET['page'])? $_GET['page']: 1;
	if($this->Photoset == "")
		$photos = $flickr->getPhotos($this->Tags, $this->User, $this->PerPage, $page);
		
	else
		$photos = $flickr->getPhotoSet($this->Photoset, $this->User, $this->PerPage, $page);
		
		//Debug::show($photos);
		$photoHTML = "<div class='flickr' style='float:left'>";
		foreach($photos->PhotoItems as $photo)
			$photoHTML .=  '<a href="http://farm1.static.flickr.com/'.$photo->image_path . '.jpg" rel="lightbox" title="'.htmlentities($photo->title).'"><img src="http://farm1.static.flickr.com/'.$photo->image_path.'_s.jpg" alt="'.htmlentities($photo->title).'"/></a>';
		
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
         Requirements::css("mashups/css/mashups.css");
      }
      Requirements::javascript("jsparty/prototype.js");
      //Requirements::javascript("jsparty/scriptaculous/scriptaculous.js");
      Requirements::javascript("jsparty/scriptaculous/effects.js");
      Requirements::javascript("mashups/javascript/lightbox.js");
      Requirements::css("mashups/css/lightbox.css");
      
      parent::init();	
   }
   
   function Content(){
			return $this->Content.$this->FlickrPhotos();
   }

}


?>