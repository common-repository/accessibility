<?php
/**
 * Accessibility: Wordpress Accessibility Plugin
 *
 * Plugin Name: Accessibility
 * Description: Accessibility Utility Widget - A high quality solution for making your WordPress website accessible ready.
 * Version:     1.0.7
 * Author:      Octa Code
 * Author URI: http://octa-code.com
 * Plugin URI: http://acc.magixite.com
 * Copyright:   2015 Octa Code
 * Last Update: 04/20/2024
 * 
 * Text Domain: accessibility
 * Domain Path: /languages/
 */

if (!defined('ABSPATH')) {
    exit; // disable direct access
}


if (!class_exists('OcAccessibilityPlugin')) :

    /**
     * Register the plugin.
     *
     * Display the administration panel, insert JavaScript etc.
     */
    class OcAccessibilityPlugin
    {

        protected $loader;
        protected $version;
        protected $plugin_slug;

        private function __cleanInput($str)
        {
            // Using str_replace() function
            // to replace the word
            $res = str_replace(
                array( '\'', '"',
                ',' , ';', '<', '>' ), ' ', $str
            );

            // Returning the result
            return $res;
        }

        /**
         * Constructor
         */
        public function __construct()
        {
            $this->plugin_slug = 'accessibility';
            load_plugin_textdomain($this->plugin_slug, false, basename(dirname(__FILE__)) . '/languages/');
            $this->version = '1.0.6';


            $this->define_constants();
            $this->setup_actions();
            $this->load_dependencies();

            add_action('admin_menu', array($this, 'accessibility_create_menu'));
        }

        /**
         * Hook WC Brands into WordPress
         */
        private function setup_actions()
        {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_adminscripts'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_adminscripts'));
            add_action('wp_footer', array($this, 'magixite_script'));
        }

        public function magixite_script()
        {
            if (get_option('oc-accessibility-status', 0) < 1) {
                $licenseUrl = OCACCESSIBILITY_PLUGINURL . '/freeCode?oatk=w0rdpre55';
            }
            else {
                $licenseUrl = OCACCESSIBILITY_PLUGINURL . '/license/lw?litk=' . $this->__cleanInput(get_option('oc-accessibility'));
            }
            wp_enqueue_script(
                'accessibility',
                $licenseUrl,
                array(),
                $this->version,
                true
            );
            $language_setting = "";
            if (get_option('oc-accessibility-language', "en_us") !== 'oc_system') {
                $language_setting = "'language': '" . get_option('oc-accessibility-language', "en_us") . "'";
            }

            wp_register_script('accessibility-init', '', [], '', true);
            wp_enqueue_script('accessibility-init');
            wp_add_inline_script('accessibility-init', "setTimeout(function(){octLoader({" . $language_setting . "})}, 1000);");
        }

        // Styles Handling
        public function enqueue_styles()
        {
      
        }

        public function enqueue_adminscripts()
        {
            wp_enqueue_style('admin-style', OCACCESSIBILITY_ASSETS_URL . '/css/admin-style.css', array(), OCACCESSIBILITY_VERSION);
        }

        /**
         * Define Accessibility constants
         */
        private function define_constants()
        {
            define('OCACCESSIBILITY_VERSION', $this->version);
            define('OCACCESSIBILITY_BASE_URL', trailingslashit(plugins_url('accessibility')));
            define('OCACCESSIBILITY_ASSETS_URL', trailingslashit(OCACCESSIBILITY_BASE_URL . 'assets'));
            define('OCACCESSIBILITY_PATH', plugin_dir_path(__FILE__));
            define('OCACCESSIBILITY_PLUGINURL', '//acc.magixite.com');
        }

        /**
         * WP Admin menu links.
         */
        public function accessibility_create_menu()
        {
            //create new top-level menu
            add_menu_page(
                __('Accessibility Settings', $this->plugin_slug), __('Accessibility', $this->plugin_slug), 'manage_options', 'oc-accessibility-admin', array($this, 'accessibility_settings_page'), 'dashicons-universal-access-alt'
            );
            add_submenu_page(
                'oc-accessibility-admin', __('Attachments alt', $this->plugin_slug), __('Attachments alt', $this->plugin_slug), 'manage_options', 'oc-accessibility-media', array($this, 'accessibility_admin_media_page'), 'dashicons-admin-media'
            );
        }

        /**
         * General Settings.
         */
        public function accessibility_settings_page()
        {
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == "save_accessibility_settings") {
                if (isset($_POST['form_nonce']) && wp_verify_nonce($_POST['form_nonce'],'oc-accessibility') && is_user_logged_in()) {
                    $this->_admin_update_accessibility_settings();
                } else {
                    echo '<p>Error: Goodbye hackers! Better luck next time. </p>';
                }
            }
      
            include "includes/accessibility-settings.php";
        }
        /**
         * Attachments media handler.
         */
        public function accessibility_admin_media_page()
        {
            //      update_post_meta($pid, '_wp_attachment_image_alt', $palt);
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == "save_accessibility_attachments_settings") {
                if (isset($_POST['form_nonce']) && wp_verify_nonce($_POST['form_nonce'],'oc-accessibility') && is_user_logged_in()) {
                    $this->_admin_update_attachments();
                } else {
                    echo '<p>Error: Goodbye hackers! Better luck next time. </p>';
                }
            }
            include "includes/accessibility-attachments-alt.php";
        }

        private function load_dependencies()
        {
            include_once OCACCESSIBILITY_PATH . 'includes/class-accessibility-loader.php';

            $this->loader = new OcAccessibility_Loader();
        }

        public function run()
        {
            $this->loader->run();
        }

        public function get_version()
        {
            return $this->version;
        }
    
        public function _admin_update_attachments()
        {
            $alt_data = $this->__cleanInput($_POST['attachments_alt']);
            $title_data = $this->__cleanInput($_POST['attachments_title']);
            if ($alt_data == "" && $title_data == "") {
                $message = __("Nothing Changed", $this->plugin_slug);
                echo "<div class=\"".esc_attr($class)."\"> <p>".esc_html($message)."</p></div>";
                return;
            }
            foreach($alt_data as $pid => $p_alt) {
                update_post_meta($pid, '_wp_attachment_image_alt', $p_alt);
            }
            foreach($title_data as $pid => $p_title) {
                $post = array(
                'ID'           => $pid,
                'post_title'   => $p_title,
                );
                wp_update_post($post);
            }
            $class = "update-nag";

            // Validate the license.
            $message = __("Saved Changes Successfully", $this->plugin_slug);
            $message_two = "";
            echo "<div class=\"".esc_attr($class)."\"> <p>".esc_html($message)."</p><p>".esc_html($message_two)."</p></div>";
        }

        public function get_language_options()
        {
            $languages = array();

            $languages['auto_detect'] = (object) array('name' => 'Auto Detect');
            $languages['he_il'] = (object) array('name' => 'Hebrew');
            $languages['en_us'] = (object) array('name' => 'English');
            $languages['es_es'] = (object) array('name' => 'Spanish');
            $languages['ru_ru'] = (object) array('name' => 'Russian');
            $languages['oc_system'] = (object) array('name' => '- From License');

            return $languages;
        }

        private function _get_license_data($lkey)
        {
            if ($lkey == "") {
                return new stdClass();
            }

            $siteSrc = urlencode(site_url());
            $remoteGetOptions = array('sslverify' => false);
            $rsp = wp_safe_remote_get("http:" . OCACCESSIBILITY_PLUGINURL . "/license/v?litk=" . $lkey . "&utm_source=" . $siteSrc, $remoteGetOptions);

            $jsonRsp = wp_remote_retrieve_body($rsp);

            $licenseData = json_decode($jsonRsp);
            return $licenseData;
        }

        /**
         * Post form action for the update prices for brand.
         */
        function _admin_update_accessibility_settings() {
            $lkey = sanitize_text_field($_POST["magixite_license"]);
            update_option('oc-accessibility', $lkey);
            update_option('oc-accessibility-language', sanitize_text_field($_POST["ac_language"]));
            if ($lkey == '') {
                $status = 0;
            }
            else {
                $licenseData = $this->_get_license_data($lkey);
                if ($licenseData->status == 'Success') {
                    $status = 1;
                }
                else {
                    $status = -1;
                }
            }

            update_option('oc-accessibility-status', $status);
            $class = "update-nag";

            // Validate the license.
            $message = __("Saved Settings Successfully", $this->plugin_slug);
            $message_two = "";
            echo "<div class=\"".esc_attr($class)."\"> <p>".esc_html($message)."</p><p>".esc_html($message_two)."</p></div>";
        }

        function _get_license_message($license_data)
        {
            if (!$license_data) {
                $licenseObtainLink = 'https:' . OCACCESSIBILITY_PLUGINURL . '?wp-ref';
                return "Get your <strong>free</strong> license <a href=\"".esc_attr($licenseObtainLink)."\" target=\"_blank\">here</a> to unlock more design options.";
            }
            if ($license_data->status == 'Failure') {
                return "<span style=\"color: red;\">License Did not validate</span>";
            }
            if (isset($license_data->data->expiry)) {
                $daysLeft = (intval($license_data->data->expiry) - time()) / (3600 * 24);
                if ($daysLeft > 0) {
                    return "Success! <strong>" . esc_html(round($daysLeft)) . "</strong> days for license renewal.";
                }
                else {
                    return "Oh Oh! <strong> Your license has expired " . esc_html(round($daysLeft * (-1))) . " days ago.</strong><br/>Please go ahead and renew.";
                }
            }

            return "Nothing to tell ya..";
        }
    
        public function get_attachments($all = false)
        {
            global $wpdb;
      
            //      $query_only_with_alt = "SELECT `wp`.`ID`, `wp`.`post_author`, `wp`.`post_date`, `wp`.`post_title`, `wp`.`post_name`,
            //        `wp`.`guid`, `wp`.`post_parent`, `wpm`.`meta_key`, `wpm`.`meta_value`
            //        FROM $wpdb->posts `wp` 
            //        LEFT JOIN $wpdb->postmeta `wpm` ON `wp`.`ID` = `wpm`.`post_id` 
            //        WHERE `wp`.`post_type` = 'attachment' AND `wpm`.`meta_key` = \"_wp_attachment_image_alt\" AND `wpm`.`meta_value` != ''";
      
            $query_all_attachments = "SELECT `wp`.`ID`, `wp`.`post_author`, `wp`.`post_date`, `wp`.`post_title`, `wp`.`post_name`,
        `wp`.`guid`, `wp`.`post_parent`, `wpp`.`post_title` `parent_title` FROM $wpdb->posts `wp` "
            . " LEFT JOIN $wpdb->posts `wpp` ON `wp`.`post_parent` = `wpp`.`ID`"
            . " WHERE `wp`.`post_type` = 'attachment' ORDER BY `wp`.`ID` DESC";
      
            $posts = $wpdb->get_results($query_all_attachments);
      
            return $posts;
        }

    }

endif;

/**
 * Runs the main instance of OC_Accessibility to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object OC_Accessibility
 */
function Run_Accessibility_plugin()
{
    $plugin = new OcAccessibilityPlugin();
    $plugin->run();
}

Run_Accessibility_plugin();
