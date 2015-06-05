<?php
/**
 * Post type related object.
 *
 */
 
if ( ! defined( 'ABSPATH' ) )
	exit;
 
class WPML_Translate_Post_Type {

	// active plugin - WPML or Polylang
	public $plugin;
	// post type object.
	public $post_type_object;
	// translated rewrite slugs.
	public $translated_slugs;

	/**
	 * Contructor.
	 */
	public function __construct( $post_type_object, $translated_slugs, $plugin ) {
		$this->plugin = $plugin;
		$this->post_type_object = $post_type_object;
		$this->translated_slugs = $this->sanitize_translated_slugs( $translated_slugs );

		// replace "extra_rules_top", for archive
		$this->replace_extra_rules_top();
		// replace "permastruct", for single
		$this->replace_permastruct();
	}

	private function sanitize_translated_slugs( $translated_slugs ) {
		$post_type = $this->post_type_object->name;

		// add defaults to translated_slugs
		$defaults = array(
			'has_archive'	 => false,
			'rewrite'		 => true,
		);

		foreach ( $translated_slugs as $lang => $translated_slug ) {
			$args = wp_parse_args( $translated_slug, $defaults );
			$args = (object) $args;

			if ( $args->rewrite !== false && (is_admin() || get_option( 'permalink_structure' ) != '') ) {
				if ( ! is_array( $args->rewrite ) )
					$args->rewrite = array();
				if ( empty( $args->rewrite['slug'] ) )
					$args->rewrite['slug'] = $post_type;
				if ( ! isset( $args->rewrite['with_front'] ) )
					$args->rewrite['with_front'] = true;
				if ( ! isset( $args->rewrite['pages'] ) )
					$args->rewrite['pages'] = true;
				if ( ! isset( $args->rewrite['feeds'] ) || ! $args->has_archive )
					$args->rewrite['feeds'] = (bool) $args->has_archive;
				if ( ! isset( $args->rewrite['ep_mask'] ) ) {
					if ( isset( $args->permalink_epmask ) ) {
						$args->rewrite['ep_mask'] = $args->permalink_epmask;
					} else {
						$args->rewrite['ep_mask'] = EP_PERMALINK;
					}
				}
			}

			$translated_slugs[$lang] = $args;
		}

		return $translated_slugs;
	}

	/**
	 * Replace "extra_rules_top", for archive.
	 *
	 * This code simulate the code used in WordPress function "register_post_type"
	 * and execute it for each language. After that, Polylang will consider these
	 * rules like "individual" post types (one by lang) and will create the appropriated
	 * rules.
	 */
	private function replace_extra_rules_top() {
		global $wp_rewrite;

		$post_type = $this->post_type_object->name;

		// remove the original extra rules
		if ( $this->post_type_object->has_archive ) {
			$archive_slug = $this->post_type_object->has_archive === true ? $this->post_type_object->rewrite['slug'] : $this->post_type_object->has_archive;
			
			if ( $this->post_type_object->rewrite['with_front'] ) {
				$archive_slug = substr( $wp_rewrite->front, 1 ) . $archive_slug;
			} else {
				$archive_slug = $wp_rewrite->root . $archive_slug;
			}

			unset( $wp_rewrite->extra_rules_top["{$archive_slug}/?$"] );

			if ( $this->post_type_object->rewrite['feeds'] && $wp_rewrite->feeds ) {
				$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
				unset( $wp_rewrite->extra_rules_top["{$archive_slug}/feed/$feeds/?$"] );
				unset( $wp_rewrite->extra_rules_top["{$archive_slug}/$feeds/?$"] );
			}

			if ( $this->post_type_object->rewrite['pages'] )
				unset( $wp_rewrite->extra_rules_top["{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$"] );
		}

		// add the translated extra rules for each languages
		foreach ( $this->translated_slugs as $lang => $translated_slug ) {
			if ( $translated_slug->has_archive && get_option( 'permalink_structure' ) != '' ) {
				$archive_slug = $translated_slug->has_archive === true ? $translated_slug->rewrite['slug'] : $translated_slug->has_archive;

				if ( $translated_slug->rewrite['with_front'] ) {
					$archive_slug = substr( $wp_rewrite->front, 1 ) . $archive_slug;
				} else {
					$archive_slug = $wp_rewrite->root . $archive_slug;
				}

				$force_lang = true;
				
				if ( $this->plugin === 'Polylang' ) {
					global $polylang;
					
					// if "The language is set from content" is enabled
					$force_lang = (bool) $polylang->options['force_lang'];
				}
					
				if ( $force_lang === false ) {
					add_rewrite_rule( "{$archive_slug}/?$", "index.php?post_type=$post_type&lang={$lang}", 'top' );
	
					if ( $translated_slug->rewrite['feeds'] && $wp_rewrite->feeds ) {
						$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
						add_rewrite_rule( "{$archive_slug}/feed/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]&lang={$lang}', 'top' );
						add_rewrite_rule( "{$archive_slug}/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]&lang={$lang}', 'top' );
					}
	
					if ( $translated_slug->rewrite['pages'] )
						add_rewrite_rule( "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$post_type" . '&paged=$matches[1]&lang={$lang}', 'top' );
				} else {
					add_rewrite_rule( "{$archive_slug}/?$", "index.php?post_type=$post_type", 'top' );
	
					if ( $translated_slug->rewrite['feeds'] && $wp_rewrite->feeds ) {
						$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
						add_rewrite_rule( "{$archive_slug}/feed/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
						add_rewrite_rule( "{$archive_slug}/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
					}
	
					if ( $translated_slug->rewrite['pages'] )
						add_rewrite_rule( "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$post_type" . '&paged=$matches[1]', 'top' );
				}
				
				do_action('wpml_translated_post_type_replace_rewrite_rules', $post_type, $lang, $translated_slug);
			}
		}
	}

	/**
	 * Replace "permastruct", for single.
	 *
	 * This code simulate the code used in WordPress function "register_post_type"
	 * and execute it for each language.
	 */
	private function replace_permastruct() {
		global $wp_rewrite;

		$post_type = $this->post_type_object->name;

		// remove the original permastructs
		unset( $wp_rewrite->extra_permastructs[$post_type] );

		// add the translated permastructs for each languages
		foreach ( $this->translated_slugs as $lang => $translated_slug ) {
			$args = $translated_slug;

			if ( $args->rewrite !== false && (is_admin() || get_option( 'permalink_structure' ) != '') ) {
				$permastruct_args = $args->rewrite;
				$permastruct_args['feed'] = $permastruct_args['feeds'];
				// set the walk_dirs to false to avoid conflict with has_archive = false and the %language%
				// in the rewrite directive. Without it the archive page redirect to the frontpage if has_archive is false
				$permastruct_args['walk_dirs'] = false;

				// if "Hide URL language information for default language" option is
				// set to true the rules has to be different for the default language
				if ( $this->plugin === 'Polylang' ) {
					global $polylang;

					// if "The language is set from content" is enabled
					if ( (bool) $polylang->options['force_lang'] === false ) {
						add_permastruct( $post_type . '_' . $lang, "{$args->rewrite['slug']}/%$post_type%", $permastruct_args );
					} else {
						if ( $polylang->options['hide_default'] && $lang == pll_default_language() ) {
							add_permastruct( $post_type . '_' . $lang, "{$args->rewrite['slug']}/%$post_type%", $permastruct_args );
						} else {
							// if "Keep /language/ in pretty permalinks" is enabled
							if ( $polylang->options['rewrite'] == 0 && ! ( $polylang->options['hide_default'] && $lang == pll_default_language() ) ) {
								add_permastruct( $post_type . '_' . $lang, 'language/' . "%language%/{$args->rewrite['slug']}/%$post_type%", $permastruct_args );
							} else {
								add_permastruct( $post_type . '_' . $lang, "%language%/{$args->rewrite['slug']}/%$post_type%", $permastruct_args );
							}
						}
					}
				} elseif ( $this->plugin === 'WPML' ) {
					add_permastruct( $post_type . '_' . $lang, "{$args->rewrite['slug']}/%$post_type%", $permastruct_args );
				}
				
				do_action('wpml_translated_post_type_replace_permastruct', $post_type, $lang, $translated_slug);
			}
		}
	}

}
