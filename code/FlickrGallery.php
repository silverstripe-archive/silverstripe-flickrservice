<?php
class FlickrGallery extends Page {
 
	// define your database fields here - for example we have author
	static $db = array(
	   	"User" => "Varchar",
	   	"GroupID" => "Varchar", // This is the group ID, not the group NAME. It's in the format 377682@N20
	   	"Method" => "Int",
	   	"Photoset" => "Varchar",
	   	"NumberToShow" => "Int",
	   	"Tags" => "Varchar(200)",
	   	"Sortby" => "Varchar"
	);
   
	static $defaults = array(
		"Method" => 2,
		"NumberToShow" => 20,
		"Sortby" => "date-posted-desc"
	);
   
	static $icon = "flickrservice/images/flickr";
 
	/**
	 * Add custom fields for this flickr gallery page
	 *
	 * @return FieldSet
	 */
	function getCMSFields() {
 
		$fields = parent::getCMSFields();
       	$fields->addFieldToTab("Root.Content.Photos", new DropdownField("Method", _t('FlickrGallery.SELECT','Select'), array(
				'1' => _t('FlickrGallery.TAKENBY','Photos taken by'),
				'2' => _t('FlickrGallery.TAGGEDWITH','Photos tagged with'),
				'3' => _t('FlickrGallery.FROMPHOTOSET','Photos from photoset'))));
		$fields->addFieldToTab("Root.Content.Photos", new TextField("User", _t('FlickrGallery.USER','Flickr User')));
		$fields->addFieldToTab("Root.Content.Photos", new TextField("Tags", _t('FlickrGallery.TAGS','Tags')));
		$fields->addFieldToTab("Root.Content.Photos", new TextField("Photoset", _t('FlickrGallery.PHOTOSET','Photoset id')));
		$fields->addFieldToTab("Root.Content.Photos", new NumericField("NumberToShow",_t('FlickrGallery.PHOTOSPERPAGE','Photos per page'), "20"));
		$fields->addFieldToTab("Root.Content.Photos", new DropdownField("Sortby", _t('FlickrGallery.SORTBY','Sort by'), array(
			'date-posted-desc' => _t('FlickrGallery.MOSTRECENT','Most recent'),
			'interestingness-desc' => _t('FlickrGallery.MOSTINTERESTING','Most interesting'))));
        return $fields;
   }
   
	function getFlickrPage($page) {
		$flickr = new FlickrService();

		switch ($this->Method){
			case 1:
				$photos = $flickr->getPhotos($this->Tags, $this->User, $this->NumberToShow, $page, $this->Sortby);
				break;
			case 2:
				$photos = $flickr->getPhotos($this->Tags, NULL, $this->NumberToShow, $page, $this->Sortby);
				break;
			case 3:
				$photos = $flickr->getPhotoSet($this->Photoset, $this->User, $this->NumberToShow, $page);
				break;
			case 4:
				$photos = $flickr->getPhotosFromGroupPool($this->GroupID, $this->Tags, $this->User, $this->NumberToShow, $page, $this->SortBy);
				break;
		}
		
		return $photos;
	}

	function getFlickrPageHTML($page) {
		$photos = $this->getFlickrPage($page);
			
		$photoHTML = "<div class='flickr' style='float:left'>";
		foreach($photos->PhotoItems as $photo){
			$caption = htmlentities("<a href='$photo->page_url'>" . _t('FlickrGallery.VIEWINFLICKR','View this in Flickr') . "</a>");
			$photoHTML .=  '<a href="http://farm1.static.flickr.com/'.$photo->image_path . '.jpg" class="lightwindow" title="'.htmlentities($photo->title).'" caption="'.$caption.'"><img src="http://farm1.static.flickr.com/'.$photo->image_path.'_s.jpg" alt="'.htmlentities($photo->title).'"/></a>';
		}
		$photoHTML .= "</div>";
		
		if($photos->PhotoItems){
			$photoHTML .= "<div class='pages'><div class='paginator'>";
			$photoHTML .= $photos->getPages();
			$photoHTML .= "</div><span class='results'>". sprintf(_t('FlickrGallery.TOTALPHOTOS',"(%s Photos)"),$photos->getTotalPhotos()) ."</span></div>";
		}
		else {
			$photoHTML .= "<span>" . _t('FlickrGallery.NOIMAGES','Sorry!  Gallery doesn\'t contain any images for this page.') . "</span>";
		}
		return $photoHTML;
	}

	/**
	 * Get a list of URLs to cache related to this page
	 */
	function subPagesToCache() {
		$urls = parent::subPagesToCache();
		$numPages = 16;
		// To do: get this line working
		//$numPages = $this->getFlickrPage(1)->numPages();
		for($i=1;$i<=$numPages;$i++) {
			$urls[] = $this->Link() . 'page/' . $i;
		}
		return $urls;
	}
}

class FlickrGallery_Controller extends Page_Controller {
	function init() {
		if(Director::fileExists(project() . "/css/FlickrGallery.css")) {
		   Requirements::css(project() . "/css/FlickrGallery.css");
		} else {
		   Requirements::css("flickrservice/css/FlickrGallery.css");
		}
		
		Requirements::javascript( "flickrservice/javascript/prototype.js" );
		Requirements::javascript( "flickrservice/javascript/effects.js" );
		Requirements::javascript( "flickrservice/javascript/lightwindow.js" );
		
		Requirements::css("flickrservice/css/lightwindow.css");
		
		if($pos = strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'MSIE') ) {
			$version = substr( $_SERVER[ 'HTTP_USER_AGENT' ], $pos + 5, 3 );
			if( $version < 7 ) {
					Requirements::css( "flickrservice/css/lightwindowIE6.css" );
			}
		}
		
		parent::init();	
	}
	
	function Content() {
		return $this->Content . $this->FlickrPhotos();
	}

	function FlickrPhotos(){
		return $this->getFlickrPageHTML($this->currentFlickrPage());
	}
	
	function page() {
		return array();
	}
	
	function currentFlickrPage() {
		if($this->action == 'page' && is_numeric($this->urlParams['ID'])) return $this->urlParams['ID'];
		else return 1;
	}
   
}
?>