<?php
/**
 * Plugin Name: WishList Completed Contents
 * Description: Display the list of completed contents from WishList Member for a user on their profile.
 * Version: 1.0.0
 * Author: Obi Juan & Team Fabi Paolini
 * Author URI: http://www.obijuan.dev
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wishlist-completed-contents
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 6.0
 *
 * @package  wishlistCompletedContents
 * @category plugin
 * @author   Obi Juan & Team Fabi Paolini
 * @license  http://www.gnu.org/licenses/gpl-2.0.html
 * @link     http://www.obijuan.dev
 * @since    1.0
 */

defined( 'ABSPATH' ) || exit( 'Get outta here!' );

/**
 * WishList_Completed_Contents
 *
 * @since 1.0
 */
final class WishList_Completed_Contents {

	/**
	 *Plugin version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Plugin slug.
	 */
	const SLUG = 'wishlist-completed-contents';

	/**
	 * Coursecure slug.
	 */
	const COURSECURE = 'CourseCure';

	/**
	 * Singleton instance.
	 *
	 * @var WishList_Completed_Contents
	 */
	private static $_instance;

	/**
	 * Singleton instance
	 *
	 * @return WishList_Completed_Contents
	 */
	public static function get_instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	private function __construct() {

		$this->_define_constants();

		$this->wishlist_check_php_version_requirement();

		$this->coursecure_check_dependency();

		$this->hooks_init();

		$this->get_active_plugins();
	}

	/**
	 * Define constants
	 *
	 * @since 1.0
	 */
	private function _define_constants() {
		define( 'WISHLIST_COMPLETED_CONTENTS_VERSION', self::VERSION );
		define( 'WISHLIST_COMPLETED_CONTENTS_SLUG', self::SLUG );
		define( 'WISHLIST_COMPLETED_CONTENTS_TEXTDOMAIN', self::SLUG );
		define( 'WISHLIST_COMPLETED_CONTENTS_PHP_MINIMUM_VERSION', '7.4' );
		define( 'WISHLIST_COMPLETED_CONTENTS_NAME', 'WishList Completed Contents' );
		define( 'WISHLIST_COMPLETED_CONTENTS_DIR', plugin_dir_path( __FILE__ ) );
		define( 'WISHLIST_COMPLETED_CONTENTS_URL', plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Hooks init
	 *
	 * Initialize mandatory hooks.
	 *
	 * @since 1.0
	 */
	private function hooks_init() {
		// Add completed contents table to user profile.
		add_action( 'show_user_profile', array( $this, 'display_completed_contents_table' ) );

		// Add completed contents table to user profile.
		add_action( 'edit_user_profile', array( $this, 'display_completed_contents_table' ) );
	}

	/**
	 * Load text domain
	 *
	 * @since 1.0
	 */
	public function load_text_domain() {
		load_plugin_textdomain(
			WISHLIST_COMPLETED_CONTENTS_TEXTDOMAIN,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Compares PHP version requirement
	 *
	 * @access private
	 * @return bool. True if PHP version requirement is met.
	 * @since  1.0
	 */
	public function wishlist_site_meets_php_minimum_version() {
		return version_compare( phpversion(), WISHLIST_COMPLETED_CONTENTS_PHP_MINIMUM_VERSION, '>=' );
	}

	/**
	 * Admin notice
	 *
	 * Display an admin notice if PHP version requirement is not met.
	 *
	 * @since 1.0
	 */
	public function wishlist_check_php_version_requirement() {
		// Check if PHP version meets requirement.
		if ( ! $this->wishlist_site_meets_php_minimum_version() ) {

			// Display admin notice.
			add_action( 'admin_notices', array( $this, 'wishlist_admin_notice_php_version' ) );
		}
	}

	/**
	 * Admin notice
	 *
	 * Display an admin notice if PHP version requirement is not met.
	 *
	 * @since 1.0
	 */
	public function coursecure_check_dependency() {
		if ( ! $this->search_string_in_array( 'coursecure', $this->get_active_plugins() ) ) {
			add_action( 'admin_notices', array( $this, 'coursecoure_admin_notice_dependency' ) );
		}
	}

	/**
	 * Admin notice
	 *
	 * Admin notice markup for the PHP version requirement evaluation.
	 *
	 * @since 1.0
	 */
	public function wishlist_admin_notice_php_version() {
		?>

		<div class="notice notice-error">
			<p>
		<?php
		echo wp_kses_post(
			sprintf(
			/* translators: %s: Minimum required PHP version */
				__( '%1$s requires PHP version %2$s or later. Please upgrade PHP or disable the plugin.', 'wishlist-completed-contents' ),
				esc_html( WISHLIST_COMPLETED_CONTENTS_NAME ),
				esc_html( WISHLIST_COMPLETED_CONTENTS_PHP_MINIMUM_VERSION )
			)
		);
		?>
			</p>
		</div>
		<?php
	}

	/**
	 * Admin notice
	 *
	 * Admin notice markup for the 'CourseCure' plugin requirement evaluation.
	 *
	 * @since 1.0
	 */
	public function coursecoure_admin_notice_dependency() {
		?>

		<div class="notice notice-error">
			<p>
		<?php
		echo wp_kses_post(
			sprintf(
			/* translators: %s: Wishlist Completed Contents plugin name. 2nd: 'CourseCure' plugin name. */
				__( '%1$s requires the %2$s plugin. Please install it or disable the plugin.', 'wishlist-completed-contents' ),
				esc_html( WISHLIST_COMPLETED_CONTENTS_NAME ),
				esc_html( self::COURSECURE )
			)
		);
		?>
			</p>
		</div>
		<?php
	}

	/**
	 * Display completed contents table
	 *
	 * Display the list of completed contents from WishList Member for a user on their profile.
	 *
	 * @param object $user. The user object.
	 * @since 1.0
	 */
	public function display_completed_contents_table( object $user ) {

		$completed_contents = get_user_meta( $user->ID, 'completed_contents', true );
		$completed_contents_array = maybe_unserialize( $completed_contents );

		if ( is_array( $completed_contents_array ) ) {
			echo '<h3 id="completedContents">Completed Contents</h3>';
			// Modify the button's onclick event to append a query parameter and reload.
			echo '<table>';
			echo '<tr><th>Post ID</th><th>Post Title</th><th>Post Type</th><th>Timestamp</th><th>Post Actions</th></tr>';

			foreach ( $completed_contents_array as $post_id => $timestamp ) {
				$edit_post_link = get_edit_post_link( $post_id );
				$view_post_link = get_permalink( $post_id );
				$post_title = get_the_title( $post_id );
				$post_type = get_post_type( $post_id );

				echo '<tr onMouseOver="this.style.background=\'#00acc8\'" onMouseOut="this.style.background=\'#f0f0f1\'">';
				echo '<td>' . esc_html( $post_id ) . '</td>';
				echo '<td>' . esc_html( $post_title ) . '</td>';
				echo '<td>' . esc_html( $post_type ) . '</td>';
				echo '<td>' . esc_html( gmdate( 'Y-m-d H:i:s', $timestamp ) ) . '</td>'; // Format the timestamp in GMT time.
				echo '<td>';
				if ( $edit_post_link ) {
					echo '<a href="' . esc_url( $edit_post_link ) . '" target="_blank">Edit</a> | ';
				}
				if ( $view_post_link ) {
					echo '<a href="' . esc_url( $view_post_link ) . '" target="_blank">View</a>';
				}
				echo '</td>';
				echo '</tr>';
			}

			echo '</table>';
		} else {
			echo 'No completed contents data available.';
		}
	}

	/**
	 * Search string in array
	 *
	 * Helper method to search for a string in an array.
	 *
	 * @param $search_string. The string to search for.
	 * @param $array. The array to search.
	 * @return bool
	 */
	public function search_string_in_array( string $search_string, array $array ) {
		foreach ( $array as $element ) {
			if ( strpos( $element, $search_string ) !== false ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get active plugins
	 *
	 * Helper method to get the active plugins.
	 *
	 * @return array
	 */
	public function get_active_plugins() {
		return get_option( 'active_plugins' );
	}
}

/**
 * Initialize the plugin.
 *
 * @since 1.0
 */
add_action(
	'plugins_loaded',
	function () {
		WishList_Completed_Contents::get_instance();
	}
);