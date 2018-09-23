<?php
/**
 * Plugin Name: CAOS for Webfonts
 * Plugin URI: https://dev.daanvandenbergh.com/wordpress-plugins/host-google-fonts-locally
 * Description: Automagically save the fonts you want to use inside your content-folder, generate a stylesheet for them and enqueue it in your theme's header.
<<<<<<< HEAD
 * Version: 1.2.6
=======
 * Version: 1.1.0
>>>>>>> 7ecb0a932105ee49eefc6048af13862838134180
 * Author: Daan van den Bergh
 * Author URI: https://dev.daanvandenbergh.com
 * License: GPL2v2 or later
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Extend WP default Allowed Filetypes
 *
 * @param array $filetypes
 * @return array
 */
function hwlSetAllowedFiletypes($filetypes = array()) {
	$filetypes['woff']  = 'application/x-font-woff';
	$filetypes['woff2'] = "application/font-woff2";
	$filetypes['otf']   = 'application/x-font-otf';
	$filetypes['ttf']   = 'application/x-font-ttf';
	$filetypes['svg']   = 'image/svg+xml';
	$filetypes['eot']   = 'application/vnd.ms-fontobject';

    return $filetypes;
}
add_filter('upload_mimes', 'hwlSetAllowedFiletypes');

/**
 * Define constants.
 */
define('CAOS_WEBFONTS_FILENAME'  , 'fonts.css');
define('CAOS_WEBFONTS_CACHE_DIR' , '/cache/caos-webfonts');
define('CAOS_WEBFONTS_UPLOAD_DIR', WP_CONTENT_DIR . CAOS_WEBFONTS_CACHE_DIR);
define('CAOS_WEBFONTS_UPLOAD_URL', content_url() . CAOS_WEBFONTS_CACHE_DIR);

/**
 * Create the Admin menu-item
 */
function hwlCreateMenu()
{
    add_options_page(
        'CAOS for Webfonts',
        'Optimize Webfonts',
        'manage_options',
        'optimize-webfonts',
        'hwlSettingsPage'
    );
}
add_action('admin_menu', 'hwlCreateMenu');

/**
 * Render the settings page.
 */
function hwlSettingsPage()
{
    if (!current_user_can('manage_options'))
    {
        wp_die(__("You're not cool enough to access this page."));
    }
    ?>
    <div class="wrap">
        <h1><?php _e('CAOS for Webfonts', 'host-webfonts-local'); ?></h1>
        <p>
		    <?php _e('Developed by: ', 'host-webfonts-local'); ?>
            <a title="Buy me a beer!" href="https://dev.daanvandenbergh.com/donate/">
                Daan van den Bergh</a>.
        </p>
        <div id="hwl-admin-notices"></div>
        <?php require_once('includes/welcome-panel.php'); ?>
        <form id="hwl-options-form" name="hwl-options-form">
            <?php
            settings_fields('host-webfonts-local-basic-settings'
            );
            do_settings_sections('host-webfonts-local-basic-settings'
            );

            /**
             * Render the upload-functions.
             */
            hwlMediaUploadInit();

            do_action('hwl_after_form_settings');
            ?>
        </form>
    </div>
    <?php
}

/**
 * Set custom upload-fields and render upload buttons.
 */
function hwlMediaUploadInit() {
    wp_enqueue_media();
    ?>
    <table>
        <tbody>
            <tr valign="top">
                <td colspan="2">
                    <input type="text" name="search-field"
                           id="search-field" class="form-input-tip ui-autocomplete-input" placeholder="Search fonts..." />
                </td>
            </tr>
        </tbody>
            <tr valign="top">
                <th>
                    font-family
                </th>
                <th>
                    font-style
                </th>
                <th>
                    remove
                </th>
            </tr>
        <tbody id="hwl-results">
            <tr class="loading" style="display: none;">
                <td colspan="3" align="center">
                    <span class="spinner"></span>
                </td>
            </tr>
            <tr class="error" style="display: none;">
                <td colspan="3" align="center">No fonts available.</td>
            </tr>
        </tbody>
    </table>

    <table>
        <tbody>
            <tr valign="bottom">
                <td>
                    <input type="button" onclick="hwlGenerateStylesheet()" name="generate-btn"
                           id="generate-btn" class="button-primary" value="Generate Stylesheet" />
                </td>
            </tr>
        </tbody>
    </table>
    <script type="text/javascript">
<<<<<<< HEAD
=======
        var media_uploader = null;

        /**
         * Get the Media Uploader and prepare the uploaded fonts for generating the style sheet.
         */
        function hwlFontUploader()
        {
            media_uploader = wp.media({
                    frame: "post",
                    state: "insert",
                    multiple: true
                }).open();

            hwlSetUploadDir();

            media_uploader.on(
                "close",
                function() {
                    hwlResetUploadDir();
                }
            ).on(
                "insert",
                function() {
                    hwlGenerateOutput();
                }
            );
        }

        /**
         * Call the media-upload script or logs an error to the console.
         */
        function hwlSetUploadDir()
        {
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'hwlAjaxSetUploadDir'
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        /**
         *  AJAX call to reset upload directory.
         */
        function hwlResetUploadDir()
        {
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'hwlAjaxResetUploadDir'
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        /**
         * Generate the output after upload/insert
         */
        function hwlGenerateOutput()
        {
            var length = media_uploader.state().get("selection").length;
            var fonts = media_uploader.state().get("selection").models;
            for (var iii = 0; iii < length; iii++) {
                var font_url = fonts[iii].changed.url;
                var font_name = fonts[iii].changed.title;
                var font_type = fonts[iii].changed.subtype;
                var uploadedFont = `<tr valign="top">
                                            <td>
                                                <input type="text" name="hwl_uploaded_font][${font_name}]"
                                                       id="hwl_uploaded_font][${font_name}]"
                                                       value="${font_name}" readonly />
                                            </td>
                                            <td>
                                                <input type="text" name="hwl_uploaded_font][${font_name}][font_family]"
                                                       id="hwl_uploaded_font][${font_name}][font_family]"
                                                       value="" />
                                            </td>
                                            <td>
                                                <input type="text" name="hwl_uploaded_font][${font_name}][font_weight]"
                                                       id="hwl_uploaded_font][${font_name}][font_weight]"
                                                       value="" />
                                            </td>
                                            <td>
                                                <input type="text" name="hwl_uploaded_font][${font_name}][${font_type}]"
                                                       id="hwl_uploaded_font][${font_name}][${font_type}]"
                                                       value="${font_type}" readonly />
                                            </td>
                                            <td>
                                                <input type="text" name="hwl_uploaded_font][${font_name}][${font_type}][url]"
                                                       id="hwl_uploaded_font][${font_name}][${font_type}][url]"
                                                       value="${font_url}" readonly />
                                            </td>
                                        </tr>`;
                jQuery('#hwl_uploaded_fonts').append(uploadedFont);
            }
        }

        /**
         * Call the generate-stylesheet script and reset the upload dir to the default setting.
         */
        function hwlGenerateStylesheet() {
            var hwlData = hwlSerializeArray($('#hwl-options-form'));

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'hwlAjaxGenerateStyles',
                    uploaded_fonts: hwlData
                },
                success: function(response) {
                    jQuery('#hwl-admin-notices').append(
                        `<div class="updated settings-error notice is-dismissible">
                            <p>${response}</p>
                        </div>`
                    );
                    jQuery('#hwl_uploaded_fonts').each(function() {
                        jQuery(this).fadeOut(700, function() {
                            jQuery(this).remove();
                        })
                    });
                },
                error: function(response) {
                    jQuery('#hwl-admin-notices').append(
                        `<div class="notice notice-error is-dismissible">
                            <p><?php _e( 'The stylesheet could not be created:', 'host-webfonts-local' ); ?> ${response}</p>"
                        </div>`
                    )
                }
            });
        }

        /**
         * Serialize form data to a multi-dimensional array.
         */
        function hwlSerializeArray(data) {
            var result = [];
            data.each(function() {
                var fields = {};
                $.each($(this).serializeArray(), function() {
                    fields[this.name] = this.value;
                });
                result.push(fields);
            });

            return result;
        }
>>>>>>> 7ecb0a932105ee49eefc6048af13862838134180
    </script>
<?php
}

<<<<<<< HEAD
function hwlAjaxSearchGoogleFonts() {
    try {
        $request = curl_init();

        curl_setopt($request, CURLOPT_URL, 'https://google-webfonts-helper.herokuapp.com/api/fonts/' . $_POST['search_query']);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);

	    $result  = curl_exec($request);

	    curl_close($request);
	    wp_die($result);
=======
/**
 * Before each upload we temporarily set our custom upload-directory.
 */
function hwlAjaxSetUploadDir() {
    try {
	    update_option('upload_path',WP_CONTENT_DIR . '/local-fonts');
	    update_option('upload_url_path',content_url() . '/local-fonts');
	    update_option('uploads_use_yearmonth_folders', false);
	    wp_die();
>>>>>>> 7ecb0a932105ee49eefc6048af13862838134180
    } catch (\Exception $e) {
        wp_die($e);
    }
}
<<<<<<< HEAD
add_action('wp_ajax_hwlAjaxSearchGoogleFonts', 'hwlAjaxSearchGoogleFonts');

/**
 * Create cache dir upon plugin (re-)activation.
 */
function hwlCreateCacheDir()
{
	$uploadDir = CAOS_WEBFONTS_UPLOAD_DIR;
	if (!is_dir($uploadDir)) {
		wp_mkdir_p($uploadDir);
	}
}
register_activation_hook(__FILE__, 'hwlCreateCacheDir' );
=======
add_action('wp_ajax_hwlAjaxSetUploadDir', 'hwlAjaxSetUploadDir');


/**
 * After we're done uploading we need to reset the upload-directory.
 */
function hwlAjaxResetUploadDir() {
    try {
	    update_option('upload_path',null);
	    update_option('upload_url_path',null);
	    update_option('uploads_use_yearmonth_folders', true);
	    wp_die();
    } catch (\Exception $e) {
        wp_die($e);
    }
}
add_action('wp_ajax_hwlAjaxResetUploadDir', 'hwlAjaxResetUploadDir');
>>>>>>> 7ecb0a932105ee49eefc6048af13862838134180

/**
 * The function for generating the stylesheet and resetting the upload dir to the default.
 */
function hwlAjaxGenerateStyles() {
    require_once('includes/generate-stylesheet.php');
}
add_action('wp_ajax_hwlAjaxGenerateStyles', 'hwlAjaxGenerateStyles');

/**
 * Once the stylesheet is generated. We can enqueue it.
 */
function hwlEnqueueStylesheet()
{
    $stylesheet = CAOS_WEBFONTS_UPLOAD_DIR . '/'. CAOS_WEBFONTS_FILENAME;
    if (file_exists($stylesheet)) {
	    wp_register_style('hwl-style', CAOS_WEBFONTS_UPLOAD_URL . '/' . CAOS_WEBFONTS_FILENAME);
	    wp_enqueue_style('hwl-style');
    }
}
add_action('wp_enqueue_scripts', 'hwlEnqueueStylesheet' );

function hwlEnqueueAdminJs()
{
    wp_enqueue_script('hwl-admin-js', plugin_dir_url(__FILE__) . 'js/hwl-admin.js', array('jquery'), null, true);
    wp_enqueue_style('hwl-admin.css', plugin_dir_url(__FILE__) . 'css/hwl-admin.css');
}
add_action('admin_enqueue_scripts', 'hwlEnqueueAdminJs');
