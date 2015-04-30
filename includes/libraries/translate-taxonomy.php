<?php
/**
 * Taxonomy related object.
 */
 
if ( ! defined( 'ABSPATH' ) )
	exit;
 
class WPML_Translate_Taxonomy {
	// active plugin - WPML or Polylang
	public $plugin;
	// post type object
	public $taxonomy_object;
	// translated rewrite slugs
	public $translated_slugs;
	// translated object struct
	public $translated_struct;

	/**
	 * Contructor.
	 */
	public function __construct( $taxonomy_object, $translated_slugs, $translated_struct, $plugin ) {
		$this->plugin = $plugin;
		$this->taxonomy_object = $taxonomy_object;
		$this->translated_slugs = $translated_slugs;
		$this->translated_struct = $translated_struct;

		// translate the rewrite rules of the post type
		add_filter( $this->taxonomy_object->name . '_rewrite_rules', array( $this, 'taxonomy_rewrite_rules_filter' ) );
	}

	/**
	 * Translate the rewrite rules.
	 */
	public function taxonomy_rewrite_rules_filter( $rewrite_rules ) {
		global $wp_rewrite;

		$translated_rules = array();

		// for each language
		foreach ( $this->translated_slugs as $lang => $translated_slug ) {
			
			$is_default = false;
			$force_lang = true;
			
			if ( $this->plugin === 'Polylang' ) {
				global $polylang;

				// if "Hide URL language information for default language" option is set to true the rules has to be different for the default language.
				if ( $polylang->options['hide_default'] && $lang == pll_default_language() ) {
					$is_default = true;
				}
				
				// if "The language is set from content" is enabled
				$force_lang = (bool) $polylang->options['force_lang'];
			} elseif ( $this->plugin === 'WPML' ) {
				global $sitepress;

				if ( $lang == $sitepress->get_default_language() ) {
					$is_default = true;
				}
			}
			
			if ( $force_lang === false ) {
				// for each rule
				foreach ( $rewrite_rules as $rule_key => $rule_value ) {
					// only translate the rewrite slug
					$translated_rules[str_replace( trim( $this->taxonomy_object->rewrite['slug'], '/' ), $translated_slug, $rule_key )] = $rule_value;
				}
			} else {
				// default language?
				if ( $is_default === true ) {
					// for each rule
					foreach ( $rewrite_rules as $rule_key => $rule_value ) {
						// only translate the rewrite slug
						$translated_rules[str_replace( trim( $this->taxonomy_object->rewrite['slug'], '/' ), $translated_slug, $rule_key )] = $rule_value;
					}
				} else {
					// for each rule
					foreach ( $rewrite_rules as $rule_key => $rule_value ) {
						$taxonomy_rewrite_slug = $this->taxonomy_object->rewrite['slug'];
	
						// replace the rewrite tags in slugs
						foreach ( $wp_rewrite->rewritecode as $position => $code ) {
							$taxonomy_rewrite_slug = str_replace( $code, $wp_rewrite->rewritereplace[$position], $taxonomy_rewrite_slug );
							$translated_slug = str_replace( $code, $wp_rewrite->rewritereplace[$position], $translated_slug );
						}
	
						if ( $this->plugin === 'Polylang' ) {
							global $polylang;
							
							// shift the matches up cause "lang" will be the first
							if ( $polylang->options['rewrite'] == 0 && ! ( $polylang->options['hide_default'] && $lang == $this->default_lang ) ) {
								// if "Keep /language/ in pretty permalinks" is enabled
								$translated_rules['language/' . '(' . $lang . ')/' . str_replace( trim( $taxonomy_rewrite_slug, '/' ), $translated_slug, $rule_key )] = str_replace(
									array( '[8]', '[7]', '[6]', '[5]', '[4]', '[3]', '[2]', '[1]' ), array( '[9]', '[8]', '[7]', '[6]', '[5]', '[4]', '[3]', '[2]' ), $rule_value
								);
							} else {
								$translated_rules['(' . $lang . ')/' . str_replace( trim( $taxonomy_rewrite_slug, '/' ), $translated_slug, $rule_key )] = str_replace(
									array( '[8]', '[7]', '[6]', '[5]', '[4]', '[3]', '[2]', '[1]' ), array( '[9]', '[8]', '[7]', '[6]', '[5]', '[4]', '[3]', '[2]' ), $rule_value
								);
							}
						} else {
							// only translate the rewrite slug
							$translated_rules[str_replace( trim( $this->taxonomy_object->rewrite['slug'], '/' ), $translated_slug, $rule_key )] = $rule_value;
						}
					}
				}
			}
		}

		return $translated_rules;
	}

}
