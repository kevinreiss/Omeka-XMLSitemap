<?php

/* 
 * @copyright New York Metropolitan Library Council, 2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package XmlSitemap
 * @ Author: Kevin Reiss kevin.reiss@gmail.com 
 */

/* Class enables processing of the admin index view page that
 * displays status of sitemap build and allows user to rebuild
 * sitemap on demand. 
 */
class XmlSitemap_IndexController extends Omeka_Controller_Action
{	
	
	public function indexAction() {
		
		$this->view;
		$this->_processForm();
	}
	
	protected function _processForm() {
		if(!empty($_POST)) {
			xml_sitemap_build_sitemap();
		}
	}
	
}
?>