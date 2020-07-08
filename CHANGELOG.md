# glLocator - Change Log

## 1.2.1 (2020-07-09)
  * Remove support for non-uikit themes
  * Fix call to geocoder function from UserLoc class.
  * Accept width and height parameters in autotags.
  * Add country field for markers; hopefully improve free-form address resolution.

## 1.2.0 (2018-11-03)
- Add separate Google API key for Javascript
- Add Mapquest, OpenStreetMap map providers
- Add U.S. Census and Geocodio geocoding services
- Fix output var in service_getCoords function, caused 500 errors
- Add caching for geocoding results

## 1.1.3 (2018-01-14)
- Fix missing namespace in admin

## 1.1.2 (2017-10-29)
- Fix namespace when getting user locations

## 1.1.1 (Release 2017-10-01)
- Implement PLG_displayAdBlock()
- Implement Locator namespace and class autoloader
- Use HTTPS for Google map api
- Change lookup form to use "get" to allow back button to work
- Change address to multiple fields
- Change to Jquery AJAX in admin screen, enable notifications.
- Add sitemap v2 driver
- Add plugin_getiteminfo() function to support Searcher plugin

## 1.1.0 (Released 2011-04-06)
- 0000451: [Lookup] Update to Google API V3 (lee) - resolved.
- 0000452: [Lookup] Switch lookup from CSV to JSON (lee) - resolved.

## 1.0.3 (Released 2011-01-22)
- 0000442: [Submission] Implement advanced editor for marker form (lee) - resolved.
- 0000438: [General] Add autotag support for addresses (lee) - resolved.
- 0000403: [General] Allow user-specified marker ID values (lee) - resolved.
- 0000440: [Submission] Need an error message displayed when a dupicate locator ID is submitted (lee) - resolved.
- 0000439: [Administration] Language strings in admin functions need cleaning up (lee) - resolved.
- 0000431: [General] Database error due to hard-coded table names (lee) - resolved.
- 0000437: [General] Fix language localization in public locator form (lee) - resolved.
- 0000436: [Maps] Cannot show multiple maps on a single page (lee) - resolved.

## 1.0.1 (Released 2010-05-23)
- 0000402: [Maps] Add option to put maps in the user profiles (lee) - resolved.
- 0000404: [General] Add option to display left or right blocks (lee) - resolved.
- 0000386: [General] Add config option to not show on the user menu (lee) - resolved.

## 1.0.0 (Released 2009-12-21)
- 0000292: [Maps] Add an autotag to display a map (lee) - closed.
- 0000293: [Lookup] Need to set the speedlimit for submissions requiring Google lookups (lee) - closed.
- 0000294: [Configuration] Table character set is latin1 regardless of database setting (lee) - closed.
- 0000296: [Lookup] Use the user location cache table for user-entered addresses (lee) - closed.
- 0000315: [Lookup] Add "enabled" field to markers so they can be excluded from searches (lee) - closed.
- 0000348: [Submission] Add support for Advanced Editor (lee) - closed.
- 0000370: [General] Unescaped strings are passed to search function (lee) - closed.

## 0.1.3 (Released 2009-05-11)
- 0000291: [Administration] Keywords field in the marker edit form is blank (lee) - closed.

## 0.1.2 (Released 2009-05-08)
- 0000289: [Lookup] Default distance unit is not being used (lee) - closed.
- 0000290: [Lookup] Anonymous users can't search locations, regardless of permissions (lee) - closed.

## 0.1.1 (Released 2009-05-08)
Development of patches against v0.1
- 0000275: [Maps] Turning off map display doesn't inhibit the map (lee) - closed.
- 0000278: [Administration] Hardcoded path from public_html to private (lee) - closed.
- 0000279: [Lookup] Encoding error in Google XML (lee) - closed.
- 0000280: [Administration] Need config option to disable right blocks (lee) - closed.
- 0000282: [Administration] Language file has trailing blank line (lee) - closed.
