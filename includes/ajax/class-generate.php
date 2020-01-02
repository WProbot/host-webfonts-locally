<?php
/* * * * * * * * * * * * * * * * * * * * *
 *
 *  ██████╗ ███╗   ███╗ ██████╗ ███████╗
 * ██╔═══██╗████╗ ████║██╔════╝ ██╔════╝
 * ██║   ██║██╔████╔██║██║  ███╗█████╗
 * ██║   ██║██║╚██╔╝██║██║   ██║██╔══╝
 * ╚██████╔╝██║ ╚═╝ ██║╚██████╔╝██║
 *  ╚═════╝ ╚═╝     ╚═╝ ╚═════╝ ╚═╝
 *
 * @package  : OMGF
 * @author   : Daan van den Bergh
 * @copyright: (c) 2019 Daan van den Bergh
 * @url      : https://daan.dev
 * * * * * * * * * * * * * * * * * * * */

defined('ABSPATH') || exit;

class OMGF_AJAX_Generate extends OMGF_AJAX
{
    /** @var array $fonts */
    private $fonts = [];

    /**
     * OMGF_AJAX_Generate_Styles constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->init();
    }

    /**
     * Generate the Stylesheet
     */
    private function init()
    {
        header("Content-type: text/css");

        $this->insert_promo();

        $selectedFonts = $this->db->get_total_fonts();

        $this->process_fonts($selectedFonts);

        $fonts = implode("\n", $this->fonts);
        $file  = OMGF_UPLOAD_DIR . '/' . OMGF_FILENAME;

        /**
         * If the file can be created and uploaded. Let's try to write it.
         */
        try {
            $stylesheet = fopen($file, 'w') or $this->throw_error(400, "Cannot create file {$file}");
            fwrite($stylesheet, $fonts);
            fclose($stylesheet);
            wp_die(__('Stylesheet was successfully generated and added to your theme\'s header.'));
        } catch (Exception $e) {
            $this->throw_error($e->getCode(), __("Stylesheet could not be generated: $e"));
        }
    }

    /**
     * Insert promo material :)
     *
     * The alignment is crooked, so it'll look nice in the stylesheet.
     */
    private function insert_promo()
    {
        $this->fonts[] = "/** 
 * This file is automagically generated by OMGF
 *
 * @author: Daan van den Bergh
 * @copyright: (c) 2019 Daan van den Bergh
 * @url: " . OMGF_SITE_URL . "
 */";
    }

    /**
     * Prepare fonts for generation.
     */
    private function process_fonts($fonts)
    {
        $fontDisplay = OMGF_DISPLAY_OPTION;

        $i = 1;

        foreach ($fonts as $font) {
            $fontUrlEot  = isset($font->url_eot) ? array(0 => esc_url_raw($font->url_eot)) : array();
            $fontSources = isset($font->url_woff2) ? array('woff2' => esc_url_raw($font->url_woff2)) : array();
            $fontSources = $fontSources + (isset($font->url_woff) ? array('woff' => esc_url_raw($font->url_woff)) : array());
            $fontSources = $fontSources + (isset($font->url_ttf) ? array('truetype' => esc_url_raw($font->url_ttf)) : array());
            $locals      = explode(',', sanitize_text_field($font->local));

            $this->fonts[$i] = "@font-face { \n";
            $this->fonts[$i] .= $this->build_property('font-family', $font->font_family);
            $this->fonts[$i] .= $this->build_property('font-display', $fontDisplay);
            $this->fonts[$i] .= $this->build_property('font-style', $font->font_style);
            $this->fonts[$i] .= $this->build_property('font-weight', $font->font_weight);
            $this->fonts[$i] .= isset($fontUrlEot) ? "  src: " . $this->build_source_string($fontUrlEot) : '';
            $this->fonts[$i] .= "  src: " . $this->build_source_string($locals, 'local', false);
            // There'll always be at least one font available, so no need to check here if $fontSources is set.
            $this->fonts[$i] .= $this->build_source_string($fontSources);
            $this->fonts[$i] .= "}";

            $i++;
        }
    }

    /**
     * @param $property
     * @param $value
     *
     * @return string
     */
    private function build_property($property, $value)
    {
        $value = sanitize_text_field($value);

        return "  $property: $value;\n";
    }

    /**
     * @param        $sources
     * @param string $type
     * @param bool   $endSemiColon
     *
     * @return string
     */
    private function build_source_string($sources, $type = 'url', $endSemiColon = true)
    {
        $lastSrc = end($sources);
        $source  = '';

        foreach ($sources as $format => $url) {
            $source .= "  $type('$url')" . (!is_numeric($format) ? " format('$format')" : '');

            if ($url === $lastSrc && $endSemiColon) {
                $source .= ";\n";
            } else {
                $source .= ",\n";
            }
        }

        return $source;
    }
}