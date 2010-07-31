<?php 
/* admin view that displays last sitemap build_date
 * 
 * 1. Show date last built
 * 2. Button to manually rebuild sitemap 
 * 3. Display link to current sitemap 
 */
?>
<?php head(array('title' => 'XML Sitemap Configuration', 'bodyclass' => 'xmlsitemap')); ?>
<h1>XML Sitemap Configuration</h1>

<div id="primary">

	<form id="build-xml-sitemap" action="" method="post">
		<label for="">Rebuild Sitemap</label>
		<input name="rebuild" type="submit" value="rebuild now"/>
	</form>

	<p>Sitemap Last Built: <?php echo xml_sitemap_last_built(); ?></p>
	
</div>
<?php foot(); ?>