# locator
Geo Locator plugin for glFusion

## Features
* Creates a "store locator"-type function where visitors can find locations
within a distance range from an origin location.
* Provides an autotag to allow inclusion of maps in stories and staticpages.
* Provides an API for other plugins such as Evlist.

## Requirements
* glFusion 1.7.8+
* PHP 7.3+
* LGLib plugin

## Provider Configuration
Several Geocoding and Mapping providers are included with the plugin. You can mix and match
them to meet your needs as some have different requirements or capabilities than others.

Driving directions are always provided by maps.google.com.

* Google (https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key)
  * Requires API keys and Billing enabled on your account.
  * You should use different API keys for Geocoding and Mapping and restrict them appropriately to your server's address and HTTP Referer, respectively, to prevent &quot;quota theft&quot;.
  * Geocoding: Yes
  * Mapping: Yes
* MapQuest (https://developer.mapquest.com/documentation/)
  * Terms of service do not allow for caching or using coordinates for any purpose other than mapping. Unless you have an Enhanced plan you should not use MapQuest for geocoding.
  * Geocoding: Yes
  * Mapping: Yes
  * Known Issues:
    * Map sets z-index and glFusion menus may appear behind the map controls.
* U.S. Census (https://geocoding.geo.census.gov/)
  * Only supports locations in the United States
  * Geocoding: Yes
  * Mapping: No
* Geocodio (https://www.geocod.io)
  * Free 2500 lookups per day
  * Regions: USA and Canada
  * Geocoding: Yes
  * Mapping: No
* OpenStreetMap (https://www.openstreetmap.org)
  * Check the site for terms and conditions, light usage is expected
  * Regions: Worldwide
  * Geocoding: Yes
  * Mapping: Yes
* Here.com (https://developer.here.com)
  * Free plan limits:
    * Monthly: 250k transactions, 5k active users, 2.5GB data transrer
    * Per second: 5 Geocoding requests, 30 Map tile requests
  * Regions: Worldwide
  * Geocoding: Yes
  * Mapping: Yes
