<?php
/**
 * Plugin name: VC Image Splitter - Lite
 * Author: VC Splitter
 * Version: 1.0.1
 * Plugin URI: https://www.vcsplitter.com/image/
 * Author URI: https://www.vcsplitter.com
 * Description: Effortlessly split test buttons within Visual Composer and track the preferences of your visitors.
 */

global $wps_vcs_is_active;
$wps_vcs_is_active = false;

define('WPS_VCS_IS_DIR', plugin_dir_path(__FILE__));
define('WPS_VCS_IS_URI', plugin_dir_url(__FILE__));
define('WPS_VCS_IS_TEXTDOMAIN', 'wps_is_vcs');
define('WPS_VCS_IS_NAME', 'VC Image Splitter');
define('WPS_VCS_IS_OPT_NAME', 'wpsc_vcs_is_activated');
define('WPS_VCS_IS_MENU_NAME', 'VC Image Splitter');
define('WPS_VCS_IS_SU_OPT_NAME', 'wpsc_vcs_is_signup');
define('WPS_VCS_IS_PC_NAME', 'wpsc_vcs_is_pc');

// Autoload anonymous script
spl_autoload_register(function($class) {

  // Example:
  // wps_vcs_admin_shortcode
  // remove - wps_vcs_
  // match admin as folder
  // match shortcode as filename

  // wps_vcs_admin_shortcode
  if (strpos($class, 'wps_vcs_is_') !== false) {
    // It's our file
    $class = str_replace('wps_vcs_is_', '', $class);
    // Now we have just "admin_shortcode"
    $class_extract = explode('_', $class);
    // Include once
    include_once $class_extract[0] . '/' . $class_extract[1] . '.php';
  }

});


// Main Class Call
class wps_vcs_is {


  public $version = '1.0.1';
  public $slug = 'wps_vcs_is';
  public $textdomain = WPS_VCS_IS_TEXTDOMAIN;
  public $stats = 'vcs_split_stats';

  private $admin = array();
  private $frontend = array();


  public function init() {

    if (!function_exists('vc_add_shortcode_param')) {
      add_action('admin_notices', array($this, 'vc_missing'));
    } else {

      // Include VC Things...
      include_once 'admin/vc_register.php';

      // Default Elements
      self::default_elements();

      if (is_admin()) {
        add_action('admin_notices', array($this, 'verification_required'));
        $this->admin['menu'] = new wps_vcs_is_admin_menu($this);
        $this->admin['enqueues'] = new wps_vcs_is_admin_enqueues($this);
        $this->admin['ajax'] = new wps_vcs_is_admin_ajax($this);
      } else {
        $this->frontend['shortcode'] = new wps_vcs_is_frontend_shortcode($this);
        $this->frontend['enqueues'] = new wps_vcs_is_frontend_enqueues($this);
      }

      $this->frontend['ajax'] = new wps_vcs_is_frontend_ajax($this);

    }
  } // init


  public function vc_missing() {
    echo '<div class="notice notice-error">
      <p><strong>The ' . WPS_VCS_IS_NAME . '</strong> requires Visual Composer version 3.7.2 or greater.</p>
    </div>';
  } // vc_missing


  public function verification_required() {
    $pc = get_option(WPS_VCS_IS_SU_OPT_NAME);
    $signup = get_option(WPS_VCS_IS_SU_OPT_NAME);
    if (empty($signup)) {
      ?>
      <div class="notice notice-warning notice-danger">
        <p><strong><?php echo WPS_VCS_IS_NAME; ?></strong> requires <a href="<?php echo admin_url('admin.php?page=' . $this->slug); ?>">e-mail verification</a> in order to function with Visual Composer.</p>
      </div>
      <?php
    }
  } // verification_required


  public static function activation() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    self::default_elements();

    $split_link_sql = "CREATE TABLE IF NOT EXISTS `vcs_split_links` (`ID` int(11) NOT NULL AUTO_INCREMENT,`link` tinyint(1) NOT NULL,`button` tinyint(1) NOT NULL,`post_ID` int(11) NOT NULL,PRIMARY KEY (`ID`),UNIQUE KEY `post_ID` (`post_ID`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    $split_stats_sql = "CREATE TABLE IF NOT EXISTS `vcs_split_stats` (`ID` int(11) NOT NULL AUTO_INCREMENT,`post_ID` int(11) NOT NULL DEFAULT '0',`split_ID` varchar(64) COLLATE utf8_unicode_ci NOT NULL,`split_name` text COLLATE utf8_unicode_ci NOT NULL,`split_Wrapper` varchar(64) COLLATE utf8_unicode_ci NOT NULL,`first_occurance` datetime NOT NULL,`last_occurance` datetime NOT NULL,`views` int(11) NOT NULL,`clicks` int(11) NOT NULL,`bounce` int(11) NOT NULL,PRIMARY KEY (`ID`),UNIQUE KEY `post_ID` (`post_ID`,`split_ID`,`split_Wrapper`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    dbDelta($split_link_sql);
    dbDelta($split_stats_sql);
  } // activation


  public static function default_elements() {
    $cwa = get_option('wsp_vcs_awc');
    $cwaa = explode(',', $cwa);

    if (!in_array(strrev('egami_elgnis_cv'), $cwaa)) {
      $cwa .= ',' . strrev('egami_elgnis_cv');
    }

    update_option('wsp_vcs_awc', $cwa);
  }


  public static function deactivation() {
    delete_option(WPS_VCS_IS_PC_NAME);
    delete_option(WPS_VCS_IS_OPT_NAME);
    delete_option(WPS_VCS_IS_SU_OPT_NAME);

    $cwa = get_option('wsp_vcs_awc');
    $cwaa = explode(',', $cwa);

    foreach ($cwaa as $key => $value) {
      if ($value == strrev('egami_elgnis_cv')) {
        unset($cwaa[$key]);
        $cwa = implode(',', $cwaa);
        break;
      }
    }

    update_option('wsp_vcs_awc', $cwa);
  } // deactivation

} // wps_vcs

$wps_vcs_is = new wps_vcs_is();
$wps_vcs_is->init();

register_activation_hook(__FILE__, array('wps_vcs_is', 'activation'));
register_deactivation_hook(__FILE__, array('wps_vcs_is', 'deactivation'));