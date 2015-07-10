<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

new Events_Maker_Settings();

/**
 * Events_Maker_Settings Class.
 */
class Events_Maker_Settings {

	private $capabilities;
	private $errors;
	private $orderby_opts;
	private $order_opts;
	private $positions;
	private $supports;
	private $tabs;

	public function __construct() {
		// set instance
		Events_Maker()->settings = $this;

		// actions
		add_action( 'admin_menu', array( &$this, 'settings_page' ) );
		add_action( 'admin_init', array( &$this, 'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'settings_errors' ) );
		add_action( 'after_setup_theme', array( &$this, 'load_defaults' ) );
		add_action( 'admin_init', array( &$this, 'check_action_pages' ), 1 );
		add_action( 'save_post', array( &$this, 'synchronize_save_post' ), 10, 2 );
	}

	/**
	 * Load defaults.
	 */
	public function load_defaults() {
		if ( ! is_admin() )
			return;

		$this->orderby_opts = array(
			'publish'	 => __( 'Publish date', 'events-maker' ),
			'start'		 => __( 'Events start date', 'events-maker' ),
			'end'		 => __( 'Events end date', 'events-maker' )
		);

		$this->order_opts = array(
			'asc'	 => __( 'Ascending', 'events-maker' ),
			'desc'	 => __( 'Descending', 'events-maker' )
		);

		$this->positions = array(
			'before' => __( 'before the content', 'events-maker' ),
			'after'	 => __( 'after the content', 'events-maker' ),
			'manual' => __( 'manual', 'events-maker' )
		);

		$this->errors = apply_filters( 'em_settings_errors', array(
			'settings_gene_saved'	 => __( 'General settings saved.', 'events-maker' ),
			'settings_disp_saved'	 => __( 'Display settings saved.', 'events-maker' ),
			'settings_temp_saved'	 => __( 'Templates settings saved.', 'events-maker' ),
			'settings_caps_saved'	 => __( 'Capabilities settings saved.', 'events-maker' ),
			'settings_perm_saved'	 => __( 'Permalinks settings saved.', 'events-maker' ),
			'settings_gene_reseted'	 => __( 'General settings restored to defaults.', 'events-maker' ),
			'settings_disp_reseted'	 => __( 'Display settings restored to defaults.', 'events-maker' ),
			'settings_temp_reseted'	 => __( 'Templates settings restored to defaults.', 'events-maker' ),
			'settings_caps_reseted'	 => __( 'Capabilities settings restored to defaults.', 'events-maker' ),
			'settings_perm_reseted'	 => __( 'Permalinks settings restored to defaults.', 'events-maker' ),
			'settings_page_created'	 => __( 'Page created successfully.', 'events-maker' ),
			'settings_page_failed'	 => __( 'Page was not created.', 'events-maker' )
		) );

		$this->tabs = apply_filters( 'em_settings_tabs', array(
			'general'		 => array(
				'name'	 => __( 'General', 'events-maker' ),
				'key'	 => 'events_maker_general',
				'submit' => 'save_em_general',
				'reset'	 => 'reset_em_general'
			),
			'display'		 => array(
				'name'	 => __( 'Display', 'events-maker' ),
				'key'	 => 'events_maker_display',
				'submit' => 'save_em_display',
				'reset'	 => 'reset_em_display'
			),
			'templates'		 => array(
				'name'	 => __( 'Templates', 'events-maker' ),
				'key'	 => 'events_maker_templates',
				'submit' => 'save_em_templates',
				'reset'	 => 'reset_em_templates'
			),
			'capabilities'	 => array(
				'name'	 => __( 'Capabilities', 'events-maker' ),
				'key'	 => 'events_maker_capabilities',
				'submit' => 'save_em_capabilities',
				'reset'	 => 'reset_em_capabilities'
			),
			'permalinks'	 => array(
				'name'	 => __( 'Permalinks', 'events-maker' ),
				'key'	 => 'events_maker_permalinks',
				'submit' => 'save_em_permalinks',
				'reset'	 => 'reset_em_permalinks'
			)
		) );

		$this->capabilities = apply_filters( 'em_settings_capabilities', array(
			'publish_events'			 => __( 'Publish Events', 'events-maker' ),
			'edit_events'				 => __( 'Edit Events', 'events-maker' ),
			'edit_others_events'		 => __( 'Edit Others Events', 'events-maker' ),
			'edit_published_events'		 => __( 'Edit Published Events', 'events-maker' ),
			'delete_published_events'	 => __( 'Delete Published Events', 'events-maker' ),
			'delete_events'				 => __( 'Delete Events', 'events-maker' ),
			'delete_others_events'		 => __( 'Delete Others Events', 'events-maker' ),
			'read_private_events'		 => __( 'Read Private Events', 'events-maker' ),
			'manage_event_categories'	 => __( 'Manage Event Categories', 'events-maker' )
		) );

		$this->supports = array(
			'title'			 => __( 'title', 'events-maker' ),
			'editor'		 => __( 'content editor', 'events-maker' ),
			'excerpt'		 => __( 'excerpt', 'events-maker' ),
			'thumbnail'		 => __( 'thumbnail', 'events-maker' ),
			'gallery'		 => __( 'gallery', 'events-maker' ),
			'custom-fields'	 => __( 'custom fields', 'events-maker' ),
			'author'		 => __( 'author', 'events-maker' ),
			'comments'		 => __( 'comments', 'events-maker' ),
			'trackbacks'	 => __( 'trackbacks', 'events-maker' ),
			'revisions'		 => __( 'revisions', 'events-maker' )
		);

		if ( Events_Maker()->options['general']['use_tags'] === true )
			$this->capabilities['manage_event_tags'] = __( 'Manage Event Tags', 'events-maker' );

		$this->capabilities['manage_event_locations'] = __( 'Manage Event Locations', 'events-maker' );

		if ( Events_Maker()->options['general']['use_organizers'] === true )
			$this->capabilities['manage_event_organizers'] = __( 'Manage Event Organizers', 'events-maker' );
	}

	/**
	 * Display errors and notices.
	 */
	public function settings_errors() {
		settings_errors( 'em_settings_errors' );
	}

	/**
	 * Add options page as submenu to events.
	 */
	public function settings_page() {
		add_submenu_page( 'edit.php?post_type=event', __( 'Settings', 'events-maker' ), __( 'Settings', 'events-maker' ), 'manage_options', 'events-settings', array( $this, 'options_page' ) );
	}

	/**
	 * Options page output.
	 */
	public function options_page() {
		$tab_key = (isset( $_GET['tab'] ) ? $_GET['tab'] : 'general');

		echo '
		<div class="wrap">
			<h2>' . __( 'Events Maker', 'events-maker' ) . '</h2>
			<h2 class="nav-tab-wrapper">';

		foreach ( $this->tabs as $key => $tab ) {
			echo '
			<a class="nav-tab ' . ($tab_key == $key ? 'nav-tab-active' : '') . '" href="' . esc_url( admin_url( 'edit.php?post_type=event&page=events-settings&tab=' . $key ) ) . '">' . $tab['name'] . '</a>';
		}

		echo '
			</h2>
			<div class="events-maker-settings">
				<div class="df-sidebar">
					<div class="df-credits">
						<h3 class="hndle">' . __( 'Events Maker', 'events-maker' ) . ' ' . Events_Maker()->defaults['version'] . '</h3>
						<div class="inside">
							<h4 class="inner">' . __( 'Need support?', 'events-maker' ) . '</h4>
							<p class="inner">' . __( 'If you are having problems with this plugin, checkout plugin', 'events-maker' ) . '  <a href="http://www.dfactory.eu/docs/events-maker-plugin/?utm_source=events-maker-settings&utm_medium=link&utm_campaign=documentation" target="_blank" title="' . __( 'Documentation', 'events-maker' ) . '">' . __( 'Documentation', 'events-maker' ) . '</a> ' . __( 'or talk about them in the', 'events-maker' ) . ' <a href="http://www.dfactory.eu/support/?utm_source=events-maker-settings&utm_medium=link&utm_campaign=support" target="_blank" title="' . __( 'Support forum', 'events-maker' ) . '">' . __( 'Support forum', 'events-maker' ) . '</a></p>
							<hr />
							<h4 class="inner">' . __( 'Do you like this plugin?', 'events-maker' ) . '</h4>
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" class="inner">
								<input type="hidden" name="cmd" value="_s-xclick">
								<input type="hidden" name="hosted_button_id" value="X53L5RETQ24KQ">
								<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
								<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">
							</form>
							<p class="inner"><a href="http://wordpress.org/support/view/plugin-reviews/events-maker" target="_blank" title="' . __( 'Rate it 5', 'events-maker' ) . '">' . __( 'Rate it 5', 'events-maker' ) . '</a> ' . __( 'on WordPress.org', 'events-maker' ) . '<br />' .
		__( 'Blog about it & link to the', 'events-maker' ) . ' <a href="http://www.dfactory.eu/plugins/events-maker/?utm_source=events-maker-settings&utm_medium=link&utm_campaign=blog-about" target="_blank" title="' . __( 'plugin page', 'events-maker' ) . '">' . __( 'plugin page', 'events-maker' ) . '</a><br />' .
		__( 'Check out our other', 'events-maker' ) . ' <a href="http://www.dfactory.eu/plugins/?utm_source=events-maker-settings&utm_medium=link&utm_campaign=other-plugins" target="_blank" title="' . __( 'WordPress plugins', 'events-maker' ) . '">' . __( 'WordPress plugins', 'events-maker' ) . '</a>
							</p>     
							<hr />
							<p class="df-link inner">' . __( 'Created by', 'events-maker' ) . ' <a href="http://www.dfactory.eu/?utm_source=events-maker-settings&utm_medium=link&utm_campaign=created-by" target="_blank" title="dFactory - Quality plugins for WordPress"><img src="' . EVENTS_MAKER_URL . '/images/logo-dfactory.png' . '" title="dFactory - Quality plugins for WordPress" alt="dFactory - Quality plugins for WordPress" /></a></p>
						</div>
					</div>';
		/*
		  <div class="df-ads">
		  <a href="http://www.dfactory.eu/plugins/frontend-users/?utm_source=events-maker-settings&utm_medium=link&utm_campaign=banner" target="_blank" title="Frontend Users by dFactory"><img src="'.EVENTS_MAKER_URL.'/images/ad-frontend-users.png'.'" alt="Frontend Users by dFactory" /></a>
		  </div>
		 */
		echo '
				</div>
				<form action="options.php" method="post">';

		settings_fields( $this->tabs[$tab_key]['key'] );
		do_settings_sections( $this->tabs[$tab_key]['key'] );

		echo '
					<p class="submit">';

		submit_button( '', 'primary', $this->tabs[$tab_key]['submit'], false );

		echo ' ';

		if ( $this->tabs[$tab_key]['reset'] !== false )
			submit_button( __( 'Reset to defaults', 'events-maker' ), 'secondary', $this->tabs[$tab_key]['reset'], false );

		echo '
					</p>
				</form>
			</div>
			<div class="clear"></div>
		</div>';
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		// general
		register_setting( 'events_maker_general', 'events_maker_general', array( $this, 'validate_general' ) );
		add_settings_section( 'events_maker_general', __( 'General settings', 'events-maker' ), '', 'events_maker_general' );
		add_settings_field( 'em_available_features', __( 'Event features support', 'events-maker' ), array( $this, 'em_available_features' ), 'events_maker_general', 'events_maker_general' );
		add_settings_field( 'em_use_tags', __( 'Tags', 'events-maker' ), array( $this, 'em_use_tags' ), 'events_maker_general', 'events_maker_general' );
		add_settings_field( 'em_use_organizers', __( 'Organizers', 'events-maker' ), array( $this, 'em_use_organizers' ), 'events_maker_general', 'events_maker_general' );
		add_settings_field( 'em_use_event_tickets', __( 'Tickets', 'events-maker' ), array( $this, 'em_use_event_tickets' ), 'events_maker_general', 'events_maker_general' );
		add_settings_field( 'em_ical_feed', __( 'iCal feed/files', 'events-maker' ), array( $this, 'em_ical_feed' ), 'events_maker_general', 'events_maker_general' );
		add_settings_field( 'em_events_in_rss', __( 'RSS feed', 'events-maker' ), array( $this, 'em_events_in_rss' ), 'events_maker_general', 'events_maker_general' );
		add_settings_field( 'em_deactivation_delete', __( 'Deactivation', 'events-maker' ), array( $this, 'em_deactivation_delete' ), 'events_maker_general', 'events_maker_general' );

		// general: currencies
		add_settings_section( 'events_maker_currencies', __( 'Currency settings', 'events-maker' ), '', 'events_maker_general' );
		add_settings_field( 'em_tickets_currency_code', __( 'Currency', 'events-maker' ), array( $this, 'em_tickets_currency_code' ), 'events_maker_general', 'events_maker_currencies' );
		add_settings_field( 'em_tickets_currency_position', __( 'Currency position', 'events-maker' ), array( $this, 'em_tickets_currency_position' ), 'events_maker_general', 'events_maker_currencies' );
		add_settings_field( 'em_tickets_currency_symbol', __( 'Currency symbol', 'events-maker' ), array( $this, 'em_tickets_currency_symbol' ), 'events_maker_general', 'events_maker_currencies' );
		add_settings_field( 'em_tickets_currency_format', __( 'Currency display format', 'events-maker' ), array( $this, 'em_tickets_currency_format' ), 'events_maker_general', 'events_maker_currencies' );

		// general: query
		add_settings_section( 'events_maker_query', __( 'Query settings', 'events-maker' ), '', 'events_maker_general' );
		add_settings_field( 'em_order_by', __( 'Order by', 'events-maker' ), array( $this, 'em_order_by' ), 'events_maker_general', 'events_maker_query' );
		add_settings_field( 'em_order', __( 'Sort order', 'events-maker' ), array( $this, 'em_order' ), 'events_maker_general', 'events_maker_query' );
		add_settings_field( 'em_show_past_events', __( 'Past events', 'events-maker' ), array( $this, 'em_show_past_events' ), 'events_maker_general', 'events_maker_query' );
		add_settings_field( 'em_expire_current', __( 'Current events', 'events-maker' ), array( $this, 'em_expire_current' ), 'events_maker_general', 'events_maker_query' );
		add_settings_field( 'em_show_occurrences', __( 'Occurrences', 'events-maker' ), array( $this, 'em_show_occurrences' ), 'events_maker_general', 'events_maker_query' );

		// display
		register_setting( 'events_maker_display', 'events_maker_general', array( $this, 'validate_general' ) );
		add_settings_section( 'events_maker_pages', __( 'Pages settings', 'events-maker' ), array( $this, 'em_pages_section' ), 'events_maker_display' );
		add_settings_field( 'em_events_page', __( 'Events page', 'events-maker' ), array( $this, 'em_events_page' ), 'events_maker_display', 'events_maker_pages' );
		add_settings_field( 'em_calendar_page', __( 'Calendar page', 'events-maker' ), array( $this, 'em_calendar_page' ), 'events_maker_display', 'events_maker_pages' );
		// add_settings_field('em_past_events_page', __('Past events page', 'events-maker'), array($this, 'em_past_events_page'), 'events_maker_display', 'events_maker_pages');
		add_settings_field( 'em_locations_page', __( 'Locations page', 'events-maker' ), array( $this, 'em_locations_page' ), 'events_maker_display', 'events_maker_pages' );
		add_settings_field( 'em_organizers_page', __( 'Organizers page', 'events-maker' ), array( $this, 'em_organizers_page' ), 'events_maker_display', 'events_maker_pages' );
		add_settings_section( 'events_maker_display', __( 'Display settings', 'events-maker' ), '', 'events_maker_display' );
		add_settings_field( 'em_default_event_options', __( 'Event default options', 'events-maker' ), array( $this, 'em_default_event_options' ), 'events_maker_display', 'events_maker_display' );
		add_settings_field( 'em_date_format', __( 'Date and time format', 'events-maker' ), array( $this, 'em_date_format' ), 'events_maker_display', 'events_maker_display' );
		add_settings_field( 'em_first_weekday', __( 'First day of the week', 'events-maker' ), array( $this, 'em_first_weekday' ), 'events_maker_display', 'events_maker_display' );

		// templates
		register_setting( 'events_maker_templates', 'events_maker_templates', array( $this, 'validate_templates' ) );
		add_settings_section( 'events_maker_templates', __( 'Templates settings', 'events-maker' ), '', 'events_maker_templates' );
		add_settings_field( 'em_default_templates', __( 'Default templates', 'events-maker' ), array( $this, 'em_default_templates' ), 'events_maker_templates', 'events_maker_templates' );
		add_settings_field( 'em_template_archive', __( 'Events archive', 'events-maker' ), array( $this, 'em_template_archive' ), 'events_maker_templates', 'events_maker_templates' );
		add_settings_field( 'em_template_content_archive_event', __( 'Archive event content', 'events-maker' ), array( $this, 'em_template_content_archive_event' ), 'events_maker_templates', 'events_maker_templates' );
		add_settings_field( 'em_template_single', __( 'Single event', 'events-maker' ), array( $this, 'em_template_single' ), 'events_maker_templates', 'events_maker_templates' );
		add_settings_field( 'em_template_content_single_event', __( 'Single event content', 'events-maker' ), array( $this, 'em_template_content_single_event' ), 'events_maker_templates', 'events_maker_templates' );
		add_settings_field( 'em_template_content_widget_event', __( 'Widget event content', 'events-maker' ), array( $this, 'em_template_content_widget_event' ), 'events_maker_templates', 'events_maker_templates' );
		add_settings_field( 'em_template_tax_categories', __( 'Categories', 'events-maker' ), array( $this, 'em_template_tax_categories' ), 'events_maker_templates', 'events_maker_templates' );

		if ( Events_Maker()->options['general']['use_tags'] )
			add_settings_field( 'em_template_tax_tags', __( 'Tags', 'events-maker' ), array( $this, 'em_template_tax_tags' ), 'events_maker_templates', 'events_maker_templates' );

		add_settings_field( 'em_template_tax_locations', __( 'Locations', 'events-maker' ), array( $this, 'em_template_tax_locations' ), 'events_maker_templates', 'events_maker_templates' );

		if ( Events_Maker()->options['general']['use_organizers'] )
			add_settings_field( 'em_template_tax_organizers', __( 'Organizers', 'events-maker' ), array( $this, 'em_template_tax_organizers' ), 'events_maker_templates', 'events_maker_templates' );

		// capabilities
		register_setting( 'events_maker_capabilities', 'events_maker_capabilities', array( $this, 'validate_capabilities' ) );
		add_settings_section( 'events_maker_capabilities', __( 'Capabilities settings', 'events-maker' ), array( $this, 'em_capabilities_table' ), 'events_maker_capabilities' );

		// permalinks
		register_setting( 'events_maker_permalinks', 'events_maker_permalinks', array( $this, 'validate_permalinks' ) );
		add_settings_section( 'events_maker_permalinks', __( 'Permalinks settings', 'events-maker' ), array( $this, 'em_permalinks_desc' ), 'events_maker_permalinks' );
		add_settings_field( 'em_archive_event', __( 'Events base', 'events-maker' ), array( $this, 'em_archive_event' ), 'events_maker_permalinks', 'events_maker_permalinks' );
		add_settings_field( 'em_single_event', __( 'Single event', 'events-maker' ), array( $this, 'em_single_event' ), 'events_maker_permalinks', 'events_maker_permalinks' );
		add_settings_field( 'em_category_event', __( 'Categories', 'events-maker' ), array( $this, 'em_category_event' ), 'events_maker_permalinks', 'events_maker_permalinks' );

		if ( Events_Maker()->options['general']['use_tags'] )
			add_settings_field( 'em_tag_event', __( 'Tags', 'events-maker' ), array( $this, 'em_tag_event' ), 'events_maker_permalinks', 'events_maker_permalinks' );

		add_settings_field( 'em_location_event', __( 'Locations', 'events-maker' ), array( $this, 'em_location_event' ), 'events_maker_permalinks', 'events_maker_permalinks' );

		if ( Events_Maker()->options['general']['use_organizers'] )
			add_settings_field( 'em_organizer_event', __( 'Organizers', 'events-maker' ), array( $this, 'em_organizer_event' ), 'events_maker_permalinks', 'events_maker_permalinks' );

		do_action( 'em_after_register_settings' );
	}

	/**
	 *
	 */
	public function em_pages_section() {
		echo '
			<p class="description">' . __( 'The following pages need to be created or selected so that Events Maker knows where they are. Events page is required, others may be used according to your needs.', 'events-maker' ) . '</span>';
	}

	/**
	 * 
	 */
	public function em_available_features() {
		echo '
		<div id="em_available_features">
			<fieldset>';

		foreach ( $this->supports as $val => $trans ) {
			echo '
				<input id="em-available-feature-' . $val . '" type="checkbox" name="events_maker_general[supports][]" value="' . esc_attr( $val ) . '" ' . checked( true, isset( Events_Maker()->options['general']['supports'][$val] ) ? Events_Maker()->options['general']['supports'][$val] : Events_Maker()->defaults['general']['supports'][$val], false ) . ' /><label for="em-available-feature-' . $val . '">' . $trans . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select which features would you like to enable for your events.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_use_tags() {
		echo '
		<div id="em_use_tags">
			<fieldset>
				<input id="em-use-tags" type="checkbox" name="events_maker_general[use_tags]" ' . checked( Events_Maker()->options['general']['use_tags'], true, false ) . ' /><label for="em-use-tags">' . __( 'Enable Event Tags', 'events-maker' ) . '</label>
				<p class="description">' . __( 'Enable if you want to use Event Tags.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_use_organizers() {
		echo '
		<div id="em_use_organizers">
			<fieldset>
				<input id="em-use-organizers" type="checkbox" name="events_maker_general[use_organizers]" ' . checked( Events_Maker()->options['general']['use_organizers'], true, false ) . ' /><label for="em-use-organizers">' . __( 'Enable Event Organizers', 'events-maker' ) . '</label>
				<p class="description">' . __( 'Enable if you want to use Event Organizers (including organizer contact details).', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_use_event_tickets() {
		echo '
		<div id="em_use_event_tickets">
			<fieldset>
				<input id="em-use-event-tickets" type="checkbox" name="events_maker_general[use_event_tickets]" ' . checked( Events_Maker()->options['general']['use_event_tickets'], true, false ) . ' /><label for="em-use-event-tickets">' . __( 'Enable Event Tickets', 'events-maker' ) . '</label>
				<p class="description">' . __( 'Enable if you want to use Event Tickets (including free events, paid events and multiple ticket types).', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_default_event_options() {
		$options = array(
			'google_map'				 => __( 'Google Map', 'events-maker' ),
			'display_gallery'			 => __( 'Event Gallery', 'events-maker' ),
			'display_location_details'	 => __( 'Location Details', 'events-maker' )
		);
		// if tickets are enabled
		if ( Events_Maker()->options['general']['use_event_tickets'] )
			$options = array_merge( $options, array( 'price_tickets_info' => __( 'Tickets Info', 'events-maker' ) ) );
		// if organizers are enabled
		if ( Events_Maker()->options['general']['use_organizers'] )
			$options = array_merge( $options, array( 'display_organizer_details' => __( 'Organizer Details', 'events-maker' ) ) );

		$options = apply_filters( 'em_default_event_display_options', $options );
		$values = Events_Maker()->options['general']['default_event_options'];

		echo '
		<div id="em_default_event_options">
			<fieldset>';
		foreach ( $options as $key => $name ) {
			?>
			<label for="em_default_event_option_<?php echo $key; ?>">
				<input id="em_default_event_option_<?php echo $key; ?>" type="checkbox" name="events_maker_general[default_event_options][<?php echo $key; ?>]" <?php checked( (isset( $values[$key] ) && $values[$key] !== '' ? $values[$key] : '0' ), '1' ); ?> /><?php echo $name; ?>
			</label><br />
			<?php
		}
		echo '
				<p class="description">' . __( 'Select default display options for single event (this can overriden for each event separately).', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_events_page() {
		echo '
		<div id="em_events_page" class="action-page">
			<fieldset>';

		$selected_id = isset( Events_Maker()->options['general']['pages']['events']['id'] ) ? (int) Events_Maker()->options['general']['pages']['events']['id'] : Events_Maker()->defaults['general']['pages']['events']['id'];

		echo $this->dropdown_pages(
			array(
				'name'		 => 'events_maker_general[pages][events][id]',
				'selected'	 => $selected_id,
				'id'		 => 'em_action_page-events'
			)
		);

		echo '	
				<input type="submit" name="em_create_page[events]" class="button button-primary button-small create-page" value="' . __( 'Create Page', 'events-maker' ) . '"' . ($selected_id != 0 ? ' style="display: none;"' : '') . '>
				
				<p class="description">' . sprintf( __( 'Select or create base page for events archive. That page slug will be automatically set as events base permalink <a href="%s">here</a>.', 'events-maker' ), esc_url( admin_url( 'edit.php?post_type=event&page=events-settings&tab=permalinks' ) ) ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_calendar_page() {
		echo '
		<div id="em_calendar_page" class="action-page">
			<fieldset>';

		$selected_id = isset( Events_Maker()->options['general']['pages']['calendar']['id'] ) ? (int) Events_Maker()->options['general']['pages']['calendar']['id'] : Events_Maker()->defaults['general']['pages']['calendar']['id'];

		echo $this->dropdown_pages(
			array(
				'name'		 => 'events_maker_general[pages][calendar][id]',
				'selected'	 => Events_Maker()->options['general']['pages']['calendar']['id'],
				'id'		 => 'em_action_page-calendar'
			)
		);

		echo '
				<input type="submit" name="em_create_page[calendar]" class="button button-primary button-small create-page" value="' . __( 'Create Page', 'events-maker' ) . '"' . ($selected_id != 0 ? ' style="display: none;"' : '') . '>
				
			<div>';

		// backward compatibility
		if ( isset( Events_Maker()->options['general']['pages']['calendar']['position'] ) )
			$selected = esc_attr( Events_Maker()->options['general']['pages']['calendar']['position'] );
		else {
			if ( isset( Events_Maker()->options['general']['full_calendar_display']['type'] ) && Events_Maker()->options['general']['full_calendar_display']['type'] === 'manual' )
				$selected = 'manual';
			elseif ( isset( Events_Maker()->options['general']['full_calendar_display']['content'] ) )
				$selected = esc_attr( Events_Maker()->options['general']['full_calendar_display']['content'] );
		}

		$selected_position = ! empty( $selected ) ? $selected : Events_Maker()->defaults['general']['pages']['calendar']['position'];

		foreach ( $this->positions as $id => $position ) {
			echo '
					<input id="em_calendar_page_position-' . $id . '" type="radio" name="events_maker_general[pages][calendar][position]" value="' . esc_attr( $id ) . '" ' . checked( $id, $selected_position, false ) . ' /><label for="em_calendar_page_position-' . $id . '">' . $position . '</label>';
		}

		echo '
				</div>
				<p class="description">' . __( 'Select or create page for events calendar display. Use <code>[em-full-calendar]</code> shortcode for manual display.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_past_events_page() {
		echo '
		<div id="em_past_events_page" class="action-page">
			<fieldset>';

		$selected_id = isset( Events_Maker()->options['general']['pages']['past_events']['id'] ) ? (int) Events_Maker()->options['general']['pages']['past_events']['id'] : Events_Maker()->defaults['general']['pages']['past_events']['id'];

		echo $this->dropdown_pages(
			array(
				'depth'		 => 1,
				'name'		 => 'events_maker_general[pages][past_events][id]',
				'selected'	 => $selected_id,
				'id'		 => 'em_action_page-past_events'
			)
		);

		echo '
				<input type="submit" name="em_create_page[past_events]" class="button button-primary button-small create-page" value="' . __( 'Create Page', 'events-maker' ) . '"' . ($selected_id != 0 ? ' style="display: none;"' : '') . '>
				
			<div>';

		$selected_position = isset( Events_Maker()->options['general']['pages']['past_events']['position'] ) ? esc_attr( Events_Maker()->options['general']['pages']['past_events']['position'] ) : Events_Maker()->defaults['general']['pages']['past_events']['position'];

		foreach ( $this->positions as $id => $position ) {
			echo '
					<input id="em_past_events_position-' . $id . '" type="radio" name="events_maker_general[pages][past_events][position]" value="' . esc_attr( $id ) . '" ' . checked( $id, $selected_position, false ) . ' /><label for="em_past_events_position-' . $id . '">' . $position . '</label>';
		}

		echo '
				</div>
				<p class="description">' . __( 'Select or create page for past events display. Use <code>[em-events show_past_events="1" order="DESC" start_before="NOW"]</code> shortcode for manual display.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_locations_page() {
		echo '
		<div id="em_locations_page" class="action-page">
			<fieldset>';

		$selected_id = isset( Events_Maker()->options['general']['pages']['locations']['id'] ) ? (int) Events_Maker()->options['general']['pages']['locations']['id'] : Events_Maker()->defaults['general']['pages']['locations']['id'];

		echo $this->dropdown_pages(
			array(
				'name'		 => 'events_maker_general[pages][locations][id]',
				'selected'	 => $selected_id,
				'id'		 => 'em_action_page-locations'
			)
		);

		echo '
				<input type="submit" name="em_create_page[locations]" class="button button-primary button-small create-page" value="' . __( 'Create Page', 'events-maker' ) . '"' . ($selected_id != 0 ? ' style="display: none;"' : '') . '>
				
			<div>';

		$selected_position = isset( Events_Maker()->options['general']['pages']['locations']['position'] ) ? esc_attr( Events_Maker()->options['general']['pages']['locations']['position'] ) : Events_Maker()->defaults['general']['pages']['locations']['position'];

		foreach ( $this->positions as $id => $position ) {
			echo '
					<input id="em_locations_position-' . $id . '" type="radio" name="events_maker_general[pages][locations][position]" value="' . esc_attr( $id ) . '" ' . checked( $id, $selected_position, false ) . ' /><label for="em_locations_position-' . $id . '">' . $position . '</label>';
		}

		echo '
				</div>
				<p class="description">' . __( 'Select or create page for locations list display. Use <code>[em-locations-list]</code> shortcode for manual display.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_organizers_page() {
		echo '
		<div id="em_organizers_page" class="action-page">
			<fieldset>';

		$selected_id = isset( Events_Maker()->options['general']['pages']['organizers']['id'] ) ? (int) Events_Maker()->options['general']['pages']['organizers']['id'] : Events_Maker()->defaults['general']['pages']['organizers']['id'];

		echo $this->dropdown_pages(
			array(
				'name'		 => 'events_maker_general[pages][organizers][id]',
				'selected'	 => $selected_id,
				'id'		 => 'em_action_page-organizers'
			)
		);

		echo '
				<input type="submit" name="em_create_page[organizers]" class="button button-primary button-small create-page" value="' . __( 'Create Page', 'events-maker' ) . '"' . ($selected_id != 0 ? ' style="display: none;"' : '') . '>
				
			<div>';

		$selected_position = isset( Events_Maker()->options['general']['pages']['organizers']['position'] ) ? esc_attr( Events_Maker()->options['general']['pages']['organizers']['position'] ) : Events_Maker()->defaults['general']['pages']['organizers']['position'];

		foreach ( $this->positions as $id => $position ) {
			echo '
					<input id="em_organizers_position-' . $id . '" type="radio" name="events_maker_general[pages][organizers][position]" value="' . esc_attr( $id ) . '" ' . checked( $id, $selected_position, false ) . ' /><label for="em_organizers_position-' . $id . '">' . $position . '</label>';
		}

		echo '
				</div>
				<p class="description">' . __( 'Select or create page for organizers list display. Use <code>[em-organizers-list]</code> shortcode for manual display.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_ical_feed() {
		$permalinks = get_option( 'permalink_structure' );
		echo '
		<div id="em_ical_feed">
			<fieldset>
				<input id="em-ical-feed" type="checkbox" name="events_maker_general[ical_feed]" ' . checked( Events_Maker()->options['general']['ical_feed'], true, false ) . ' /><label for="em-ical-feed">' . __( 'Enable iCal feed/files', 'events-maker' ) . '</label>
				<p class="description">' . __( 'Enable to generate an iCal feed/files for all your events, categories, tags, locations, organizers and single events. iCal feed/files are accessible under event URL extended with:', 'events-maker' ) . '<code><strong>' . ( ! empty( $permalinks ) ? '/feed/ical' : '&feed=ical') . '</strong></code><br />' .
		__( 'For example:', 'events-maker' ) . ' <code>' . get_post_type_archive_link( 'event' ) . '<strong>' . ( ! empty( $permalinks ) ? 'feed/ical' : '&feed=ical') . '</strong></code></p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_events_in_rss() {
		echo '
		<div id="em_events_in_rss">
			<fieldset>
				<input id="em-events-in-rss" type="checkbox" name="events_maker_general[events_in_rss]" ' . checked( Events_Maker()->options['general']['events_in_rss'], true, false ) . ' /><label for="em-events-in-rss">' . __( 'Enable RSS feed', 'events-maker' ) . '</label>
				<p class="description">' . __( 'Enable to include events in your website main RSS feed.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_deactivation_delete() {
		echo '
		<div id="em_deactivation_delete">
			<fieldset>
				<input id="em-deactivation-delete" type="checkbox" name="events_maker_general[deactivation_delete]" ' . checked( Events_Maker()->options['general']['deactivation_delete'], true, false ) . ' /><label for="em-deactivation-delete">' . __( 'Enable delete on deactivation', 'events-maker' ) . '</label>
				<p class="description">' . __( 'Enable if you want all plugin data to be deleted on deactivation.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_tickets_currency_code() {
		echo '
		<div id="em_tickets_currency_code">
			<fieldset>
				<select id="em-tickets-currency-code" name="events_maker_general[currencies][code]">';

		foreach ( Events_Maker()->localisation->currencies['codes'] as $code => $currency ) {
			echo '
					<option value="' . esc_attr( $code ) . '" ' . selected( $code, strtoupper( Events_Maker()->options['general']['currencies']['code'] ), false ) . '>' . esc_html( $currency ) . ' (' . Events_Maker()->localisation->currencies['symbols'][$code] . ')</option>';
		}

		echo '
				</select>
				<p class="description">' . __( 'Choose the currency that will be used for ticket prices.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_tickets_currency_position() {
		echo '
		<div id="em_tickets_currency_position">
			<fieldset>';

		foreach ( Events_Maker()->localisation->currencies['positions'] as $key => $position ) {
			echo '
				<input id="em-ticket-currency-position-' . $key . '" type="radio" name="events_maker_general[currencies][position]" value="' . esc_attr( $key ) . '" ' . checked( $key, Events_Maker()->options['general']['currencies']['position'], false ) . ' /><label for="em-ticket-currency-position-' . $key . '">' . $position . '</label>';
		}

		echo '
				<p class="description">' . __( 'Choose the location of the currency sign.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_tickets_currency_symbol() {
		echo '
		<div id="em_tickets_currency_symbol">
			<fieldset>
				<input type="text" size="4" name="events_maker_general[currencies][symbol]" value="' . esc_attr( Events_Maker()->options['general']['currencies']['symbol'] ) . '" />
				<p class="description">' . __( 'This will appear next to all the currency figures on the website. Ex. $, USD, €...', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_tickets_currency_format() {
		echo '
		<div id="em_tickets_currency_format">
			<fieldset>
				<select id="em-tickets-currency-format" name="events_maker_general[currencies][format]">';

		foreach ( Events_Maker()->localisation->currencies['formats'] as $code => $format ) {
			echo '
					<option value="' . esc_attr( $code ) . '" ' . selected( $code, Events_Maker()->options['general']['currencies']['format'], false ) . '>' . $format . '</option>';
		}

		echo '
				</select>
				<p class="description">' . __( 'This determines how your currency is displayed. Ex. 1,234.56 or 1,200 or 1200.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_order_by() {
		echo '
		<div id="em_order_by">
			<fieldset>';

		foreach ( $this->orderby_opts as $val => $trans ) {
			echo '
				<input id="em-order-by-' . $val . '" type="radio" name="events_maker_general[order_by]" value="' . esc_attr( $val ) . '" ' . checked( $val, Events_Maker()->options['general']['order_by'], false ) . ' /><label for="em-order-by-' . $val . '">' . $trans . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select how to order your events list (works for both: admin and front-end default query).', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_order() {
		echo '
		<div id="em_order">
			<fieldset>';

		foreach ( $this->order_opts as $val => $trans ) {
			echo '
				<input id="em-order-' . $val . '" type="radio" name="events_maker_general[order]" value="' . esc_attr( $val ) . '" ' . checked( $val, Events_Maker()->options['general']['order'], false ) . ' /><label for="em-order-' . $val . '">' . $trans . '</label>';
		}

		echo '
				<p class="description">' . __( 'Select events list order (works for both: admin and front-end default query).', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_show_past_events() {
		echo '
		<div id="em_show_past_events">
			<fieldset>
				<input id="em-show-ended-events" type="checkbox" name="events_maker_general[show_past_events]" ' . checked( Events_Maker()->options['general']['show_past_events'], true, false ) . ' /><label for="em-show-ended-events">' . __( 'Show past events', 'events-maker' ) . '</label>
				<p class="description">' . __( 'Select whether to include past events in events list (works for front-end default query).', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_expire_current() {
		echo '
		<div id="em_expire_current">
			<fieldset>
				<input id="em-expire-current" type="checkbox" name="events_maker_general[expire_current]" ' . checked( Events_Maker()->options['general']['expire_current'], true, false ) . ' /><label for="em-expire-current">' . __( 'Expire current events', 'events-maker' ) . '</label>
				<p class="description">' . __( 'Select how to handle already started events (works for front-end default query).', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_show_occurrences() {
		echo '
		<div id="em_show_occurrences">
			<fieldset>
				<input id="em-show-occurrences" type="checkbox" name="events_maker_general[show_occurrences]" ' . checked( Events_Maker()->options['general']['show_occurrences'], true, false ) . ' /><label for="em-show-occurrences">' . __( 'Show occurrences', 'events-maker' ) . '</label>
				<p class="description">' . __( 'Select whether to include event occurrences in events list (works for front-end default query).', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_date_format() {
		echo '
		<div id="em_date_format">
			<fieldset>
				<label for="em-date-format">' . __( 'Date', 'events-maker' ) . ':</label> <input id="em-date-format" type="text" name="events_maker_general[datetime_format][date]" value="' . esc_attr( Events_Maker()->options['general']['datetime_format']['date'] ) . '" /> <code>' . date_i18n( Events_Maker()->options['general']['datetime_format']['date'], current_time( 'timestamp' ) ) . '</code>
				<br />
				<label for="em-time-format">' . __( 'Time', 'events-maker' ) . ':</label> <input id="em-time-format" type="text" name="events_maker_general[datetime_format][time]" value="' . esc_attr( Events_Maker()->options['general']['datetime_format']['time'] ) . '" /> <code>' . date( Events_Maker()->options['general']['datetime_format']['time'], current_time( 'timestamp' ) ) . '</code>
				<p class="description">' . __( 'Enter your preffered date and time formatting.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_first_weekday() {
		global $wp_locale;

		echo '
		<div id="em_first_weekday">
			<fieldset>
				<select name="events_maker_general[first_weekday]">
					<option value="1" ' . selected( 1, Events_Maker()->options['general']['first_weekday'], false ) . '>' . $wp_locale->get_weekday( 1 ) . '</option>
					<option value="7" ' . selected( 7, Events_Maker()->options['general']['first_weekday'], false ) . '>' . $wp_locale->get_weekday( 0 ) . '</option>
				</select>
				<p class="description">' . __( 'Select preffered first day of the week for the calendar display.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_default_templates() {
		echo '
		<div id="em_default_templates">
			<fieldset>
				<input id="em-default-templates" type="checkbox" name="events_maker_templates[default_templates]" ' . checked( Events_Maker()->options['templates']['default_templates'], true, false ) . ' /><label for="em-default-templates">' . __( 'Enable to use default templates', 'events-maker' ) . '</label>
				<p class="description">' . __( 'For each of the events pages, the corresponding template is used. To use your own template simply give it the same name and store in your theme folder. By default, if Events Maker can\'t find a template in your theme directory, it will use its own default template. To prevent this, uncheck this option. WordPress will then decide which template from your theme\'s folder to use.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_template_archive() {
		echo '
		<div id="em_template_archive">
			<p class="description">
				<code>templates/<strong>archive-event.php</strong></code>
			</p>
		</div>';
	}

	/**
	 * 
	 */
	public function em_template_content_archive_event() {
		echo '
		<div id="em_template_content_archive_event">
			<p class="description">
				<code>templates/<strong>content-event.php</strong></code>
			</p>
		</div>';
	}

	/**
	 * 
	 */
	public function em_template_single() {
		echo '
		<div id="em_template_single">
			<p class="description">
				<code>templates/<strong>single-event.php</strong></code>
			</p>
		</div>';
	}

	/**
	 * 
	 */
	public function em_template_content_single_event() {
		echo '
		<div id="em_template_content_single_event">
			<p class="description">
				<code>templates/<strong>content-single-event.php</strong></code>
			</p>
		</div>';
	}

	/**
	 * 
	 */
	public function em_template_content_widget_event() {
		echo '
		<div id="em_template_content_widget_event">
			<p class="description">
				<code>templates/<strong>content-widget-event.php</strong></code>
			</p>
		</div>';
	}

	/**
	 * 
	 */
	public function em_template_tax_locations() {
		echo '
		<div id="em_template_tax_locations">
			<p class="description">
				<code>templates/<strong>taxonomy-event-location.php</strong></code>
			</p>
		</div>';
	}

	/**
	 * 
	 */
	public function em_template_tax_categories() {
		echo '
		<div id="em_template_tax_categories">
			<p class="description">
				<code>templates/<strong>taxonomy-event-category.php</strong></code>
			</p>
		</div>';
	}

	/**
	 * 
	 */
	public function em_template_tax_organizers() {
		echo '
		<div id="em_template_tax_organizers">
			<p class="description">
				<code>templates/<strong>taxonomy-event-organizer.php</strong></code>
			</p>
		</div>';
	}

	/**
	 * 
	 */
	public function em_template_tax_tags() {
		echo '
		<div id="em_template_tax_tags">
			<p class="description">
				<code>templates/<strong>taxonomy-event-tag.php</strong></code>
			</p>
		</div>';
	}

	/**
	 * 
	 */
	public function em_permalinks_desc() {
		echo '
		<span class="description">' . __( 'These settings will work only if permalinks are enabled.', 'events-maker' ) . '</span>';
	}

	/**
	 * 
	 */
	public function em_archive_event() {
		echo '
		<div id="em_archive_event">
			<fieldset>
				<input type="text" name="events_maker_permalinks[event_rewrite_base]" value="' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_base'] ) ) . '" />
				<p class="description"><code>' . site_url() . '/<strong>' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_base'] ) ) . '</strong>/</code></p>
				<p class="description">' . sprintf( __( 'General Events root slug to prefix all your events pages with. That permalink will be automatically set as a slug for events page selected <a href="%s">here</a>.', 'events-maker' ), esc_url( admin_url( 'edit.php?post_type=event&page=events-settings&tab=display' ) ) ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_single_event() {
		echo '
		<div id="em_single_event">
			<fieldset>
				<input type="text" name="events_maker_permalinks[event_rewrite_slug]" value="' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_slug'] ) ) . '" />
				<p class="description"><code>' . site_url() . '/<strong>' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_base'] ) ) . '</strong>/<strong>' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_slug'] ) ) . '</strong>/</code></p>
				<p class="description">' . __( 'Single event page slug.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_location_event() {
		echo '
		<div id="em_location_event">
			<fieldset>
				<input type="text" name="events_maker_permalinks[event_locations_rewrite_slug]" value="' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_locations_rewrite_slug'] ) ) . '" />
				<p class="description"><code>' . site_url() . '/<strong>' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_base'] ) ) . '</strong>/<strong>' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_locations_rewrite_slug'] ) ) . '</strong>/</code></p>
				<p class="description">' . __( 'Event Locations page slug.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_category_event() {
		echo '
		<div id="em_category_event">
			<fieldset>
				<input type="text" name="events_maker_permalinks[event_categories_rewrite_slug]" value="' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_categories_rewrite_slug'] ) ) . '" />
				<p class="description"><code>' . site_url() . '/<strong>' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_base'] ) ) . '</strong>/<strong>' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_categories_rewrite_slug'] ) ) . '</strong>/</code></p>
				<p class="description">' . __( 'Event Categories page slug.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_tag_event() {
		echo '
		<div id="em_tag_event">
			<fieldset>
				<input type="text" name="events_maker_permalinks[event_tags_rewrite_slug]" value="' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_tags_rewrite_slug'] ) ) . '" />
				<p class="description"><code>' . site_url() . '/<strong>' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_base'] ) ) . '</strong>/<strong>' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_tags_rewrite_slug'] ) ) . '</strong>/</code></p>
				<p class="description">' . __( 'Event Tags page slug.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_organizer_event() {
		echo '
		<div id="em_organizer_event">
			<fieldset>
				<input type="text" name="events_maker_permalinks[event_organizers_rewrite_slug]" value="' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_organizers_rewrite_slug'] ) ) . '" />
				<p class="description"><code>' . site_url() . '/<strong>' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_base'] ) ) . '</strong>/<strong>' . untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_organizers_rewrite_slug'] ) ) . '</strong>/</code></p>
				<p class="description">' . __( 'Event Organizers page slug.', 'events-maker' ) . '</p>
			</fieldset>
		</div>';
	}

	/**
	 * 
	 */
	public function em_capabilities_table() {
		global $wp_roles;

		$editable_roles = get_editable_roles();

		$html = '
		<table class="widefat fixed posts">
			<thead>
				<tr>
					<th>' . __( 'Role', 'events-maker' ) . '</th>';

		foreach ( $editable_roles as $role_name => $role_info ) {
			$html .= '<th>' . esc_html( (isset( $wp_roles->role_names[$role_name] ) ? translate_user_role( $wp_roles->role_names[$role_name] ) : __( 'None', 'events-maker' ) ) ) . '</th>';
		}

		$html .= '
				</tr>
			</thead>
			<tbody id="the-list">';

		$i = 0;

		foreach ( $this->capabilities as $em_role => $role_display ) {
			$html .= '
				<tr' . (($i ++ % 2 === 0) ? ' class="alternate"' : '') . '>
					<td>' . esc_html( __( $role_display, 'events-maker' ) ) . '</td>';

			foreach ( $editable_roles as $role_name => $role_info ) {
				$role = $wp_roles->get_role( $role_name );
				$html .= '
					<td>
						<input type="checkbox" name="events_maker_capabilities[' . esc_attr( $role->name ) . '][' . esc_attr( $em_role ) . ']" value="1" ' . checked( '1', $role->has_cap( $em_role ), false ) . ' ' . disabled( $role->name, 'administrator', false ) . ' />
					</td>';
			}

			$html .= '
				</tr>';
		}

		$html .= '
			</tbody>
		</table>';

		echo $html;
	}

	/**
	 * Validate capabilities settings.
	 * 
	 * @return 	array
	 */
	public function validate_capabilities( $input ) {
		if ( empty( $_POST['_wpnonce'] ) )
			return '';

		if ( ! current_user_can( 'manage_options' ) )
			return '';

		global $wp_roles;

		if ( isset( $_POST['save_em_capabilities'] ) ) {
			foreach ( $wp_roles->roles as $role_name => $role_text ) {
				$role = $wp_roles->get_role( $role_name );

				if ( ! $role->has_cap( 'manage_options' ) ) {
					foreach ( Events_Maker()->defaults['capabilities'] as $capability ) {
						if ( isset( $input[$role_name][$capability] ) && $input[$role_name][$capability] === '1' )
							$role->add_cap( $capability );
						else
							$role->remove_cap( $capability );
					}
				}
			}

			add_settings_error( 'em_settings_errors', 'settings_caps_saved', $this->errors['settings_caps_saved'], 'updated' );
		}
		elseif ( isset( $_POST['reset_em_capabilities'] ) ) {
			foreach ( $wp_roles->roles as $role_name => $display_name ) {
				$role = $wp_roles->get_role( $role_name );

				foreach ( Events_Maker()->defaults['capabilities'] as $capability ) {
					if ( $role->has_cap( 'manage_options' ) )
						$role->add_cap( $capability );
					else
						$role->remove_cap( $capability );
				}
			}

			add_settings_error( 'em_settings_errors', 'settings_caps_reseted', $this->errors['settings_caps_reseted'], 'updated' );
		}

		return '';
	}

	/**
	 * Validate or reset general settings.
	 * 
	 * @return 	array
	 */
	public function validate_general( $input_old ) {
		if ( empty( $_POST['_wpnonce'] ) )
			return $input_old;

		if ( ! current_user_can( 'manage_options' ) )
			return $input_old;

		if ( isset( $_POST['save_em_general'] ) ) {

			$input = Events_Maker()->options['general'];

			// rewrite rules
			$input['rewrite_rules'] = true;

			// supports
			$supports = array();
			$input_old['supports'] = (isset( $input_old['supports'] ) ? array_flip( $input_old['supports'] ) : null);

			foreach ( $this->supports as $functionality => $label ) {
				$supports[$functionality] = isset( $input_old['supports'][$functionality] );
			}

			$input['supports'] = $supports;

			// currencies
			$input['currencies']['symbol'] = sanitize_text_field( $input_old['currencies']['symbol'] );
			$input['currencies']['code'] = (isset( $input_old['currencies']['code'] ) && in_array( $input_old['currencies']['code'], array_keys( Events_Maker()->localisation->currencies['codes'] ) ) ? strtoupper( $input_old['currencies']['code'] ) : Events_Maker()->defaults['currencies']['code']);
			$input['currencies']['format'] = (isset( $input_old['currencies']['format'] ) && in_array( $input_old['currencies']['format'], array_keys( Events_Maker()->localisation->currencies['formats'] ) ) ? $input_old['currencies']['format'] : Events_Maker()->defaults['currencies']['format']);
			$input['currencies']['position'] = (isset( $input_old['currencies']['position'] ) && in_array( $input_old['currencies']['position'], array_keys( Events_Maker()->localisation->currencies['positions'] ) ) ? $input_old['currencies']['position'] : Events_Maker()->defaults['currencies']['position']);

			// default order
			$input['order_by'] = (isset( $input_old['order_by'] ) && in_array( $input_old['order_by'], array_keys( $this->order_opts ) ) ? $input_old['order_by'] : Events_Maker()->defaults['general']['order_by']);
			$input['order'] = (isset( $input_old['order'] ) && in_array( $input_old['order'], array_keys( $this->order_opts ) ) ? $input_old['order'] : Events_Maker()->defaults['general']['order']);

			// treat current event as expired
			$input['expire_current'] = isset( $input_old['expire_current'] );

			// show past events
			$input['show_past_events'] = isset( $input_old['show_past_events'] );

			// show occurrences
			$input['show_occurrences'] = isset( $input_old['show_occurrences'] );

			// use organizers
			$input['use_organizers'] = isset( $input_old['use_organizers'] );

			// use tags
			$input['use_tags'] = isset( $input_old['use_tags'] );

			// use tickets
			$input['use_event_tickets'] = isset( $input_old['use_event_tickets'] );

			// iCal feed
			$input['ical_feed'] = isset( $input_old['ical_feed'] );

			// RSS feed
			$input['events_in_rss'] = isset( $input_old['events_in_rss'] );

			// deactivation
			$input['deactivation_delete'] = isset( $input_old['deactivation_delete'] );

			add_settings_error( 'em_settings_errors', 'settings_gene_saved', $this->errors['settings_gene_saved'], 'updated' );

		} elseif ( isset( $_POST['reset_em_general'] ) ) {

			$input = Events_Maker()->options['general'];

			// special values
			$input['rewrite_rules'] = true;

			// general defaults settings
			$input['supports'] = Events_Maker()->defaults['general']['supports'];
			$input['currencies'] = Events_Maker()->defaults['general']['currencies'];
			$input['order_by'] = Events_Maker()->defaults['general']['order_by'];
			$input['order'] = Events_Maker()->defaults['general']['order'];
			$input['expire_current'] = Events_Maker()->defaults['general']['expire_current'];
			$input['show_past_events'] = Events_Maker()->defaults['general']['show_past_events'];
			$input['show_occurrences'] = Events_Maker()->defaults['general']['show_occurrences'];
			$input['use_organizers'] = Events_Maker()->defaults['general']['use_organizers'];
			$input['use_tags'] = Events_Maker()->defaults['general']['use_tags'];
			$input['use_event_tickets'] = Events_Maker()->defaults['general']['use_event_tickets'];
			$input['ical_feed'] = Events_Maker()->defaults['general']['ical_feed'];
			$input['events_in_rss'] = Events_Maker()->defaults['general']['events_in_rss'];
			$input['deactivation_delete'] = Events_Maker()->defaults['general']['deactivation_delete'];

			add_settings_error( 'em_settings_errors', 'settings_gene_reseted', $this->errors['settings_gene_reseted'], 'updated' );

		} elseif ( isset( $_POST['save_em_display'] ) ) {

			$input = Events_Maker()->options['general'];

			// rewrite rules
			$input['rewrite_rules'] = true;

			// date, time, weekday
			$input['datetime_format']['date'] = sanitize_text_field( $input_old['datetime_format']['date'] );
			$input['datetime_format']['time'] = sanitize_text_field( $input_old['datetime_format']['time'] );
			$input['first_weekday'] = (in_array( $input_old['first_weekday'], array( 1, 7 ) ) ? (int) $input_old['first_weekday'] : Events_Maker()->defaults['general']['first_weekday']);

			if ( $input['datetime_format']['date'] === '' )
				$input['datetime_format']['date'] = get_option( 'date_format' );

			if ( $input['datetime_format']['time'] === '' )
				$input['datetime_format']['time'] = get_option( 'time_format' );

			// event default options
			$default_event_options = array();

			if ( isset( $input_old['default_event_options'] ) ) {
				foreach ( $input_old['default_event_options'] as $key => $value ) {
					$default_event_options[$key] = (isset( $input_old['default_event_options'][$key] ) ? true : false);
				}
			}

			$input['default_event_options'] = $default_event_options;

			// action pages
			if ( ! empty( Events_Maker()->action_pages ) && is_array( Events_Maker()->action_pages ) ) {
				foreach ( Events_Maker()->action_pages as $key => $label ) {
					$input['pages'][$key]['id'] = (int) (isset( $input_old['pages'][$key]['id'] ) ? $input_old['pages'][$key]['id'] : Events_Maker()->defaults['general']['pages'][$key]['id']);
					$input['pages'][$key]['position'] = esc_attr( isset( $input_old['pages'][$key]['position'] ) ? $input_old['pages'][$key]['position'] : Events_Maker()->defaults['general']['pages'][$key]['position'] );
				}
			}

			$input['pages_notice'] = ! Events_Maker()->admin->is_action_page_set( $input['pages'] );

			add_settings_error( 'em_settings_errors', 'settings_disp_saved', $this->errors['settings_disp_saved'], 'updated' );

		} elseif ( isset( $_POST['reset_em_display'] ) ) {

			$input = Events_Maker()->options['general'];

			// special values
			$input['rewrite_rules'] = true;

			// display defaults settings
			$input['first_weekday'] = Events_Maker()->defaults['general']['first_weekday'];
			$input['default_event_options'] = Events_Maker()->defaults['general']['default_event_options'];
			$input['pages'] = Events_Maker()->defaults['general']['pages'];

			$input['pages_notice'] = ! Events_Maker()->admin->is_action_page_set( $input['pages'] );

			// datetime format
			$input['datetime_format'] = array(
				'date'	 => get_option( 'date_format' ),
				'time'	 => get_option( 'time_format' )
			);

			add_settings_error( 'em_settings_errors', 'settings_disp_reseted', $this->errors['settings_disp_reseted'], 'updated' );

		} else {

			// required for page creation
			if ( isset( $_POST['em_create_page'] ) )
				$input = wp_parse_args( Events_Maker()->options['general'], $input_old );
			else
				$input = $input_old;
		}

		return $input;
	}

	/**
	 * Validate permalinks settings.
	 * 
	 * @return 	array
	 */
	public function validate_permalinks( $input ) {
		if ( empty( $_POST['_wpnonce'] ) )
			return $input;

		if ( ! current_user_can( 'manage_options' ) )
			return $input;

		if ( isset( $_POST['save_em_permalinks'] ) ) {

			// slugs
			$input['event_rewrite_base'] = untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_base'] ) );
			$input['event_rewrite_slug'] = untrailingslashit( esc_html( $input['event_rewrite_slug'] ) );
			$input['event_categories_rewrite_slug'] = untrailingslashit( esc_html( $input['event_categories_rewrite_slug'] ) );
			$input['event_locations_rewrite_slug'] = untrailingslashit( esc_html( $input['event_locations_rewrite_slug'] ) );

			if ( Events_Maker()->options['general']['use_tags'] === true )
				$input['event_tags_rewrite_slug'] = untrailingslashit( esc_html( $input['event_tags_rewrite_slug'] ) );

			if ( Events_Maker()->options['general']['use_organizers'] === true )
				$input['event_organizers_rewrite_slug'] = untrailingslashit( esc_html( $input['event_organizers_rewrite_slug'] ) );

			add_settings_error( 'em_settings_errors', 'settings_perm_saved', $this->errors['settings_perm_saved'], 'updated' );

		} elseif ( isset( $_POST['reset_em_permalinks'] ) ) {

			$input = Events_Maker()->defaults['permalinks'];
			
			add_settings_error( 'em_settings_errors', 'settings_perm_reseted', $this->errors['settings_perm_reseted'], 'updated' );

		}

		return $input;
	}

	/**
	 * Validate or reset templates settings.
	 * 
	 * @return 	array
	 */
	public function validate_templates( $input ) {
		if ( empty( $_POST['_wpnonce'] ) )
			return $input;

		if ( ! current_user_can( 'manage_options' ) )
			return $input;

		if ( isset( $_POST['save_em_templates'] ) ) {
			$input['default_templates'] = (isset( $input['default_templates'] ) ? true : false);

			add_settings_error( 'em_settings_errors', 'settings_temp_saved', $this->errors['settings_temp_saved'], 'updated' );
		} elseif ( isset( $_POST['reset_em_templates'] ) ) {
			$input = Events_Maker()->defaults['templates'];

			add_settings_error( 'em_settings_errors', 'settings_temp_reseted', $this->errors['settings_temp_reseted'], 'updated' );
		}

		return $input;
	}

	/**
	 * Create action page.
	 */
	public function check_action_pages() {
		if ( ! is_admin() )
			return;

		if ( empty( $_POST['_wpnonce'] ) )
			return;

		if ( ! current_user_can( 'manage_options' ) )
			return;

		// create event page
		if ( isset( $_POST['em_create_page'] ) && ! empty( $_POST['em_create_page'] ) && is_array( $_POST['em_create_page'] ) ) {
			// get pages to create
			$pages = array_map( 'sanitize_key', array_keys( $_POST['em_create_page'] ) );

			if ( ! empty( $pages ) ) {
				foreach ( $pages as $page ) {
					if ( in_array( $page, array_keys( Events_Maker()->action_pages ) ) ) {
						// assign post parent, if possible
						if ( $page === 'events' )
							$post_parent = 0;
						else
							$post_parent = ! empty( Events_Maker()->options['general']['pages']['events']['id'] ) ? (int) Events_Maker()->options['general']['pages']['events']['id'] : 0;

						// create new page
						Events_Maker()->options['general']['pages'][$page]['id'] = wp_insert_post(
							array(
							'comment_status' => 'closed',
							'ping_status'	 => 'closed',
							'post_status'	 => 'publish',
							'post_type'		 => 'page',
							'post_title'	 => Events_Maker()->action_pages[$page],
							'post_parent'	 => $post_parent
							), false
						);

						// update event base permalink
						if ( $page === 'events' ) {
							// update events base
							$this->synchronize_permalink( Events_Maker()->options['general']['pages'][$page]['id'] );
						}

						// update message
						if ( Events_Maker()->options['general']['pages'][$page]['id'] === 0 )
							add_settings_error( 'em_settings_errors', 'settings_page_failed', $this->errors['settings_page_failed'], 'error' );
						else
							add_settings_error( 'em_settings_errors', 'settings_page_created', $this->errors['settings_page_created'], 'updated' );
					}
				}

				// display notice?
				Events_Maker()->options['general']['pages_notice'] = ! Events_Maker()->admin->is_action_page_set( Events_Maker()->options['general']['pages'] );

				update_option( 'events_maker_general', Events_Maker()->options['general'] );
			}
		}
		// possible change of event page
		elseif ( isset( $_POST['save_em_display'] ) && isset( $_POST['events_maker_general']['pages']['events']['id'] ) ) {
			$old_id = (int) Events_Maker()->options['general']['pages']['events']['id'];
			$new_id = (int) $_POST['events_maker_general']['pages']['events']['id'];

			// check if there's a change and synchronize
			if ( $new_id !== $old_id )
				$this->synchronize_permalink( $new_id );
		}
		// possible change of event slug
		elseif ( isset( $_POST['save_em_permalinks'] ) && isset( $_POST['events_maker_permalinks']['event_rewrite_base'] ) ) {
			$this->synchronize_page_slug( untrailingslashit( esc_html( $_POST['events_maker_permalinks']['event_rewrite_base'] ) ) );
		}
		// reset display to defaults
		elseif ( isset( $_POST['reset_em_display'] ) ) {
			$this->synchronize_permalink();
		}
		// reset permalinks to defaults
		elseif ( isset( $_POST['reset_em_permalinks'] ) ) {
			$this->synchronize_page_slug();
		}
	}

	/**
	 * Synchronize events base permalink with events page.
	 */
	public function synchronize_permalink( $page_id = 0 ) {
		if ( ! current_user_can( 'manage_options' ) )
			return;

		$page_id = ! empty( $page_id ) ? (int) $page_id : 0;

		// update events base
		Events_Maker()->options['permalinks']['event_rewrite_base'] = $page_id > 0 ? get_page_uri( $page_id ) : Events_Maker()->defaults['permalinks']['event_rewrite_base'];
		update_option( 'events_maker_permalinks', Events_Maker()->options['permalinks'] );

		// set flush rewrite rules on next init
		Events_Maker()->options['general']['rewrite_rules'] = true;
		update_option( 'events_maker_general', Events_Maker()->options['general'] );
	}

	/**
	 * Synchronize events page with events base permalink.
	 */
	public function synchronize_page_slug( $slug = '' ) {
		if ( ! current_user_can( 'manage_options' ) )
			return;

		$slug = ! empty( $slug ) ? untrailingslashit( esc_html( $slug ) ) : '';
		$page = get_post( (int) Events_Maker()->options['general']['pages']['events']['id'] );

		// slug is set, use it
		if ( ! empty( $slug ) ) {
			// page is set, so try to modify it's slug
			if ( ! empty( $page ) ) {
				/* @TODO remove parent uri from slug
				  if($page->post_parent)
				  $slug = str_replace(get_page_uri($page_id), '', $slug);
				 */

				// update page
				$post_id = wp_update_post(
					array(
						'ID'		 => $page->ID,
						'post_name'	 => $slug
					)
				);

				// if slug has changed, we have to synchronize the permalink too
				if ( $post_id )
					$this->synchronize_permalink( $post_id );
			}
		}
		else {
			// no slug, set event page to none	
			Events_Maker()->options['general']['pages']['events']['id'] = 0;
			Events_Maker()->options['general']['pages_notice'] = true;
		}

		// set flush rewrite rules on next init
		Events_Maker()->options['general']['rewrite_rules'] = true;
		update_option( 'events_maker_general', Events_Maker()->options['general'] );
	}

	/**
	 * Synchronize events base permalink on events page update.
	 */
	public function synchronize_save_post( $post_id, $post ) {
		// break, if user has no cap
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		// break, if it's not event page being updated
		if ( $post_id !== (int) Events_Maker()->options['general']['pages']['events']['id'] )
			return $post_id;

		// break, if it is just a revision
		if ( wp_is_post_revision( $post_id ) )
			return $post_id;

		// break, if it's not a page
		if ( $post->post_type != 'page' )
			return $post_id;

		// break, if the post status is not publish
		if ( $post->post_status != 'publish' )
			return $post_id;

		// ok, let's synchronize
		$this->synchronize_permalink( $post_id );
	}

	/**
	 * Get all available pages with default admin language.
	 */
	private function dropdown_pages( $args = array() ) {
		$defaults = array(
			'echo'					=> 0,
			'post_type'				=> 'page',
			'show_option_none'		=> __( 'None', 'events-maker' ),
			'option_none_value'		=> 0,
			'suppress_filters'		=> false
		);
		$args = wp_parse_args( $args, $defaults );

		if ( class_exists( 'SitePress' ) && array_key_exists( 'sitepress', $GLOBALS ) ) {
			global $sitepress;

			$current_lang = $sitepress->get_current_language();
			$default_lang = $sitepress->get_default_language();
			
			// @TODO this only works if $_GET lang parameter is 'all' or equal to $default_lang
			// unfortunatelly WPML does not support any method to override that
			$sitepress->switch_lang( $default_lang, true );
		} elseif ( class_exists( 'Polylang' ) && function_exists( 'pll_default_language' ) ) {
			$args['lang'] = pll_default_language( 'slug' );
		}

		$pages = wp_dropdown_pages( $args );

		if ( class_exists( 'SitePress' ) && array_key_exists( 'sitepress', $GLOBALS ) ) {
			$sitepress->switch_lang( $current_lang );
		}

		return $pages;
	}

}