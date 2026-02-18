<?php
/**
 * Plugin Name: Plan Your Catering
 * Description: Interactive catering estimate & enquiry system.
 * Version: 1.0.0
 * Author: Devang Jani
 * Text Domain: plan-your-catering
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin constants
 */
define('PYC_VERSION', '1.0.0');
define('PYC_PLUGIN_FILE', __FILE__);
define('PYC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PYC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PYC_PLUGIN_PATH', plugin_dir_path(__FILE__));


/**
 * Load core loader
 */
require_once PYC_PLUGIN_DIR . 'includes/class-pyc-loader.php';

/**
 * Initialize plugin
 */
function pyc_init_plugin(): void
{
    $loader = new PYC_Loader();
    $loader->run();
}
add_action('plugins_loaded', 'pyc_init_plugin');

/**
 * Activation hook
 */
function pyc_activate(): void
{
    // Reserved for future use
}
register_activation_hook(__FILE__, 'pyc_activate');

/**
 * Deactivation hook
 */
function pyc_deactivate(): void
{
    // Reserved for future use
}
register_deactivation_hook(__FILE__, 'pyc_deactivate');
