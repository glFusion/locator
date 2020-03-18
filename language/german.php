<?php
/**
*   german.php
*   German formal language file for the Locator plugin, 
*   addressing the user as "Du"
*   @author     Lee Garner <lee@leegarner.com>
*   @translated Siegfried Gutschi <sigi AT modellbaukalender DOT info> (Dez 2016)
*   @copyright  Copyright (c) 2009 Lee Garner <lee@leegarner.com>
*   @package    locator
*   @version    0.1.1
*   @license    http://opensource.org/licenses/gpl-2.0.php 
*   GNU Public License v2 or later
*   @filesource
*/

/**
*   Main language string array
*   @global array $LANG_GEO
*/
$LANG_GEO= array(
'pi_title'                    => 'Geo-Locator Standortsuche',
'item_id'                    => 'Standort-ID',
'access_denied'                => 'Zugriff vereigert',
'access_denied_msg'            => 'Du besitzt nicht die nötigen Berechtigungen, um auf diese Seite zugreifen zu können. Dein Benutzername und Deine IP wurden aufgezeichnet.',
'address'                    => 'Addresse',
'city'              => 'City',
'state'             => 'State',
'postal'            => 'Postal Code',
'admin_menu'                => 'Geo-Locator',
'anonview'                    => 'Zur Einsicht anmelden nötig',
'coord'                        => 'Koordinaten',
'coord_instr'                => '<ul><li>Gib die Koordinaten als Dezimalzahlen ein.</li><li>Südliche Breitengrade und Westliche Längengrade werden als negative Werte eingegeben.</li></ul>',
'coord_instr2'                => 'Wenn beide Koordinaten leer sind, wird versucht diese mit Hilfe von Google Maps aus der angegebenen Adresse zu generieren.',
'contrib_origin'            => 'Neuen Standort einsenden',
'description'                => 'Beschreibung',
'disclaimer'                => 'Die angegebenen Entfernungen sind ungefähre Werte und entsprechen der Luftlinie vom Ausgangspunkt. Die Fahrstrecken sind entsprechend länger.',
'distance'                    => 'Entfernung',
'editmarker'                => 'Standort bearbeiten',
'group'                        => 'Gruppe',
'have_new_submission'        => 'Du hast eine neue Standort-Einsendung in der Warteschlange.',
'new_submission'            => 'Neue Standort-Einsendung',
'home'                        => 'Neue-Suche',
'is_origin'                    => 'Startpunkt',
'enabled'                    => 'Aktiv',
'keywords'                    => 'Stichworte',
'latlng'                    => 'Breite/Länge',
'latitude'                    => 'Breitengrad',
'longitude'                    => 'Längengrad',
'login_required'            => 'Du musst angemeldet sein, um diese Funktion nutzen zu können.',
'menulabel'                    => 'Geo-Locator',
'miles'                        => 'Meilen',
'manage_userlocs'            => 'Benutzer-Standorte',
'my_origins'                => 'Meine-Startpunkte',
'name'                        => 'Name',
'no_locs_found'                => 'Keine Ergebnisse gefunden.',
'origin'                    => 'Startpunkt',
'origin_add'                => 'Als Startpunkt setzen',
'origin_remove'                => 'Als Startpunkt entfernen',
'origin_true'                => 'Allgemeiner Startpunkt',
'owner'                        => 'Ersteller',
'perms'                        => 'Berechtigungen',
'plugin_name'                => 'Geo-Locator',
'radius'                    => 'Radius',
'reset'                        => 'Zurücksetzen',
'speedlimit_exceeded'        => 'Du kannst nur alle ' . $_CONF['speedlimit'] . ' Sekunden einen Suchvorgang durchführen.',
'title'                        => 'Titel',
'url'                        => 'URL',
'version'                    => 'Version',
'manage_locations'            => 'Standort-Verwaltung',
'desc_admin_locs'            => 'Öffentliche Standorte verwalten',
'desc_admin_userlocs'        => 'Benutzer-Standorte verwalten:<ul><li>Diese werden automatisch aus dem Feld "Wohnort" im Benutzerprofil erzeugt.</li><li>Sie können bearbeitet werden, um die Koordinaten zu ändern, oder sie können hier gelöscht werden.</li><li>Durch einfaches Ändern des Namens wird ein neuer Datensatz mit dem alten Namen erstellt.</li></ul>',
'editor_mode'                => 'Editor-Modus',
'db_save_error'                => 'Standort-ID bereits vorhanden! Dein Eintrag konnte leider nicht gespeichert werden.',
'confirm_delitem'            => 'Bist Du sicher. dass Du diesen Standort löschen willst?',
'get_directions'            => 'Routen-Planer',
'start_addr'                => 'Start-Adresse',
'or_address'                => 'oder Adresse',
'select_origin'                => 'Wähle einen Startpunkt oder gib eine Adresse ein.',
'menu_hlp'                    => array(
    'userloc' => '<ul><li>Bearbeiten oder Löschen von Benutzer-Standorten.</li><li>Dabei handelt es sich um Standorte, die als "Wohnort" im Profil eingetragen wurden bzw. um Orte aus der Standort-Suche.</li></ul>',
    ),
'back'      => 'Back',
'location'  => 'Location',
'loc_updated'       => 'Location has been updated',
'notify_subject'    => 'Location Submission from ' . $_CONF['site_name'],
'from_origin'       => 'From Origin',
);


$PLG_locator_MESSAGE1 = 'Dein Standort wurde erfolgreich gespeichert und zur Überprüfung weitergeleitet.';
$PLG_locator_MESSAGE2 = 'Dein Standort wurde erfolgreich gespeichert';
$PLG_locator_MESSAGE3 = 'Fehler beim Abrufen der aktuellen Versionsnummer';
$PLG_locator_MESSAGE4 = 'Fehler beim Durchführen der Plugin-Aktualisierung';
$PLG_locator_MESSAGE5 = 'Fehler beim Aktualisieren der Versionsnummer des Plugins';
$PLG_locator_MESSAGE6 = 'Plugin wurde bereits aktualisiert';
$PLG_locator_MESSAGE7 = 'Beim Speichern des Standortes ist ein Fehler aufgetreten. Siehe "error.log"';
$PLG_locator_MESSAGE8 = 'Fehler: Standort-ID bereits vorhanden!';

$PLG_locator_MESSAGE99 = 'Ein Datenbankfehler ist aufgetreten. Überprüfe die Datei "error.log" für Details.';


/**
*   Localization of the Admin Configuration UI
*   @global array $LANG_configsections['locator']
*/
$LANG_configsections['locator'] = array(
    'label' => 'Geo-Locator',
    'title' => 'Geo-Locator Konfiguration'
);

/**
*   Configuration system prompt strings
*   @global array $LANG_confignames['locator']
*/
$LANG_confignames['locator'] = array(
    'default_radius'    => 'Standart Such-Radius',
    'distance_unit'     => 'Entfernungs-Einheit',
    'autofill_coord'    => 'Koordinaten automatisch ausfüllen',
    'submission'        => 'Einsendungen überprüfen',
    'submit'            => 'Allow anonymous or member submissions?',
    'displayblocks'     => 'glFusion-Blöcke anzeigen',
    'purge_userlocs'    => 'Umkreis-Suche löschen nach Tagen',
    'profile_showmap'   => 'Karte im Benutzer-Profil',
    'usermenu_option'   => 'Im Menü anzeigen',
    'use_weather'       => 'Wetter-Plugin integrieren',
    'use_directions'    => 'Routenplaner anzeigen',
    'api_only'          => 'Nur als API funktionieren',

    'geocode_profile'   => 'Geocode profile locations?',
    'show_map'          => 'Karte anzeigen',
    'google_api_key'    => 'Google Maps API Key',
    'google_js_key'     => 'Google Maps JS Key',

    'defgrp'            => 'Standard-Gruppe',
    'default_permissions' => 'Standard-Berechtigungen',
    'mapper'            => 'Map Provider',
    'mapquest_key'      => 'Mapquest Client Key',
    'geocodio_api_key'  => 'Geocodio API Key',
    'geocoder' => 'Geocoding Provider',
    'osm_use_tileserver' => 'Use internal tileserver for OSM',
);

/**
*   Configuration system subgroup strings
*   @global array $LANG_configsubgroups['locator']
*/
$LANG_configsubgroups['locator'] = array(
    'sg_main' => 'Haupteinstellungen'
);

/**
*   Configuration system fieldset names
*   @global array $LANG_fs['locator']
*/
$LANG_fs['locator'] = array(
    'fs_main' => 'Allgemeine-Einstellungen',
    'fs_mappers' => 'Map Providers',
    'fs_permissions' => 'Standard-Berechtigungen',
 );

/**
*   Configuration system selection strings
*   Note: entries 0, 1, and 12 are the same as in 
*   $LANG_configselects['Core']
*
*   @global array $LANG_configselects['locator']
*/
$LANG_configselects['locator'] = array(
    0 => array('Ja' => 1, 'Nein' => 0),
    1 => array('Ja' => TRUE, 'Nein' => FALSE),
    3 => array('Ja' => 1, 'Nein' => 0),
    4 => array('Aktiviert' => 1, 'Deaktiviert' => 0),
    5 => array('Ganz oben' => 1, 'Nach Hauptartikel' => 2, 'Ganz unten' => 3),
    10 => array('5' => 5, '10' => 10, '25' => 25, '50' => 50),
    11 => array('Meilen' => 'miles', 'Kilometer' => 'km'),
    12 => array('Kein Zugang' => 0, 'Nur lesen' => 2, 'Lesen-Schreiben' => 3),
    13 => array('Keine' => 0, 'Linke Blöcke' => 1, 'Rechte Blöcke' => 2, 'Linke & Rechte Blöcke' => 3),
    15 => array('Admins Only' => 0, 'Logged-In Users' => 1, 'Logged-In+Anon Users' => 2),
);


?>
