<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

new Events_Maker_Welcome_Page();

/**
 * Events_Maker_Welcome_Page Class.
 */
class Events_Maker_Welcome_Page {

	public function __construct() {
		// set instance
		Events_Maker()->welcome = $this;

		// actions
		add_action( 'admin_menu', array( &$this, 'admin_menus' ) );
		add_action( 'admin_head', array( &$this, 'admin_head' ) );
		add_action( 'admin_init', array( &$this, 'welcome' ) );
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		$welcome_page_title = __( 'Welcome to Events Maker', 'events-maker' );
		// about
		$about = add_dashboard_page( $welcome_page_title, $welcome_page_title, 'manage_options', 'events-maker-about', array( $this, 'about_screen' ) );
	}

	/**
	 * Add styles just for this page, and remove dashboard page links.
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'events-maker-about' );
	}

	/**
	 * Intro text/links shown on all about pages.
	 */
	private function intro() {

		// get plugin version
		$plugin_version = substr( get_option( 'events_maker_version' ), 0, 3 );
		?>
		<h1><?php printf( __( 'Welcome to Events Maker', 'events-maker' ), $plugin_version ); ?></h1>

		<div class="about-text events-maker-about-text">
		<?php
		printf( __( 'Events Maker is an easy to use but flexible events management plugin made the WordPress way.', 'events-maker' ), $plugin_version );
		?>
		</div>

		<p class="events-maker-actions">
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=event&page=events-settings' ) ); ?>" class="button button-primary"><?php _e( 'Settings', 'events-maker' ); ?></a>
			<a href="http://www.dfactory.eu/docs/events-maker-plugin/?utm_source=events-maker-welcome&utm_medium=button&utm_campaign=documentation" class="button button-primary" target="_blank"><?php _e( 'Documentation', 'events-maker' ); ?></a>
			<a href="http://www.dfactory.eu/support/?utm_source=events-maker-welcome&utm_medium=button&utm_campaign=support" class="button button-primary" target="_blank"><?php _e( 'Support', 'events-maker' ); ?></a>
			<a href="http://www.dfactory.eu/?utm_source=events-maker-welcome&utm_medium=button&utm_campaign=dfactory-plugins" class="button button-primary" target="_blank"><?php _e( 'dFactory Plugins', 'events-maker' ); ?></a>
		</p>

		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php if ( $_GET['page'] == 'events-maker-about' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'events-maker-about' ), 'index.php' ) ) ); ?>">
		<?php _e( 'About Events Maker', 'events-maker' ); ?>		
			</a>
		</h2>
				<?php
			}

	/**
	 * Ooutput the about screen.
	 */
	public function about_screen() {
		?>
		<div class="wrap about-wrap">

		<?php $this->intro(); ?>

			<div class="changelog">

				<h3><?php _e( 'Main Features', 'events-maker' ); ?></h3>

				<div class="feature-section col three-col">

					<div>
						<img src="<?php echo EVENTS_MAKER_URL . '/images/welcome-01.jpg'; ?>" alt="Events Management screenshot" style="width: 99%; margin: 0 0 1em;" />
						<h4><?php _e( 'Easy Event Management', 'events-maker' ); ?></h4>
						<p><?php _e( 'Set up date and time, title, desciption and other details of your events in a WordPress native interface.', 'events-maker' ); ?></p>
					</div>

					<div>
						<img src="<?php echo EVENTS_MAKER_URL . '/images/welcome-02.jpg'; ?>" alt="Locations and Organizers screenshot" style="width: 99%; margin: 0 0 1em;" />
						<h4><?php _e( 'Locations and Organizers', 'events-maker' ); ?></h4>
						<p><?php _e( 'Organize your events in reusable Categories, Tags, Locations and Organizers.', 'events-maker' ); ?></p>
					</div>

					<div class="last-feature">
						<img src="<?php echo EVENTS_MAKER_URL . '/images/welcome-03.jpg'; ?>" alt="Event Tickets screenshot" style="width: 99%; margin: 0 0 1em;" />
						<h4><?php _e( 'Multiple Event Tickets', 'events-maker' ); ?></h4>
						<p><?php _e( 'Create free or paid events and add as many different types of tickets as you need.', 'events-maker' ); ?></p>
					</div>

				</div>

				<div class="feature-section col three-col">

					<div>
						<img src="<?php echo EVENTS_MAKER_URL . '/images/welcome-04.jpg'; ?>" alt="Ajax Calendar screenshot" style="width: 99%; margin: 0 0 1em;" />
						<h4><?php _e( '6 Widgets incl. Ajax Calendar', 'events-maker' ); ?></h4>
						<p><?php _e( 'Complete set of widgets allow you to display event details anywhere in a theme.', 'events-maker' ); ?></p>
					</div>

					<div>
						<img src="<?php echo EVENTS_MAKER_URL . '/images/welcome-05.jpg'; ?>" alt="Settings screenshot" style="width: 99%; margin: 0 0 1em;" />
						<h4><?php _e( 'Highly Customizable Settings', 'events-maker' ); ?></h4>
						<p><?php _e( 'Easily control different areas of the plugin including Permalinks, Capabilities and many other.', 'events-maker' ); ?></p>
					</div>

					<div class="last-feature">
						<img src="<?php echo EVENTS_MAKER_URL . '/images/welcome-06.jpg'; ?>" alt="WPML Compatibility screenshot" style="width: 99%; margin: 0 0 1em;" />
						<h4><?php _e( 'WPML Compatibility', 'events-maker' ); ?></h4>
						<p><?php _e( 'Are you running a multilingual events site? Events Maker is fully compatible with WPML (and probably other multilingual plugins too).', 'events-maker' ); ?></p>
					</div>

				</div>

			</div>

			<div class="changelog">

				<h3><?php _e( 'Under the Hood', 'events-maker' ); ?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'Custom Post Types & Custom Taxonomies', 'events-maker' ); ?></h4>
						<p><?php _e( 'Events Maker is built using WordPress custom post types, custom taxonomies and custom post fields. ', 'events-maker' ); ?></p>
					</div>
					<div>
						<h4><?php _e( 'Filters and Actions', 'events-maker' ); ?></h4>
						<p><?php _e( 'We have included various hooks and filters so you can plug-into these hooks to extend the functionalities.', 'events-maker' ); ?></p>
					</div>

					<div class='last-feature'>
						<h4><?php _e( 'Custom Functions', 'events-maker' ); ?></h4>
						<p><?php _e( 'There are 26 custom functions available. You can use it to make Events Maker suite your individual needs.', 'events-maker' ); ?></p>
					</div>

				</div>

			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=event&page=events-settings' ) ); ?>"><?php _e( 'Go to Events Maker Settings', 'events-maker' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Send user to the welcome page on first activation.
	 */
	public function welcome() {

		// bail if no activation redirect transient is set
		if ( ! get_transient( 'em_activation_redirect' ) )
			return;

		// delete the redirect transient
		delete_transient( 'em_activation_redirect' );

		// bail if activating from network, or bulk, or within an iFrame
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) || defined( 'IFRAME_REQUEST' ) )
			return;

		if ( (isset( $_GET['action'] ) && 'upgrade-plugin' == $_GET['action']) && (isset( $_GET['plugin'] ) && strstr( $_GET['plugin'], 'events-maker.php' )) )
			return;

		wp_safe_redirect( admin_url( 'index.php?page=events-maker-about' ) );
		exit;
	}

}
