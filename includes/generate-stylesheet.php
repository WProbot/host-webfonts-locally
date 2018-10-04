<?php
/**
 * @package: CAOS for Webfonts
 * @author: Daan van den Bergh
 * @copyright: (c) 2018 Daan van den Bergh
 * @url: https://dev.daanvandenbergh.com
 */

// Exit if accessed directly
if (!defined( 'ABSPATH')) exit;

/**
 * set the content type header
 */
header("Content-type: text/css");

/**
 * Check if user has the needed permissions.
 */
if (!current_user_can('manage_options'))
{
	wp_die(__("You're not cool enough to access this page."));
}

/**
 * If cache directory doesn't exist, we should create it.
 */
$uploadDir = CAOS_WEBFONTS_UPLOAD_DIR;
if (!file_exists($uploadDir)) {
	wp_mkdir_p($uploadDir);
}

/**
 * Get the POST data.
 */
$selectedFonts = $_POST['selected_fonts'][0]['hwl-rendered-fonts'];

/**
 * Download the fonts.
 */
foreach ($selectedFonts as $id => $font) {
	$remoteFiles = $font['url'];

	foreach ($remoteFiles as $type => $url) {
		$remoteFile = $url;
		$filename   = basename($remoteFile);
		$localFile  = CAOS_WEBFONTS_UPLOAD_DIR . '/' . $filename;

		$fileWritten = file_put_contents($localFile, file_get_contents($remoteFile));

		if($fileWritten) {
			$localFileUrl = CAOS_WEBFONTS_UPLOAD_URL . '/' . $filename;
			$selectedFonts[$id]['url'][$type] = $localFileUrl;
		}
	}
}

/**
 * Let's generate the stylesheet.
 */
$fonts[] = "/** This file is automagically generated by CAOS for Webfonts */\n";

foreach ($selectedFonts as $font) {
	$fontFamily = sanitize_text_field($font['font-family']);
	$fontStyle  = sanitize_text_field($font['font-style']);
	$fontWeight = sanitize_text_field($font['font-weight']);

	$fontUrlEot     = filter_var($font['url']['eot'],   FILTER_VALIDATE_URL);
	$fontUrlWoffTwo = filter_var($font['url']['woff2'], FILTER_VALIDATE_URL);
	$fontUrlWoff    = filter_var($font['url']['woff'],  FILTER_VALIDATE_URL);
	$fontUrlTtf     = filter_var($font['url']['ttf'],   FILTER_VALIDATE_URL);

	$fonts[] = "@font-face {
                font-family: '$fontFamily';
                font-style: $fontStyle;
                font-weight: $fontWeight;
                src: url('$fontUrlEot'), /* IE9 Compatible */
                url('$fontUrlWoffTwo') format('woff2'), /* Super Modern Browsers */
                url('$fontUrlWoff') format('woff'), /* Modern Browsers */
                url('$fontUrlTtf') format('truetype'); /* Safari, Android, iOS */
            }";
}

$fonts = implode("\n", $fonts);
$file  = CAOS_WEBFONTS_UPLOAD_DIR . '/' . CAOS_WEBFONTS_FILENAME;

/**
 * If the file can be created and uploaded. Let's try to write it.
 */
try {
	$stylesheet = fopen($file, 'w') or die ("Cannot create file {$file}");
	fwrite ($stylesheet, $fonts);
	fclose ($stylesheet);
	wp_die(_e('Stylesheet was successfully generated and added to your theme\'s header.'));
} catch (Exception $e) {
	wp_die($e);
}
