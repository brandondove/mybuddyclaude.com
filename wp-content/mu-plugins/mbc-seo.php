<?php
/**
 * Plugin Name: MBC SEO
 * Description: Outputs meta description, Open Graph tags, Twitter Card tags, and JSON-LD structured data.
 * Version: 1.0.0
 * Author: My Buddy Claude
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// =========================================================================
// DATA GATHERING
// =========================================================================

/**
 * Build the shared SEO context array for the current page.
 *
 * @return array{title:string,description:string,url:string,image_url:string,image_width:int,image_height:int,image_alt:string,og_type:string,site_name:string,post:WP_Post|null}
 */
function mbc_seo_get_context(): array {
	$ctx = array(
		'title'        => '',
		'description'  => '',
		'url'          => '',
		'image_url'    => '',
		'image_width'  => 0,
		'image_height' => 0,
		'image_alt'    => '',
		'og_type'      => 'website',
		'site_name'    => get_bloginfo( 'name' ),
		'post'         => null,
	);

	if ( is_front_page() ) {
		$ctx['title']       = $ctx['site_name'];
		$ctx['description'] = get_bloginfo( 'description' );
		$ctx['url']         = home_url( '/' );
		return $ctx;
	}

	if ( is_singular() ) {
		$post               = get_queried_object();
		$ctx['post']        = $post;
		$ctx['title']       = get_the_title( $post );
		$ctx['description'] = mbc_seo_get_description_for_post( $post );
		$ctx['url']         = get_permalink( $post );

		if ( is_singular( 'post' ) || is_singular( 'mbc_tool' ) ) {
			$ctx['og_type'] = 'article';
		}

		$thumbnail_id = get_post_thumbnail_id( $post );
		if ( $thumbnail_id ) {
			$image_url = wp_get_attachment_image_url( (int) $thumbnail_id, 'full' );
			if ( $image_url ) {
				$ctx['image_url'] = $image_url;
				$image_meta       = wp_get_attachment_metadata( (int) $thumbnail_id );
				if ( is_array( $image_meta ) ) {
					$ctx['image_width']  = (int) ( $image_meta['width'] ?? 0 );
					$ctx['image_height'] = (int) ( $image_meta['height'] ?? 0 );
				}
				$alt = get_post_meta( (int) $thumbnail_id, '_wp_attachment_image_alt', true );
				$ctx['image_alt'] = is_string( $alt ) && $alt !== '' ? $alt : $ctx['title'];
			}
		}

		return $ctx;
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term               = get_queried_object();
		$ctx['title']       = single_term_title( '', false );
		$ctx['description'] = mbc_seo_get_archive_description();
		$link               = get_term_link( $term );
		$ctx['url']         = is_string( $link ) ? $link : home_url( '/' );
		return $ctx;
	}

	if ( is_post_type_archive( 'mbc_tool' ) ) {
		$ctx['title']       = post_type_archive_title( '', false );
		$ctx['description'] = 'A curated showcase of tools built through human-AI collaboration.';
		$ctx['url']         = get_post_type_archive_link( 'mbc_tool' ) ?: home_url( '/' );
		return $ctx;
	}

	// Fallback for other pages (search, 404, date archives).
	$ctx['title']       = $ctx['site_name'];
	$ctx['description'] = get_bloginfo( 'description' );
	$ctx['url']         = home_url( '/' );
	return $ctx;
}

/**
 * Derive a meta description from a post's excerpt or content.
 */
function mbc_seo_get_description_for_post( WP_Post $post ): string {
	if ( $post->post_excerpt !== '' ) {
		return wp_strip_all_tags( $post->post_excerpt );
	}

	$content = $post->post_content;
	if ( $content === '' ) {
		return '';
	}

	// Strip block markup comments.
	$content = preg_replace( '/<!--(.|\s)*?-->/', '', $content );
	$content = wp_strip_all_tags( (string) $content );
	$content = trim( $content );

	if ( $content === '' ) {
		return '';
	}

	$trimmed = wp_trim_words( $content, 25, '...' );

	// Guard against unusually long words producing > 160 chars.
	if ( mb_strlen( $trimmed ) > 160 ) {
		$trimmed = mb_substr( $trimmed, 0, 155 ) . '...';
	}

	return $trimmed;
}

/**
 * Derive a meta description for taxonomy archive pages.
 */
function mbc_seo_get_archive_description(): string {
	$term = get_queried_object();

	if ( $term instanceof WP_Term && $term->description !== '' ) {
		return wp_strip_all_tags( $term->description );
	}

	if ( $term instanceof WP_Term ) {
		return 'Posts in ' . $term->name . ' on My Buddy Claude.';
	}

	return '';
}

// =========================================================================
// OUTPUT: META DESCRIPTION
// =========================================================================

/**
 * Output the meta description tag.
 *
 * @param array $ctx SEO context from mbc_seo_get_context().
 */
function mbc_seo_output_meta_description( array $ctx ): void {
	if ( $ctx['description'] === '' ) {
		return;
	}

	printf(
		'<meta name="description" content="%s" />' . "\n",
		esc_attr( $ctx['description'] )
	);
}

// =========================================================================
// OUTPUT: OPEN GRAPH
// =========================================================================

/**
 * Output Open Graph meta tags.
 *
 * @param array $ctx SEO context from mbc_seo_get_context().
 */
function mbc_seo_output_open_graph( array $ctx ): void {
	$tags = array(
		'og:site_name'  => $ctx['site_name'],
		'og:type'       => $ctx['og_type'],
		'og:title'      => $ctx['title'],
		'og:description'=> $ctx['description'],
		'og:url'        => $ctx['url'],
	);

	foreach ( $tags as $property => $content ) {
		if ( $content === '' ) {
			continue;
		}
		printf(
			'<meta property="%s" content="%s" />' . "\n",
			esc_attr( $property ),
			esc_attr( $content )
		);
	}

	if ( $ctx['image_url'] !== '' ) {
		printf(
			'<meta property="og:image" content="%s" />' . "\n",
			esc_url( $ctx['image_url'] )
		);
		if ( $ctx['image_width'] > 0 && $ctx['image_height'] > 0 ) {
			printf(
				'<meta property="og:image:width" content="%d" />' . "\n",
				$ctx['image_width']
			);
			printf(
				'<meta property="og:image:height" content="%d" />' . "\n",
				$ctx['image_height']
			);
		}
		printf(
			'<meta property="og:image:alt" content="%s" />' . "\n",
			esc_attr( $ctx['image_alt'] )
		);
	}
}

// =========================================================================
// OUTPUT: TWITTER CARD
// =========================================================================

/**
 * Output Twitter Card meta tags.
 *
 * @param array $ctx SEO context from mbc_seo_get_context().
 */
function mbc_seo_output_twitter_card( array $ctx ): void {
	$card_type = $ctx['image_url'] !== '' ? 'summary_large_image' : 'summary';

	printf(
		'<meta name="twitter:card" content="%s" />' . "\n",
		esc_attr( $card_type )
	);

	if ( $ctx['title'] !== '' ) {
		printf(
			'<meta name="twitter:title" content="%s" />' . "\n",
			esc_attr( $ctx['title'] )
		);
	}

	if ( $ctx['description'] !== '' ) {
		printf(
			'<meta name="twitter:description" content="%s" />' . "\n",
			esc_attr( $ctx['description'] )
		);
	}

	if ( $ctx['image_url'] !== '' ) {
		printf(
			'<meta name="twitter:image" content="%s" />' . "\n",
			esc_url( $ctx['image_url'] )
		);
		printf(
			'<meta name="twitter:image:alt" content="%s" />' . "\n",
			esc_attr( $ctx['image_alt'] )
		);
	}
}

// =========================================================================
// OUTPUT: JSON-LD STRUCTURED DATA
// =========================================================================

/**
 * Output JSON-LD structured data script blocks.
 *
 * @param array $ctx SEO context from mbc_seo_get_context().
 */
function mbc_seo_output_json_ld( array $ctx ): void {
	$schemas = array();

	// WebSite schema — always output.
	$schemas[] = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'name'            => $ctx['site_name'],
		'url'             => home_url( '/' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'        => 'EntryPoint',
				'urlTemplate'  => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	// BlogPosting schema — single journal posts.
	if ( is_singular( 'post' ) && $ctx['post'] instanceof WP_Post ) {
		$post      = $ctx['post'];
		$author_id = (int) $post->post_author;

		$article = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'headline'         => get_the_title( $post ),
			'description'      => $ctx['description'],
			'url'              => get_permalink( $post ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $author_id ),
				'url'   => get_author_posts_url( $author_id ),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => $ctx['site_name'],
				'url'   => home_url( '/' ),
			),
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => get_permalink( $post ),
			),
		);

		if ( $ctx['image_url'] !== '' ) {
			$article['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $ctx['image_url'],
				'width'  => $ctx['image_width'],
				'height' => $ctx['image_height'],
			);
		}

		$schemas[] = $article;
	}

	// SoftwareApplication schema — single tool pages.
	if ( is_singular( 'mbc_tool' ) && $ctx['post'] instanceof WP_Post ) {
		$post     = $ctx['post'];
		$tool_url = get_post_meta( $post->ID, 'mbc_tool_url', true );
		$tool_tech = get_post_meta( $post->ID, 'mbc_tool_tech', true );

		$tool = array(
			'@context'            => 'https://schema.org',
			'@type'               => 'SoftwareApplication',
			'name'                => get_the_title( $post ),
			'description'         => $ctx['description'],
			'url'                 => get_permalink( $post ),
			'applicationCategory' => 'WebApplication',
		);

		if ( is_string( $tool_url ) && $tool_url !== '' ) {
			$tool['sameAs'] = esc_url_raw( $tool_url );
		}

		if ( is_string( $tool_tech ) && $tool_tech !== '' ) {
			$tool['keywords'] = sanitize_text_field( $tool_tech );
		}

		if ( $ctx['image_url'] !== '' ) {
			$tool['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $ctx['image_url'],
				'width'  => $ctx['image_width'],
				'height' => $ctx['image_height'],
			);
		}

		$schemas[] = $tool;
	}

	// BreadcrumbList schema — all non-front-page views.
	if ( ! is_front_page() ) {
		$breadcrumbs = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => 'Home',
				'item'     => home_url( '/' ),
			),
		);

		if ( is_singular( 'post' ) && $ctx['post'] instanceof WP_Post ) {
			$categories = get_the_category( $ctx['post']->ID );
			if ( ! empty( $categories ) ) {
				$breadcrumbs[] = array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => $categories[0]->name,
					'item'     => get_category_link( $categories[0]->term_id ),
				);
			}
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => count( $breadcrumbs ) + 1,
				'name'     => get_the_title( $ctx['post'] ),
				'item'     => get_permalink( $ctx['post'] ),
			);
		} elseif ( is_singular( 'mbc_tool' ) && $ctx['post'] instanceof WP_Post ) {
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => 'Tools',
				'item'     => get_post_type_archive_link( 'mbc_tool' ),
			);
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 3,
				'name'     => get_the_title( $ctx['post'] ),
				'item'     => get_permalink( $ctx['post'] ),
			);
		} elseif ( is_singular( 'page' ) && $ctx['post'] instanceof WP_Post ) {
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => get_the_title( $ctx['post'] ),
				'item'     => get_permalink( $ctx['post'] ),
			);
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => single_term_title( '', false ),
				'item'     => $ctx['url'],
			);
		} elseif ( is_post_type_archive( 'mbc_tool' ) ) {
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => 'Tools',
				'item'     => $ctx['url'],
			);
		}

		$schemas[] = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $breadcrumbs,
		);
	}

	// Output each schema as a separate JSON-LD block.
	foreach ( $schemas as $schema ) {
		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG )
		);
	}
}

// =========================================================================
// MAIN HOOK
// =========================================================================

/**
 * Output all SEO tags in the document head.
 */
function mbc_seo_head(): void {
	if ( is_admin() ) {
		return;
	}

	$ctx = mbc_seo_get_context();
	echo "\n<!-- MBC SEO -->\n";
	mbc_seo_output_meta_description( $ctx );
	mbc_seo_output_open_graph( $ctx );
	mbc_seo_output_twitter_card( $ctx );
	mbc_seo_output_json_ld( $ctx );
	echo "<!-- /MBC SEO -->\n\n";
}
add_action( 'wp_head', 'mbc_seo_head', 5 );
