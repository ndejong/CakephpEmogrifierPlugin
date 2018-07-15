<?php

/****************************************************************************
 * Cakephp EmogrifierPlugin
 * Verb Networks Pty Ltd - http://verbnetworks.com - https://github.com/verbnetworks
 * 16 Feb 2014
 * 
 * @author Nicholas de Jong
 * @copyright Verb Networks Pty Ptd
 ****************************************************************************/

/**
 * Be default the CakephpEmogrifierPlugin will look in the following paths to 
 * try and locate the Emogrifier vendor library:-
 *
 *  - %APP%/Plugin/Vendor/Emogrifier.php
 *  - %APP%/Plugin/Vendor/emogrifier.php
 *  - %APP%/Plugin/Vendor/emogrifier/Classes/Emogrifier.php
 *  - %APP%/Plugin/Vendor/emogrifier/Classes/emogrifier.php
 *  - %APP%/Plugin/Vendor/Emogrifier/Classes/Emogrifier.php
 *  - %APP%/Plugin/Vendor/Emogrifier/Classes/emogrifier.php
 *  - %APP%/Plugin/Vendor/emogrifier/Emogrifier.php
 *  - %APP%/Plugin/Vendor/emogrifier/emogrifier.php
 *  - %APP%/Plugin/Vendor/Emogrifier/Emogrifier.php
 *  - %APP%/Plugin/Vendor/Emogrifier/emogrifier.php
 *
 *
 * NB: in order to cause this bootstrap.php to be loaded you MUST tell CakePlugin 
 * to do like this
 * CakePlugin::loadAll(array(
 *      'Emogrifier' => array('bootstrap' => true)
 * ));
 *
 *
 * You can specify the path as a path relative to the %APP%/Plugin or as a fully
 * qualified path from the system root
 *
 * # %APP%/Plugin/Vendor/emogrifier/Classes/Emogrifier.php
 * Configure::write('Emogrifier.include','emogrifier/Classes/Emogrifier.php');
 * 
 * # /foo/bar/Emogrifier.php
 * Configure::write('Emogrifier.include','/foo/bar/Emogrifier.php');
 *
 * # %APP%/Plugin/Vendor/custom_emogrifier.php
 * Configure::write('Emogrifier.include','custom_emogrifier.php');
 *
 **/

/**
 * media_types - if a <link> element has a media attribute, we only 
 * slurp up the CSS those that match these
 *
 * Configure::write('Emogrifier.media_types',array('all','screen'));
 */

/**
 * remove_css - remove the original css from HTML, includes the removal 
 * of <link> and <style> elements
 * 
 * Configure::write('Emogrifier.remove_css',true);
 */

/**
 * import_external_css - determines if we attempt to pull down external @import css
 * 
 * Configure::write('Emogrifier.import_external_css',false);
 */


