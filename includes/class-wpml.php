<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

new Events_Maker_WPML();

/**
 * Events_Maker_Templates Class.
 */
class Events_Maker_WPML {

	private $post_type_slugs = array();
	private $taxonomy_slugs = array();

	public function __construct() {
		// set instance
		Events_Maker()->wpml = $this;
		
		// actions
		add_action( 'init', array( &$this, 'set_translated_slugs' ) );
		add_action( 'plugins_loaded', array( &$this, 'register_strings' ) );
		add_action( 'wpml_translated_post_type_replace_rewrite_rules', array (&$this, 'register_extra_rewrite_rules' ) , 10, 3 );

		// filters
		add_filter( 'wpml_translated_post_type_rewrite_slugs', array( &$this, 'register_translated_post_type_slugs' ) );
		add_filter( 'wpml_translated_taxonomy_rewrite_slugs', array( &$this, 'register_translated_taxonomy_slugs' ) );
	}
	
	/**
	 * Register strings for translation.
	 */
	public function register_strings() {
		if ( ! is_admin() )
			return;
		
		// WPML and Polylang
		if ( function_exists( 'icl_register_string' ) ) {
			icl_register_string( 'Events Maker', 'Event rewrite base', Events_Maker()->options['permalinks']['event_rewrite_base'] );
			icl_register_string( 'Events Maker', 'Event rewrite slug', Events_Maker()->options['permalinks']['event_rewrite_slug'] );
			icl_register_string( 'Events Maker', 'Event Categories rewrite slug', Events_Maker()->options['permalinks']['event_categories_rewrite_slug'] );
			icl_register_string( 'Events Maker', 'Event Locations rewrite slug', Events_Maker()->options['permalinks']['event_locations_rewrite_slug'] );
			if ( Events_Maker()->options['general']['use_tags'] === true )
				icl_register_string( 'Events Maker', 'Event Tags rewrite slug', Events_Maker()->options['permalinks']['event_tags_rewrite_slug'] );
			if ( Events_Maker()->options['general']['use_organizers'] === true )
				icl_register_string( 'Events Maker', 'Event Organizers rewrite slug', Events_Maker()->options['permalinks']['event_organizers_rewrite_slug'] );
		}
	}
	
	/**
	 * Register translated post type slugs.
	 */
	public function register_extra_rewrite_rules($post_type, $lang, $translated_slug) {
		global $wp_rewrite;
		
		$archive_slug = $translated_slug->has_archive === true ? $translated_slug->rewrite['slug'] : $translated_slug->has_archive;

		add_rewrite_rule( "{$archive_slug}/([0-9]{4}(?:/[0-9]{2}(?:/[0-9]{2})?)?)/?$", "index.php?post_type=$post_type" . '&event_ondate=$matches[1]', 'top' );

		if ( $translated_slug->rewrite['pages'] )
			add_rewrite_rule( "{$archive_slug}/([0-9]{4}(?:/[0-9]{2}(?:/[0-9]{2})?)?)/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$post_type" . '&event_ondate=$matches[1]' . '&paged=$matches[2]', 'top' );
	}

	/**
	 * Register translated post type slugs.
	 */
	public function register_translated_post_type_slugs() {
		return $this->post_type_slugs;
	}

	/**
	 * Register translated post type slugs.
	 */
	public function register_translated_taxonomy_slugs() {
		return $this->taxonomy_slugs;
	}

	/**
	 * Get post type and taxonomy slugs.
	 */
	public function set_translated_slugs() {
		$plugin = '';

		// check if WPML or Polylang is active
		include_once(ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( is_plugin_active( 'polylang/polylang.php' ) ) {
			global $polylang;

			$plugin = 'Polylang';
		} elseif ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) && is_plugin_active( 'wpml-string-translation/plugin.php' ) ) {
			global $sitepress;

			$plugin = 'WPML';
		}

		if ( empty( $plugin ) )
			return;

		$languages = array();
		$default = '';

		// Polylang
		if ( $plugin === 'Polylang' ) {
			// get registered languages
			$registered_languages = $polylang->model->get_languages_list();

			if ( ! empty( $registered_languages ) ) {
				foreach ( $registered_languages as $language )
					$languages[] = $language->slug;
			}

			// get default language
			$default = pll_default_language();
		}
		// WPML
		else {
			// get registered languages
			$registered_languages = icl_get_languages();

			if ( ! empty( $registered_languages ) ) {
				foreach ( $registered_languages as $language )
					$languages[] = $language['language_code'];
			}

			// get default language
			$default = $sitepress->get_default_language();
			$current = $sitepress->get_current_language();
		}

		if ( ! empty( $languages ) ) {
			foreach ( $languages as $language ) {
				$slugs = array();

				if ( $plugin === 'Polylang' ) {
					
					// get language strings
					$slugs['event_rewrite_base'] = pll_translate_string( untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_base'] ) ), $language );
					$slugs['event_rewrite_slug'] = pll_translate_string( untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_slug'] ) ), $language );
					$slugs['event_categories_rewrite_slug'] = pll_translate_string( untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_categories_rewrite_slug'] ) ), $language );
					$slugs['event_locations_rewrite_slug'] = pll_translate_string( untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_locations_rewrite_slug'] ) ), $language );

					if ( Events_Maker()->options['general']['use_tags'] === true )
						$slugs['event_tags_rewrite_slug'] = pll_translate_string( untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_tags_rewrite_slug'] ) ), $language );

					if ( Events_Maker()->options['general']['use_organizers'] === true )
						$slugs['event_organizers_rewrite_slug'] = pll_translate_string( untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_organizers_rewrite_slug'] ) ), $language );
					
				} elseif ( $plugin === 'WPML' ) {
					
					$sitepress->switch_lang( $language, true );

					$slugs['event_rewrite_base'] = icl_t( 'Events Maker', 'Event rewrite base', untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_base'] ) ), $has_translation = null, false, $language );
					$slugs['event_rewrite_slug'] = icl_t( 'Events Maker', 'Event rewrite slug', untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_rewrite_slug'] ) ), $has_translation = null, false, $language );
					$slugs['event_categories_rewrite_slug'] = icl_t( 'Events Maker', 'Event Categories rewrite slug', untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_categories_rewrite_slug'] ) ), $has_translation = null, false, $language );
					$slugs['event_locations_rewrite_slug'] = icl_t( 'Events Maker', 'Event Locations rewrite slug', untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_locations_rewrite_slug'] ) ), $has_translation = null, false, $language );

					if ( Events_Maker()->options['general']['use_tags'] === true )
						$slugs['event_tags_rewrite_slug'] = icl_t( 'Events Maker', 'Event Tags rewrite slug', untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_tags_rewrite_slug'] ) ), $has_translation = null, false, $language );

					if ( Events_Maker()->options['general']['use_organizers'] === true )
						$slugs['event_organizers_rewrite_slug'] = icl_t( 'Events Maker', 'Event Organizers rewrite slug', untrailingslashit( esc_html( Events_Maker()->options['permalinks']['event_organizers_rewrite_slug'] ) ), $has_translation = null, false, $language );
						
				}

				$slugs = apply_filters( 'em_translated_taxonomy_rewrite_slugs_' . $language, $slugs );

				// set translated post type slugs
				$this->post_type_slugs['event'][$language] = array(
					'has_archive'	 => $slugs['event_rewrite_base'],
					'rewrite'		 => array(
						'slug' => $slugs['event_rewrite_base'] . '/' . $slugs['event_rewrite_slug'],
					),
				);

				// set translated taxonomy slugs
				$this->taxonomy_slugs['event-category'][$language] = $slugs['event_rewrite_base'] . '/' . $slugs['event_categories_rewrite_slug'];
				$this->taxonomy_slugs['event-location'][$language] = $slugs['event_rewrite_base'] . '/' . $slugs['event_locations_rewrite_slug'];
				if ( Events_Maker()->options['general']['use_tags'] === true )
					$this->taxonomy_slugs['event-tag'][$language] = $slugs['event_rewrite_base'] . '/' . $slugs['event_tags_rewrite_slug'];
				if ( Events_Maker()->options['general']['use_organizers'] === true )
					$this->taxonomy_slugs['event-organizer'][$language] = $slugs['event_rewrite_base'] . '/' . $slugs['event_organizers_rewrite_slug'];
			}
			// switch back to current language
			if ( $plugin === 'WPML' )
				$sitepress->switch_lang( $current, true );
		}
	}

}

?>