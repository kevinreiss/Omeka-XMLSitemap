<?php
/* couldn't find Exhibit Object without including this statement on omeka 1.2 */
// require PLUGIN_DIR . '/ExhibitBuilder/models/Exhibit.php';

/* 
 * @copyright New York Metropolitan Library Council, 2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package XmlSitemap
 * @Author: Kevin Reiss kevin.reiss@gmail.com 
 */

/* XML Sitemap from Digtal Metro Project [http://nycdigital.org/]
 * 
 * Builds a valid XML Sitemap to faciliate page crawling.
 * Installations adds the sitemap declaration to an Omeka instance's robots.txt file.
 * 
 * Sitemap Format Information: URL 
 * 
 * Version 0.11
 * Tested on Omeka 1.2
 * 
 * Todo 4.12.2010
 * 
 * 1. Idenitfy a way to make datestamp of last update for exhibits, collections, categories 
 * 	  more accurate
 * 2. Identify a way to efficiently update sitemap upon addition of new content
 *    building entire sitemap using the varies "after" hooks that are currently
 *    commented out.
 * 3. Idenitfy a way to schedule sitemap build via daily/weekly chron.
 * 4. Identify a way to automatically register sitemap with various 
 *    search engines after the first time it is built.
 * 5. Think about who should have permission to see the admin options 
 * 6. Proper routine to check and see if MetadataBrowser plugin is installed so 
 *    addition of MetadataBrowingCategory categories only happens when the plugin
 *    confirms it is installed.
 */

add_plugin_hook('config_form', 'xml_sitemap_config_form');
add_plugin_hook('config', 'xml_sitemap_config');
add_plugin_hook('install', 'xml_sitemap_install');
add_plugin_hook('uninstall', 'xml_sitemap_uninstall');

/* add a hook to allow the sitemap to be rebuilt everytime these objects change
 * There should be a more efficient way to handle this.
 * Investigate seeing a chron job can build this on a daily basis.
 * 
 */
/*
add_plugin_hook('after_save_item', 'xml_sitemap_after_save_item');
add_plugin_hook('after_update_item', 'xml_sitemap_after_update_item');
add_plugin_hook('after_delete_item', 'xml_sitemap_after_delete_item');
add_plugin_hook('after_save_collection', 'xml_sitemap_after_save_collection');
add_plugin_hook('after_update_collection', 'xml_sitemap_after_update_collection');
add_plugin_hook('after_delete_collection', 'xml_sitemap_after_delete_collection');
add_plugin_hook('after_save_exhibit', 'xml_sitemap_after_save_exhibit');
add_plugin_hook('after_update_exhibit', 'xml_sitemap_after_update_exhibit');
add_plugin_hook('after_delete_exhibit', 'xml_sitemap_after_delete_exhibit');
add_plugin_hook('after_save_page', 'xml_sitemap_after_save_page');
add_plugin_hook('after_update_page', 'xml_sitemap_after_update_page');
add_plugin_hook('after_delete_page', 'xml_sitemap_after_delete_page');
*/
// nav filter for admin menu
add_filter('admin_navigation_main', 'xml_sitemap_admin_nav');

function xml_sitemap_install() {
	// update the robots.txt file included with omeka to reflect the location of the sitemap 
	xml_sitemap_update_robots();
}

function xml_sitemap_uninstall() {
	// remove plugin options
	delete_option('xml_sitemap_include_simple_pages');
	delete_option('xml_sitemap_include_tags');
	delete_option('xml_sitemap_include_category_browser'); 
	delete_option('xml_sitemap_include_exhibits');
	// ranking options
	delete_option('xml_sitemap_home_ranking');
	delete_option('xml_sitemap_item_ranking');
	delete_option('xml_sitemap_exhibit_ranking');
	delete_option('xml_sitemap_collection_ranking');
	delete_option('xml_sitemap_main_ranking');
	delete_option('xml_sitemap_catsandtags_ranking');
	// frequency options
	delete_option('xml_sitemap_change_home_freq');
	delete_option('xml_sitemap_change_main_freq');
	delete_option('xml_sitemap_change_item_freq');
	delete_option('xml_sitemap_change_exhibit_freq');
	delete_option('xml_sitemap_change_collection_freq');
	delete_option('xml_sitemap_change_catsandtags_freq');
	
	// need to fill out following functions 
	
	// remove references from robots.txt
	xml_sitemap_remove_robots();
	// delete current sitemap if it exists
	xml_sitemap_delete_sitemap();
}

// begin object save/update/delete functions
// these routines should be invoked after changes to objects to update the sitemap accordingly

/*
function xml_sitemap_after_save_item() {
	
	xml_sitemap_build_sitemap();
}

function xml_sitemap_after_update_item() {
	xml_sitemap_build_sitemap();
}

function xml_sitemap_after_delete_item() {
	xml_sitemap_build_sitemap();
}

function xml_sitemap_after_save_collection() {
	
	xml_sitemap_build_sitemap();
}

function xml_sitemap_after_update_collection() {
	xml_sitemap_build_sitemap();
}

function xml_sitemap_after_delete_collection() {
	xml_sitemap_build_sitemap();
}

function xml_sitemap_after_save_exhibit() {
 
	xml_sitemap_build_sitemap();
}

function xml_sitemap_after_update_exhibit() {
	xml_sitemap_build_sitemap();
}

function xml_sitemap_after_delete_exhibit() {
	xml_sitemap_build_sitemap();
}

function xml_sitemap_after_save_page() {
	
	xml_sitemap_build_sitemap();
}

function xml_sitemap_after_update_page() {
	xml_sitemap_build_sitemap();
}

function xml_sitemap_after_delete_page() {
	xml_sitemap_build_sitemap();
}

* end update/save/delete hooks for various object types
*/

/* add plugin to admin thems */
function xml_sitemap_admin_nav($navArray)
{
    $navArray = $navArray + array('Xml Sitemap' => uri(array('module'=>'xml-sitemap', 'controller' => 'index', 'action'=>'index'), 'default'));
    return $navArray;
}


// show plugin configuration page
function xml_sitemap_config_form() {
	include('config_form.php');
}
 

function xml_sitemap_config() {
	// include in sitemap decisions, all other public content automatically included
	set_option('xml_sitemap_include_simple_pages', trim($_POST['xml_sitemap_include_simple_pages']));
	set_option('xml_sitemap_include_tags', trim($_POST['xml_sitemap_include_tags']));
	set_option('xml_sitemap_include_category_browser', trim($_POST['xml_sitemap_include_category_browser']));
	set_option('xml_sitemap_include_exhibits', trim($_POST['xml_sitemap_include_exhibits']));
	// ranking options for various functions
	set_option('xml_sitemap_item_ranking', trim($_POST['xml_sitemap_item_ranking']));
	set_option('xml_sitemap_exhibit_ranking', trim($_POST['xml_sitemap_exhibit_ranking']));
	set_option('xml_sitemap_collection_ranking', trim($_POST['xml_sitemap_collection_ranking']));
	set_option('xml_sitemap_catsandtags_ranking', trim($_POST['xml_sitemap_catsandtags_ranking']));
	set_option('xml_sitemap_main_ranking', trim($_POST['xml_sitemap_main_ranking']));
	set_option('xml_sitemap_home_ranking', trim($_POST['xml_sitemap_home_ranking']));
	// change frequencies for different content points 
	set_option('xml_sitemap_change_home_freq', trim($_POST['xml_sitemap_change_home_freq']));
	set_option('xml_sitemap_change_main_freq', trim($_POST['xml_sitemap_change_main_freq']));
	set_option('xml_sitemap_change_item_freq', trim($_POST['xml_sitemap_change_item_freq']));
	set_option('xml_sitemap_change_exhibit_freq', trim($_POST['xml_sitemap_change_exhibit_freq']));
	set_option('xml_sitemap_change_collection_freq', trim($_POST['xml_sitemap_change_collection_freq']));
	set_option('xml_sitemap_change_catsandtags_freq', trim($_POST['xml_sitemap_change_catsandtags_freq']));
	xml_sitemap_build_sitemap();
}

function xml_sitemap_update_robots()
{
	//update the site robots.txt file so it lists the sitemap
	
	// need to double check this. this should be activated upon installation
	
	$sitemap_uri = xml_sitemap_get_uri();
	// options for robots file 
	
	$mapdef = "\n#START XML-SITEMAP-PLUGIN\n";
	$mapdef .= "Sitemap: " . $sitemap_uri . "\n";
	$mapdef .= "#END XML-SITEMAP-PLUGIN\n";
	// open file
	$robotspath = BASE_DIR . "/robots.txt";
	if (is_writeable($robotspath))
	{	
		$robotsfile = fopen($robotspath, 'a');
		//write to file
		fwrite($robotsfile, $mapdef);
		//close file	
		fclose($robotsfile);
	}
	else { echo "Robots.txt file is not writeable"; }
}

// returns the uri of the sitemap
function xml_sitemap_get_uri() {
	return WEB_ROOT . "/sitemap.xml";
}

// returns the location of the sitemap in the local filesystem
function xml_sitemap_get_fileloc() {
	return BASE_DIR . "/sitemap.xml";
}

function xml_sitemap_remove_robots() {
	// remove reference to sitemap from robots.txt upon uninstall
	$robotspath = BASE_DIR . "/robots.txt";
	if (is_writeable($robotspath))
	{	
		$lines = file($robotspath);
		$savelines = "";
		
		foreach ($lines as $line) {
			//echo $line;
			$start = strpos($line, "#START XML");
			$map = strpos($line, "Sitemap:");
			$end = strpos($line, "#END XML");
			if($start !== false) {
				
			} 
			elseif($map !== false) {
				
			}
			elseif($end !== false) {
				
			}
			else 
			{ 
				$savelines .= $line; 
			}
			
		}
		
		$robotsfile = fopen($robotspath, 'w+');
		// write lines that were kept to the file
		fwrite($robotsfile, $savelines);
		//close file	
		fclose($robotsfile);
	}
	else { echo "Robots.txt file is not writeable"; }
}

function xml_sitemap_delete_sitemap() {
	// remove sitemap file upon uninstall of plugin
	$sitemap = xml_sitemap_get_fileloc();
	if (file_exists($sitemap) && is_writeable($sitemap)) {
		unlink($sitemap);
	} else { echo "Problem Deleting Sitemap File"; }
}
// define function to actually create the site_map.xml file  
function xml_sitemap_build_sitemap()
{
	//read configuration options
	// should the user be able to assing priorty
	// use an XML model available in zend to build the file
	// use the "get_db() function by appropriate omeka table to get this data
	// create core elements main nav and homepage options
	
	// start sitemap code 
	
	// check out how the csv tool handles files....
	
	$sitemap = "<?xml version='1.0' encoding='UTF-8'?>\n";
	$sitemap .= "<!-- generator='Omeka/" . OMEKA_VERSION . "' -->";
	$sitemap .= "<!-- generated-on='" . date("F d Y H:i:s") . "' -->\n";
	$sitemap .= "<urlset xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd' xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";
		 		
	
	
	// don't forget the homepage 
	// single datestamps 
	
	$homedatestamp = date("c"); //date("Y-m-dTH:i:s-05:00"); //, filemtime(PUBLIC_THEME_DIR . "/index.php"));
	$homeranking = get_option('xml_sitemap_home_ranking');
	$homefreq = get_option('xml_sitemap_change_home_freq');
	
	// set defualts
	
	// The final sections get omeka static and main navigational content 
	$defaultpagerank = get_option('xml_sitemap_main_ranking');
	$defaultpagefreq = get_option('xml_sitemap_change_main_freq');
	
	$sitemap .= xml_sitemap_build_entry(WEB_ROOT . "/", $homedatestamp, $homefreq, $homeranking);
	
	// go through all omeka content 
	$db = get_db();
	
	// create item pages
	$items = $db->getTable('Item');
	$cur_items = $items->findall();
	$itemranking = get_option('xml_sitemap_item_ranking');
	$itemfreq = get_option('xml_sitemap_change_item_freq');
	foreach($cur_items as $item) {
		// need to check for public/private status
		
		if($item->public == 1) {      // confirm this is a valid method 
			$url = WEB_ROOT . "/items/show/" . $item->id;
			$lastmod = date(DATE_W3C, strtotime($item->modified));
			$entry = xml_sitemap_build_entry($url, $lastmod,$itemfreq,$itemranking);
			$sitemap .= $entry;
		}
		
	}
	
	// create exhibit pages
	
	if(get_option('xml_sitemap_include_exhibits'))
	{
		$exhibits = $db->getTable('Exhibit');
		$cur_exhibits = $exhibits->findall();
		//$exhibits = exhibit_builder_get_exhibits();
		$exhibitranking = get_option('xml_sitemap_exhibit_ranking');
		$exhibitfreq = get_option('xml_sitemap_change_exhibit_freq');
		foreach($cur_exhibits as $exhibit) {
			// need to make sure grab exhibit sections as well as exhibit 
			if($exhibit->public == 1) {
				$exhibiturl = WEB_ROOT . "/exhibits/show/" . $exhibit->slug;
				$lastmod = date("c");
				$entry = xml_sitemap_build_entry($exhibiturl,$lastmod,$exhibitfreq,$exhibitranking);
				// now go get all subpages related to this exhibit
				$subpages = $db->getTable('ExhibitSection')->findall();
				foreach($subpages as $subpage) {
					if ($subpage->exhibit_id == $exhibit->id) {
						$sectionurl = $exhibiturl . "/" . $subpage->slug;
						//$lastmod = date("Y-m-d H:i:s");
						$entry .= xml_sitemap_build_entry($sectionurl,$lastmod,$exhibitfreq,$exhibitranking);
						}
					}
				$sitemap .= $entry;
			}	
		}
		
		
	}
	
	
	
	// create collection pages
	$collections = $db->getTable('Collection');
	$cur_collections = $collections->findall();
	$collectionranking = get_option('xml_sitemap_collection_ranking');
	$collectionfreq = get_option('xml_sitemap_change_collection_freq');
	foreach($cur_collections as $collection) {
		$url = WEB_ROOT . "/collections/show/" . $collection->id;
		$browsecolurl = WEB_ROOT . "/items/browse/collections/" . $collection->id;
		// how should I get the last update date for a collection item
		$lastmod = xml_sitemap_get_collection_item_lastmod($collection->id);
		// build both the index pages and the browing pages
		$entry = xml_sitemap_build_entry($url,$lastmod,$collectionfreq,$collectionranking);
		$entry .= xml_sitemap_build_entry($browsecolurl,$lastmod,$collectionfreq,$collectionranking);
		$sitemap .= $entry;
	}
		
	// create tag pages
	if(get_option('xml_sitemap_include_tags')) 
	{
		$sitemap .= xml_sitemap_build_entry(WEB_ROOT . "/items/tags", $homedatestamp,$defaultpagefreq,$defaultpagerank);
    	$tags = $db->getTable('Tag')->findall();
		$tagranking = get_option('xml_sitemap_catsandtags_ranking');
		$tagfreq = get_option('xml_sitemap_change_catsandtags_freq');
		
		foreach($tags as $tag) {
			
			$url = WEB_ROOT . '/items/browse/tags/'. htmlspecialchars($tag->name);
			//$tag_id = $tag->id;
			//$tagtimes = $tags->findBy(array('tag_id'=>$tag_id));
			$lastmod = xml_sitemap_get_tag_lastmod($tag->name);
			$entry = xml_sitemap_build_entry($url,$lastmod,$tagfreq,$tagranking);
			$sitemap .= $entry;
		}	
	}
	
	// create subject browsing pages
	//$plugin = $db->getTable('Plugin')  && is_active('metadata_browser')
	if(get_option('xml_sitemap_include_category_browser'))
	{ 
		$sitemap .= xml_sitemap_build_entry(WEB_ROOT . "/category", $homedatestamp, $defaultpagefreq, $defaultpagerank);
		$categories = $db->getTable('MetadataBrowserCategory')->findActiveCategories(); 
		$catranking = get_option('xml_sitemap_catsandtags_ranking');
		$catfreq = get_option('xml_sitemap_change_catsandtags_freq');
		foreach($categories as $category) {
			$url = WEB_ROOT . "/category/" . $category->slug;
			$lastmod = date("c"); // need a way to populate this
			$entry = xml_sitemap_build_entry($url,$lastmod,$catfreq,$catranking);
			$sitemap .= $entry;
			// should I add individual categories?
			$categoryValues = $category->getAssignedValues();
			foreach($categoryValues as $value)
			{
				$valueURL = WEB_ROOT . "/" . metadata_browser_create_url($category->element_id,$value);
				$catValueEntry = xml_sitemap_build_entry($valueURL,$lastmod,$catfreq,$catranking);
				$sitemap .= $catValueEntry;
			}
		}
	}
	
	
	//do simple pages
	if (get_option('xml_sitemap_include_simple_pages'))
	{
		$simplepages = $db->getTable('SimplePagesPage');
		$cur_pages = $simplepages->findall();
		foreach($cur_pages as $page) {
			if ($page->is_published) {
				$url = WEB_ROOT . "/" . $page->slug;
				$lastmod = date(DATE_W3C, strtotime($page->updated));
				$entry = xml_sitemap_build_entry($url,$lastmod,$defaultpagefreq,$defaultpagerank);
				$sitemap .= $entry; // add the entry to the sitemap
			}
		}
	}
	
	//current top omeka navigational items
	// call administrative navigation function and loop through it
	$sitemap .= xml_sitemap_build_entry(WEB_ROOT . "/items", $homedatestamp,$defaultpagefreq,$defaultpagerank);
	$sitemap .= xml_sitemap_build_entry(WEB_ROOT . "/items/exhibits", $homedatestamp,$defaultpagefreq,$defaultpagerank);
	
	$sitemap .= "</urlset>\n";
	$siteloc = xml_sitemap_get_fileloc();
	$sitefile = fopen($siteloc, w);
	fwrite($sitefile, $sitemap);
	fclose($sitefile);
}

/*
 * Helper function to build an individual sitemap entry
 */
function xml_sitemap_build_entry($url, $lastmod, $changefreq, $rankvalue)
{
	$entry = "\t<url>\n";
	$entry .= "\t\t<loc>" . $url . "</loc>\n";
	$entry .= "\t\t<lastmod>" . $lastmod . "</lastmod>\n";
	$entry .= "\t\t<changefreq>" . $changefreq . "</changefreq>\n";
	$entry .= "\t\t<priority>" . $rankvalue . "</priority>\n";
	$entry .= "\t</url>\n";
	return $entry;
}

function xml_sitemap_get_tag_lastmod($tag) {
	// need to confirm this working correctly in terms of a date sort
	$db = get_db();
	$taggings = $db->getTable('Taggings')->findBy(array('tag' => $tag)); // can I add an order by here?
	$dates = array();
	foreach ($taggings as $tagging) {
		array_unshift($dates, $tagging->time);
	
	}
	//arsort($dates); // order dates properly
	//print_r($dates);
	rsort($dates);
	$lastmod = date("c", strtotime($dates[0]));
	return $lastmod;
}

// error in this function - 
function xml_sitemap_get_collection_item_lastmod($collectionid) {
	$db = get_db();
	$items = $db->getTable('Item')->findBy(array('collection_id' => $collectionid));
	$dates = array();
	foreach ($items as $item) {
		array_unshift($dates, $item->modified);
	}
	rsort($dates);
	return date("c", strtotime($dates[0]));
}

function xml_sitemap_last_built() {
	// checks when sitemap was last built
	$filename = xml_sitemap_get_fileloc();
	$webfile = xml_sitemap_get_uri();
	if (file_exists($filename)) {
    	$mod_date = "<b>" . date("F d Y H:i:s", filemtime($filename)) . "</b>";
		$siteurl = xml_sitemap_get_uri();
		$mod_date .= " view at <a href='" . $siteurl . "'>" . $siteurl . "</a>";	
	}
	else
	{
		$mod_date = "Sitemap has not been built yet";
	}
	return $mod_date;
}



function xml_sitemap_set_freq($contentID, $contentLabel) {
	
	$field = '';
	$field .= '<div class="field">';
	$frequencyChoices = array();
	$frequencyChoices['always'] = 'Always';
	$frequencyChoices['hourly'] = 'Hourly';
	$frequencyChoices['daily'] = 'Daily';
	$frequencyChoices['weekly'] = 'Weekly';
	$frequencyChoices['monthly'] = 'Monthly';
	$frequencyChoices['yearly'] = 'Yearly';
	$frequencyChoices['never'] = 'Never';
	$field .= select(array('name' => $contentID, 'id' => $contentID), $frequencyChoices, get_option($contentID), $contentLabel);
	$field .= "</div>";
	return $field;

}

/*
 * Print out ranking value choices in config_form.php
 */
function xml_sitemap_set_rank($rankID, $rankLabel) {
	$field = '';
	$field .= '<div class="field">';
	$rankingChoices = array();
	$rankingChoices['0.1'] = '0.1';
	$rankingChoices['0.2'] = '0.2';
	$rankingChoices['0.3'] = '0.3';
	$rankingChoices['0.4'] = '0.4';
	$rankingChoices['0.5'] = '0.5';
	$rankingChoices['0.6'] = '0.6';
	$rankingChoices['0.7'] = '0.7';
	$rankingChoices['0.8'] = '0.8';
	$rankingChoices['0.9'] = '0.9';
	$rankingChoices['1.0'] = '1.0';
	$field .= select(array('name' => $rankID, 'id' => $rankID), $rankingChoices, get_option($rankID), $rankLabel);
	$field .= "</div>";
	return $field;
}

/* 
 * Check to see if MetadataBrowser Plug-in is installed.
 * This will explicitly check to see if these browsing options
 * can be included by a site administrator.
 * 
 * can't quite get this one to work 
 */
function xml_sitemap_check_cat_active()
{
	// is the category browser plugin active?
	$db = get_db();
	$pluginTable = $db->getTable('Plugins');
	$catPlugSelect = $pluginTable->getSelect()->where('name = ?', 'MetadataBrowser');
	$catPlugin = $db->fetchObject($catPlugSelect);
	//$catPlugin = $db->getTable('Plugin')->findBy(array('name' => 'MetadataBrowser'));
	if($catPlugin->active)
	{
		return $catPlugin->active;
	}
}

?>
