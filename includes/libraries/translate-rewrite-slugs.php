<?php
/**
 * WPML & Polylang Translate URL Rewrite Slugs
 *
 * Help translate post types and taxonomies rewrite slugs in WPML and Polylang.
 * Heavily based on https://github.com/KLicheR/wp-polylang-translate-rewrite-slugs
 */
 
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'WPML_Translate_Rewrite_Slugs' ) ) {

	class WPML_Translate_Rewrite_Slugs {
	
		// active plugin - WPML or Polylang
		public $plugin;
		// array of languages
		public $langauges;
		// default language
		public $default_lang;
		// array of custom post types
		public $post_types;
		// array of taxonomies
		public $taxonomies;
	
		/**
		 * Contructor.
		 */
		public function __construct() {
			
			$this->languages = array();
			$this->post_types = array();
			$this->taxonomies = array();
	
			// check if WPML or Polylang is active
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
			if ( is_plugin_active( 'polylang/polylang.php' ) ) {
				$this->plugin = 'Polylang';
				add_action( 'init', array( &$this, 'init' ), 20 );
			} elseif ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) && is_plugin_active( 'wpml-string-translation/plugin.php' ) ) {
				$this->plugin = 'WPML';
				add_action( 'init', array( &$this, 'init' ), 20 );
			}
		}
	
		/**
		 * Init action.
		 */
		public function init() {
			
			// languages to handle
			$this->get_languages();
	
			// post types to handle
			require_once( plugin_dir_path( __FILE__ ) . '/translate-post-type.php' );
	
			$post_type_translated_slugs = apply_filters( 'wpml_translated_post_type_rewrite_slugs', array() );
	
			foreach ( $post_type_translated_slugs as $post_type => $translated_slugs ) {
				$this->add_post_type( $post_type, $translated_slugs );
			}
	
			// taxonomies to handle
			require_once( plugin_dir_path( __FILE__ ) . '/translate-taxonomy.php' );
	
			$taxonomy_translated_slugs = apply_filters( 'wpml_translated_taxonomy_rewrite_slugs', array() );
	
			foreach ( $taxonomy_translated_slugs as $taxonomy => $translated_slugs ) {
				$this->add_taxonomy( $taxonomy, $translated_slugs );
			}
	
			// fix "get_permalink" for these post types
			add_filter( 'post_type_link', array( &$this, 'post_type_link_filter' ), 10, 4 );
			// fix "get_post_type_archive_link" for these post types
			add_filter( 'post_type_archive_link', array( &$this, 'post_type_archive_link_filter' ), 25, 2 );
			// fix "get_term_link" for taxonomies
			add_filter( 'term_link', array( &$this, 'term_link_filter' ), 10, 3 );
	
			if ( $this->plugin === 'Polylang' ) {
				// fix "PLL_Frontend_Links->get_translation_url"
				add_filter( 'pll_translation_url', array( &$this, 'pll_translation_url_filter' ), 10, 2 );
				// stop Polylang from translating rewrite rules for these post types
				add_filter( 'pll_rewrite_rules', array( &$this, 'pll_rewrite_rules_filter' ) );
			} elseif ( $this->plugin === 'WPML') {
				add_filter( 'icl_ls_languages', array( &$this, 'icl_ls_languages_filter' ) );
			}
		}
	
		/**
		 * Get available languages.
		 */
		private function get_languages() {
			if ( $this->plugin === 'Polylang' ) {
				global $polylang;
				
				// set default language
				$this->default_lang = pll_default_language();
	
				// set languages
				$registered_languages = $polylang->model->get_languages_list();
				
				if ( ! empty( $registered_languages ) ) {
					foreach ( $registered_languages as $language ) {
						$this->languages[] = $language->slug;
					}
				}
			} elseif ( $this->plugin === 'WPML' ) {
				global $sitepress;
				
				// set default language
				$this->default_lang = $sitepress->get_default_language();
				
				// set languages
				$registered_languages = icl_get_languages();
				
				if ( ! empty( $registered_languages ) ) {
					foreach ( $registered_languages as $language ) {
						$this->languages[] = $language['language_code'];
					}
				}
			}
	
			// remove default language
			  if ( ( $key = array_search( $this->default_lang, $this->languages ) ) !== false )
			  	unset( $this->languages[$key] );
		}
	
		/**
		 * Get post language function.
		 */
		private function get_post_language( $post_id ) {
			$lang = '';
	
			if ( $this->plugin === 'Polylang' ) {
				global $polylang;
	
				$language = $polylang->model->get_post_language( $post_id );
	
				if ( $language ) {
					$lang = $language->slug;
				}
			} elseif ( $this->plugin === 'WPML' ) {
				global $wpdb;
	
				$query = $wpdb->prepare( "SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_type LIKE '%%post_%%' AND element_id = %d", $post_id );
	
				$language = $wpdb->get_row( $query, 'ARRAY_A' );
	
				if ( $language ) {
					$lang = $language['language_code'];
				}
			}
			
			if ( ! $lang )
				$lang = $this->default_lang;
	
			return $lang;
		}
	
		/**
		 * Get term language function.
		 */
		private function get_term_language( $term_id ) {
			$lang = '';
	
			if ( $this->plugin === 'Polylang' ) {
				global $polylang;
	
				$language = $polylang->model->get_term_language( $term_id );
	
				if ( $language ) {
					$lang = $language->slug;
				}
			}
			elseif ( $this->plugin === 'WPML' ) {
				global $wpdb;
	
				$query = $wpdb->prepare( "SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_type LIKE '%%tax_%%' AND element_id = %d", $term_id );
	
				$language = $wpdb->get_row( $query, 'ARRAY_A' );
	
				if ( $language ) {
					$lang = $language['language_code'];
				}
			}
			
			if ( ! $lang )
				$lang = $this->default_lang;
	
			return $lang;
		}
	
		/**
		 * Create a "WPML_Translate_Post_Type" and add it to the handled post type list.
		 */
		public function add_post_type( $post_type, $translated_slugs ) {
			$post_type_object = get_post_type_object( $post_type );
	
			if ( ! is_null( $post_type_object ) ) {
				foreach ( $this->languages as $lang ) {
					// add non specified slug translation to post type default
					if ( ! array_key_exists( $lang, $translated_slugs ) ) {
						$translated_slugs[$lang] = array();
					}
	
					// trim "/" of the slug
					if ( isset( $translated_slugs[$lang]['rewrite']['slug'] ) ) {
						$translated_slugs[$lang]['rewrite']['slug'] = trim( $translated_slugs[$lang]['rewrite']['slug'], '/' );
					}
				}
				$this->post_types[$post_type] = new WPML_Translate_Post_Type( $post_type_object, $translated_slugs, $this->plugin );
			}
		}
	
		/**
		 * Create a "WPML_Translate_Taxonomy" and add it to the handled taxonomy list.
		 */
		public function add_taxonomy( $taxonomy, $translated_slugs ) {
			$taxonomy_object = get_taxonomy( $taxonomy );
	
			if ( ! is_null( $taxonomy_object ) ) {
				$translated_struct = array();
	
				foreach ( $this->languages as $lang ) {
					// add non specified slug translation to taxonomy default.
					if ( ! array_key_exists( $lang, $translated_slugs ) ) {
						$translated_slugs[$lang] = $taxonomy_object->rewrite['slug'];
					}
	
					// trim "/" of the slug
					$translated_slugs[$lang] = trim( $translated_slugs[$lang], '/' );
	
					// generate "struct" with "slug" as WordPress would do
					$translated_struct[$lang] = $translated_slugs[$lang] . "/{$taxonomy_object->name}";
				}
				$this->taxonomies[$taxonomy] = new WPML_Translate_Taxonomy( $taxonomy_object, $translated_slugs, $translated_struct, $this->plugin );
			}
		}
	
		/**
		 * Fix "get_permalink" for this post type.
		 */
		public function post_type_link_filter( $post_link, $post, $leavename, $sample ) {
			// we always check for the post language, otherwise, the current language.
			$lang = $this->get_post_language( $post->ID );
	
			// check if the post type is handle and build URL
			if ( isset( $this->post_types[$post->post_type] ) && get_option( 'permalink_structure' ) ) {
				// WPML
				if ( $this->plugin === 'WPML' ) {
					$options = get_option( 'icl_sitepress_settings' );
					
					// if language name added as parameter
					if ( $options['language_negotiation_type'] == 3 ) {
						$post_link = esc_url( home_url( $this->post_types[$post->post_type]->translated_slugs[$lang]->rewrite['slug'] . '/' . ( $leavename ? "%$post->post_type%" : $post->post_name ) ) );
						if ( $lang != $this->default_lang ) {
							$post_link = esc_url( add_query_arg( 'lang', $lang, $post_link ) );
						}
					} else {
						$post_link = esc_url( icl_get_home_url() . $this->post_types[$post->post_type]->translated_slugs[$lang]->rewrite['slug'] . '/' . ( $leavename ? "%$post->post_type%" : $post->post_name ) );
					}
				// Polylang
				} elseif ( $this->plugin === 'Polylang' ) {
					$post_link = esc_url( home_url( $this->post_types[$post->post_type]->translated_slugs[$lang]->rewrite['slug'] . '/' . ( $leavename ? "%$post->post_type%" : $post->post_name ) ) );
				}
			}
	
			return apply_filters( 'wpml_translated_post_type_link', $post_link );
		}
	
		/**
		 * Fix "get_post_type_archive_link" for this post type.
		 */
		public function post_type_archive_link_filter( $link, $archive_post_type ) {
			if ( $this->plugin === 'Polylang' ) {
				if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
					global $polylang;
					$lang = $polylang->pref_lang->slug;
				} else {
					$lang = ICL_LANGUAGE_CODE;
				}
			} else {
				$lang = ICL_LANGUAGE_CODE;
			}
			
			// check if the post type is handle.
			if ( isset( $this->post_types[$archive_post_type] ) )
				$link = $this->get_post_type_archive_link( $archive_post_type, $lang );
	
			return apply_filters( 'wpml_translated_post_type_archive_link', $link );
		}
	
		/**
		 * Reproduce "get_post_type_archive_link" WordPress function.
		 */
		private function get_post_type_archive_link( $post_type, $lang ) {
	
			global $wp_rewrite;
	
			// if the post type is handle, let the "$this->get_post_type_archive_link" function handle this
			if ( isset( $this->post_types[$post_type] ) ) {
				$translated_slugs = $this->post_types[$post_type]->translated_slugs;
				$translated_slug = $translated_slugs[$lang];
	
				if ( ! $translated_slug->has_archive )
					return false;
	
				// if premalinks are anabled and post type has rewrite
				if ( get_option( 'permalink_structure' ) && is_array( $translated_slug->rewrite ) ) {
					$struct = $translated_slug->has_archive === true ? $translated_slug->rewrite['slug'] : $translated_slug->has_archive;
	
					if ( $this->plugin === 'Polylang' ) {
						global $polylang;				
	
						// if the "URL modifications" is set to "The language is set from the directory name in pretty permalinks"
						// if not ("Hide URL language information for default language" option is set to true and the $lang is the default language.)
						if ( (bool) $polylang->options['force_lang'] === true && ! ( $polylang->options['hide_default'] && $lang == $this->default_lang ) )
							$struct = $lang . '/' . $struct;
						
						// shift the matches up cause "lang" will be the first
						if ( $polylang->options['rewrite'] == 0 && ! ( $polylang->options['hide_default'] && $lang == $this->default_lang ) )
							$struct = 'language/' . $struct;
					}
	
					if ( $translated_slug->rewrite['with_front'] ) {
						$struct = $wp_rewrite->front . $struct;
					} else {
						$struct = $wp_rewrite->root . $struct;
					}

					if ( $this->plugin === 'Polylang' ) {
						$link = esc_url( home_url( user_trailingslashit( $struct, 'post_type_archive' ) ) );
					} elseif ( $this->plugin === 'WPML' ) {
						global $sitepress;
						
						$options = get_option( 'icl_sitepress_settings' );
					
						// if language name added as parameter
						if ( $options['language_negotiation_type'] == 3 ) {
							$link = esc_url( untrailingslashit( $sitepress->convert_url( home_url( user_trailingslashit( $struct, 'post_type_archive' ) ), $lang ) ) );

							if ( $lang === $this->default_lang ) {
								$link = esc_url( remove_query_arg( 'lang', $link ) );
							}

						} else {
							$link = esc_url( untrailingslashit( $sitepress->convert_url( home_url(), $lang ) ) . user_trailingslashit( $struct, 'post_type_archive' ) );
						}
					}

				} else {
					if ( $this->plugin === 'WPML' ) {
						global $sitepress;
						$link = esc_url( $sitepress->language_url( $lang ) . '?post_type=' . $post_type );
					} else {
						$link = esc_url( home_url( '?post_type=' . $post_type ) );
					}
				}
			}
	
			return $link;
		}
	
		/**
		 * Fix "get_term_link" for this taxonomy.
		 */
		public function term_link_filter( $termlink, $term, $taxonomy ) {
			// check if the post type is handle
			if ( isset( $this->taxonomies[$taxonomy] ) ) {
				if ( ! is_object( $term ) ) {
					if ( is_int( $term ) ) {
						$term = get_term( $term, $taxonomy );
					} else {
						$term = get_term_by( 'slug', $term, $taxonomy );
					}
				}
	
				if ( ! is_object( $term ) )
					$term = new WP_Error( 'invalid_term', __( 'Empty Term' ) );
	
				if ( empty( $term ) || is_wp_error( $term ) )
					return $term;
	
				// get the term language
				if ( $this->plugin === 'Polylang' ) {
					$lang = $this->get_term_language( $term->term_id );
				} else {
					$lang = $this->get_term_language( $term->term_taxonomy_id );
				}

				// check if the language is handle
				if ( isset( $this->taxonomies[$taxonomy]->translated_slugs[$lang] ) ) {
					$taxonomy = $term->taxonomy;
	
					$slug = $term->slug;
					$t = get_taxonomy( $taxonomy );

					if ( empty( $termlink ) ) {
						if ( $taxonomy == 'category' ) {
							$termlink = '?cat=' . $term->term_id;
						} elseif ( $t->query_var ) {
							$termlink = "?$t->query_var=$slug";
						} else {
							$termlink = "?taxonomy=$taxonomy&term=$slug";
						}
	
						$termlink = esc_url( icl_get_home_url() . $termlink );
					} else {
						// @TODO: replace default home url with current lang home url?
						if ( $this->plugin === 'Polylang' ) {
							// $termlink = str_replace( untrailingslashit( home_url() ), untrailingslashit( pll_home_url() ), $termlink );
						}
						
						$termlink = esc_url( str_replace( $t->rewrite['slug'], $this->taxonomies[$taxonomy]->translated_slugs[$lang], $termlink ) );
					}
	
					// backward compatibility filters
					if ( $taxonomy == 'post_tag' ) {
						$termlink = apply_filters( 'tag_link', $termlink, $term->term_id );
					} elseif ( $taxonomy == 'category' ) {
						$termlink = apply_filters( 'category_link', $termlink, $term->term_id );
					}
				}
			}
	
			return apply_filters( 'wpml_translated_term_link', $termlink );
		}
		
		/**
		 * Fix WPML language switcher urls
		 */
		public function icl_ls_languages_filter( $languages ) {
			if ( is_archive() ) {
				$post_type = get_query_var('post_type');
	
				// if the post type is handle, let the "$this->get_post_type_archive_link" function handle this
				if ( isset( $this->post_types[$post_type] ) ) {
					foreach ( $languages as $k => $language ) {
						$languages[$k]['url'] = $this->get_post_type_archive_link( $post_type, $language['language_code'] );
					}
				}
			}
			return $languages;
		}
	
		/**
		 * Fix "PLL_Frontend_Links->get_translation_url()".
		 */
		public function pll_translation_url_filter( $url, $lang ) {
			global $wp_query, $polylang;
	
			if ( is_category() ) {
				$term = get_category_by_slug( $wp_query->get( 'category_name' ) );
				$translated_term = get_term( pll_get_term( $term->term_id, $lang ), $term->taxonomy );
	
				return esc_url( home_url( '/' . $lang . '/' . $translated_term->slug ) );
			} elseif ( is_archive() ) {
				$post_type = $wp_query->query_vars['post_type'];
	
				// if the post type is handle, let the "$this->get_post_type_archive_link" function handle this
				if ( isset( $this->post_types[$post_type] ) )
					return $this->get_post_type_archive_link( $post_type, $lang );
			}
	
			return $url;
		}
	
		/**
		 * Stop Polylang from translating rewrite rules for these post types.
		 */
		public function pll_rewrite_rules_filter( $rules ) {
			// we don't want Polylang to take care of these rewrite rules groups.
			foreach ( array_keys( $this->post_types ) as $post_type ) {
				$rule_key = array_search( $post_type, $rules );
	
				if ( $rule_key )
					unset( $rules[$rule_key] );
			}
			foreach ( array_keys( $this->taxonomies ) as $taxonomy ) {
				$rule_key = array_search( $taxonomy, $rules );
	
				if ( $rule_key )
					unset( $rules[$rule_key] );
			}
	
			return $rules;
		}
	
	}
	
	new WPML_Translate_Rewrite_Slugs();

}