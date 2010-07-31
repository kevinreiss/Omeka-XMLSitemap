/* XML Sitemap from Digtal Metro Project [http://nycdigital.org/]
 * 
 * Kevin Reiss
 * kevin.reiss[@]gmail.com
 *
 * 
 * Builds a valid XML Sitemap to faciliate page crawling.
 * Installations adds the sitemap declaration to an Omeka instance's robots.txt file.
 *
 * Sitemap Format Information: URL
 *
 * Version 0.11
 * Tested on Omeka 1.2
 *
 * Todo 7.30.2010
 *
 * 1. Idenitfy a way to make datestamp of last update for exhibits, collections, categories
 *        more accurate
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

