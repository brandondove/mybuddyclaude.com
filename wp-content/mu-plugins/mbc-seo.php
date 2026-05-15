<?php
/**
 * Plugin Name: MBC SEO
 * Description: Outputs meta description, Open Graph tags, Twitter Card tags, and JSON-LD structured data.
 * Version: 1.0.0
 * Author: My Buddy Claude
 *
 * @package MBC_SEO
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
		$ctx['title'] = $ctx['site_name'];
		$ctx['url']   = home_url( '/' );

		$front_page_id = (int) get_option( 'page_on_front' );
		if ( $front_page_id > 0 ) {
			$front_page  = get_post( $front_page_id );
			$ctx['post'] = $front_page;

			// Prefer the front page excerpt for a richer meta description.
			if ( $front_page instanceof WP_Post ) {
				$excerpt = mbc_seo_get_description_for_post( $front_page );
				if ( '' !== $excerpt ) {
					$ctx['description'] = $excerpt;
				}
			}

			mbc_seo_set_image( $ctx, $front_page_id );
		}

		// Fall back to the site tagline.
		if ( '' === $ctx['description'] ) {
			$ctx['description'] = get_bloginfo( 'description' );
		}

		return $ctx;
	}

	if ( is_home() ) {
		$posts_page = get_queried_object();
		if ( $posts_page instanceof WP_Post ) {
			$ctx['title']       = get_the_title( $posts_page );
			$ctx['description'] = mbc_seo_get_description_for_post( $posts_page );
			$ctx['url']         = get_permalink( $posts_page );
			$ctx['post']        = $posts_page;
			mbc_seo_set_image( $ctx, $posts_page->ID );
		}
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

		mbc_seo_set_image( $ctx, $post->ID );

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
		$ctx['url']         = get_post_type_archive_link( 'mbc_tool' ) ? get_post_type_archive_link( 'mbc_tool' ) : home_url( '/' );
		return $ctx;
	}

	// Fallback for other pages (search, 404, date archives).
	$ctx['title']       = $ctx['site_name'];
	$ctx['description'] = get_bloginfo( 'description' );
	$ctx['url']         = home_url( '/' );
	return $ctx;
}

/**
 * Populate the image fields in the SEO context from a post's featured image.
 *
 * @param array $ctx     SEO context array (passed by reference).
 * @param int   $post_id Post ID to look up the thumbnail for.
 */
function mbc_seo_set_image( array &$ctx, int $post_id ): void {
	$thumbnail_id = get_post_thumbnail_id( $post_id );
	if ( ! $thumbnail_id ) {
		return;
	}

	$image_url = wp_get_attachment_image_url( (int) $thumbnail_id, 'full' );
	if ( ! $image_url ) {
		return;
	}

	$ctx['image_url'] = $image_url;
	$image_meta       = wp_get_attachment_metadata( (int) $thumbnail_id );
	if ( is_array( $image_meta ) ) {
		$ctx['image_width']  = (int) ( $image_meta['width'] ?? 0 );
		$ctx['image_height'] = (int) ( $image_meta['height'] ?? 0 );
	}
	$alt              = get_post_meta( (int) $thumbnail_id, '_wp_attachment_image_alt', true );
	$ctx['image_alt'] = is_string( $alt ) && '' !== $alt ? $alt : $ctx['title'];
}

/**
 * Derive a meta description from a post's excerpt or content.
 *
 * @param WP_Post $post The post object.
 * @return string
 */
function mbc_seo_get_description_for_post( WP_Post $post ): string {
	if ( '' !== $post->post_excerpt ) {
		return wp_strip_all_tags( $post->post_excerpt );
	}

	$content = $post->post_content;
	if ( '' === $content ) {
		return '';
	}

	// Strip block markup comments.
	$content = preg_replace( '/<!--(.|\s)*?-->/', '', $content );
	$content = wp_strip_all_tags( (string) $content );
	$content = trim( $content );

	if ( '' === $content ) {
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
 *
 * @return string
 */
function mbc_seo_get_archive_description(): string {
	$term = get_queried_object();

	if ( $term instanceof WP_Term && '' !== $term->description ) {
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
	if ( '' === $ctx['description'] ) {
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
		'og:locale'      => str_replace( '-', '_', get_bloginfo( 'language' ) ),
		'og:site_name'   => $ctx['site_name'],
		'og:type'        => $ctx['og_type'],
		'og:title'       => $ctx['title'],
		'og:description' => $ctx['description'],
		'og:url'         => $ctx['url'],
	);

	foreach ( $tags as $property => $content ) {
		if ( '' === $content ) {
			continue;
		}
		printf(
			'<meta property="%s" content="%s" />' . "\n",
			esc_attr( $property ),
			esc_attr( $content )
		);
	}

	if ( '' !== $ctx['image_url'] ) {
		printf(
			'<meta property="og:image" content="%s" />' . "\n",
			esc_url( $ctx['image_url'] )
		);
		if ( $ctx['image_width'] > 0 && $ctx['image_height'] > 0 ) {
			printf(
				'<meta property="og:image:width" content="%d" />' . "\n",
				(int) $ctx['image_width']
			);
			printf(
				'<meta property="og:image:height" content="%d" />' . "\n",
				(int) $ctx['image_height']
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
	$card_type = '' !== $ctx['image_url'] ? 'summary_large_image' : 'summary';

	printf(
		'<meta name="twitter:card" content="%s" />' . "\n",
		esc_attr( $card_type )
	);

	if ( '' !== $ctx['title'] ) {
		printf(
			'<meta name="twitter:title" content="%s" />' . "\n",
			esc_attr( $ctx['title'] )
		);
	}

	if ( '' !== $ctx['description'] ) {
		printf(
			'<meta name="twitter:description" content="%s" />' . "\n",
			esc_attr( $ctx['description'] )
		);
	}

	if ( '' !== $ctx['image_url'] ) {
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
// ENTITY IDENTIFIERS & HELPERS
// =========================================================================

/**
 * Stable @id for the publishing Organization.
 *
 * @return string
 */
function mbc_seo_organization_id(): string {
	return home_url( '/#organization' );
}

/**
 * Stable @id for the primary Person (site author).
 *
 * @return string
 */
function mbc_seo_person_id(): string {
	return home_url( '/#person-brandon-dove' );
}

/**
 * Build the Organization schema node.
 *
 * @return array<string,mixed>
 */
function mbc_seo_get_organization_schema(): array {
	$site_name   = get_bloginfo( 'name' );
	$description = get_bloginfo( 'description' );

	$organization = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Organization',
		'@id'         => mbc_seo_organization_id(),
		'name'        => $site_name,
		'url'         => home_url( '/' ),
		'description' => $description,
	);

	$logo = mbc_seo_get_publisher_logo();
	if ( null !== $logo ) {
		$organization['logo'] = $logo;
		// Google's Article rich results expect `image` on the publisher.
		$organization['image'] = array( '@id' => $logo['@id'] );
	}

	/**
	 * Filter the `sameAs` URLs for the Organization schema.
	 *
	 * @param string[] $urls List of canonical profile URLs.
	 */
	$same_as = apply_filters(
		'mbc_seo_organization_same_as',
		array(
			'https://github.com/brandondove/mybuddyclaude.com',
		)
	);

	$same_as = array_values( array_filter( array_map( 'esc_url_raw', (array) $same_as ) ) );
	if ( ! empty( $same_as ) ) {
		$organization['sameAs'] = $same_as;
	}

	return $organization;
}

/**
 * Build the ImageObject node for the publisher logo, if a site icon is set.
 *
 * @return array<string,mixed>|null
 */
function mbc_seo_get_publisher_logo(): ?array {
	$site_icon_id = (int) get_option( 'site_icon' );
	if ( $site_icon_id <= 0 ) {
		return null;
	}

	$url = wp_get_attachment_image_url( $site_icon_id, 'full' );
	if ( ! is_string( $url ) || '' === $url ) {
		return null;
	}

	$logo = array(
		'@type' => 'ImageObject',
		'@id'   => home_url( '/#logo' ),
		'url'   => $url,
	);

	$meta = wp_get_attachment_metadata( $site_icon_id );
	if ( is_array( $meta ) ) {
		if ( isset( $meta['width'] ) ) {
			$logo['width'] = (int) $meta['width'];
		}
		if ( isset( $meta['height'] ) ) {
			$logo['height'] = (int) $meta['height'];
		}
	}

	return $logo;
}

/**
 * Build the Person schema node for the primary site author.
 *
 * @return array<string,mixed>
 */
function mbc_seo_get_person_schema(): array {
	$about_url = mbc_seo_get_about_url();

	$person = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Person',
		'@id'         => mbc_seo_person_id(),
		'name'        => 'Brandon Dove',
		'url'         => '' !== $about_url ? $about_url : home_url( '/' ),
		'jobTitle'    => 'Founder, Pixel Jar',
		'description' => 'WordPress developer documenting human-AI collaboration in public.',
		'worksFor'    => array(
			'@type' => 'Organization',
			'name'  => 'Pixel Jar',
			'url'   => 'https://pixeljar.com/',
		),
	);

	/**
	 * Filter the `sameAs` URLs for the primary Person schema.
	 *
	 * @param string[] $urls List of canonical profile URLs (social, professional, etc.).
	 */
	$same_as = apply_filters(
		'mbc_seo_person_same_as',
		array(
			'https://github.com/brandondove',
		)
	);

	$same_as = array_values( array_filter( array_map( 'esc_url_raw', (array) $same_as ) ) );
	if ( ! empty( $same_as ) ) {
		$person['sameAs'] = $same_as;
	}

	return $person;
}

/**
 * Get the URL of the About page if it exists.
 *
 * @return string
 */
function mbc_seo_get_about_url(): string {
	$about = get_page_by_path( 'about' );
	if ( $about instanceof WP_Post ) {
		return (string) get_permalink( $about );
	}
	return '';
}

/**
 * Build a CollectionPage schema node for journal, tools, and tool category archives.
 *
 * Lists the most recent items via `hasPart` so Google can resolve the
 * collection's contents without crawling every paginated archive page.
 *
 * @param array $ctx SEO context.
 * @return array<string,mixed>|null
 */
function mbc_seo_get_collection_page_schema( array $ctx ): ?array {
	$query_args = null;
	$name       = '';

	if ( is_home() ) {
		$query_args = array(
			'post_type'      => 'post',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
		);
		$name       = $ctx['title'] !== '' ? $ctx['title'] : 'Journal';
	} elseif ( is_post_type_archive( 'mbc_tool' ) ) {
		$query_args = array(
			'post_type'      => 'mbc_tool',
			'posts_per_page' => 50,
			'post_status'    => 'publish',
		);
		$name       = $ctx['title'] !== '' ? $ctx['title'] : 'Tools';
	} elseif ( is_tax( 'tool_category' ) ) {
		$term = get_queried_object();
		if ( ! $term instanceof WP_Term ) {
			return null;
		}
		$query_args = array(
			'post_type'      => 'mbc_tool',
			'posts_per_page' => 50,
			'post_status'    => 'publish',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'tool_category',
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				),
			),
		);
		$name       = $term->name;
	}

	if ( null === $query_args ) {
		return null;
	}

	$posts = get_posts( $query_args );

	$collection = array(
		'@context' => 'https://schema.org',
		'@type'    => 'CollectionPage',
		'name'     => $name,
		'url'      => $ctx['url'],
	);

	if ( '' !== $ctx['description'] ) {
		$collection['description'] = $ctx['description'];
	}

	if ( ! empty( $posts ) ) {
		$collection['hasPart'] = array_map(
			static function ( WP_Post $item ): array {
				$is_post = 'post' === $item->post_type;
				$part    = array(
					'@type'         => $is_post ? 'BlogPosting' : 'SoftwareApplication',
					'name'          => get_the_title( $item ),
					'headline'      => get_the_title( $item ),
					'url'           => get_permalink( $item ),
					'datePublished' => get_the_date( 'c', $item ),
					'dateModified'  => get_the_modified_date( 'c', $item ),
					'author'        => array( '@id' => mbc_seo_person_id() ),
				);
				if ( '' !== (string) $item->post_excerpt ) {
					$part['description'] = wp_strip_all_tags( $item->post_excerpt );
				}
				return $part;
			},
			$posts
		);
	}

	return $collection;
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

	// Organization schema — always output.
	$schemas[] = mbc_seo_get_organization_schema();

	// Person schema — always output so the entity is resolvable from any page.
	$schemas[] = mbc_seo_get_person_schema();

	// ProfilePage schema — mark the About page as the canonical resource for the Person.
	if ( is_page( 'about' ) ) {
		$schemas[] = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'ProfilePage',
			'url'        => $ctx['url'],
			'name'       => $ctx['title'],
			'mainEntity' => array( '@id' => mbc_seo_person_id() ),
		);
	}

	// WebSite schema — always output.
	$schemas[] = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'name'            => $ctx['site_name'],
		'url'             => home_url( '/' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	// BlogPosting schema — single journal posts.
	if ( is_singular( 'post' ) && $ctx['post'] instanceof WP_Post ) {
		$post = $ctx['post'];

		$publisher = array(
			'@type' => 'Organization',
			'@id'   => mbc_seo_organization_id(),
			'name'  => $ctx['site_name'],
			'url'   => home_url( '/' ),
		);

		$logo = mbc_seo_get_publisher_logo();
		if ( null !== $logo ) {
			$publisher['logo'] = $logo;
		}

		$article = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'headline'         => get_the_title( $post ),
			'description'      => $ctx['description'],
			'url'              => get_permalink( $post ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'inLanguage'       => str_replace( '_', '-', (string) get_locale() ),
			'author'           => array(
				'@id' => mbc_seo_person_id(),
			),
			'publisher'        => $publisher,
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => get_permalink( $post ),
			),
		);

		$categories = get_the_category( $post->ID );
		if ( ! empty( $categories ) ) {
			$section_names = array_map(
				static fn( WP_Term $term ): string => $term->name,
				$categories
			);
			$article['articleSection'] = count( $section_names ) === 1
				? $section_names[0]
				: array_values( $section_names );
		}

		$tags = get_the_tags( $post->ID );
		if ( is_array( $tags ) && ! empty( $tags ) ) {
			$article['keywords'] = implode(
				', ',
				array_map( static fn( WP_Term $term ): string => $term->name, $tags )
			);
		}

		$word_count = str_word_count( wp_strip_all_tags( (string) $post->post_content ) );
		if ( $word_count > 0 ) {
			$article['wordCount'] = $word_count;
		}

		if ( '' !== $ctx['image_url'] ) {
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
		$post      = $ctx['post'];
		$tool_url  = get_post_meta( $post->ID, 'mbc_tool_url', true );
		$tool_tech = get_post_meta( $post->ID, 'mbc_tool_tech', true );

		$tool = array(
			'@context'            => 'https://schema.org',
			'@type'               => 'SoftwareApplication',
			'name'                => get_the_title( $post ),
			'description'         => $ctx['description'],
			'url'                 => get_permalink( $post ),
			'applicationCategory' => 'DeveloperApplication',
			'operatingSystem'     => 'Web',
			'inLanguage'          => str_replace( '_', '-', (string) get_locale() ),
			'creator'             => array( '@id' => mbc_seo_person_id() ),
			'publisher'           => array( '@id' => mbc_seo_organization_id() ),
			'dateCreated'         => get_the_date( 'c', $post ),
			'dateModified'        => get_the_modified_date( 'c', $post ),
			'offers'              => array(
				'@type'         => 'Offer',
				'price'         => '0',
				'priceCurrency' => 'USD',
			),
		);

		if ( is_string( $tool_url ) && '' !== $tool_url ) {
			$tool['sameAs'] = esc_url_raw( $tool_url );
		}

		if ( is_string( $tool_tech ) && '' !== $tool_tech ) {
			$tool['keywords'] = sanitize_text_field( $tool_tech );
		}

		if ( '' !== $ctx['image_url'] ) {
			$tool['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $ctx['image_url'],
				'width'  => $ctx['image_width'],
				'height' => $ctx['image_height'],
			);
		}

		$schemas[] = $tool;
	}

	// CollectionPage schema — journal index, tools archive, and tool category archives.
	$collection = mbc_seo_get_collection_page_schema( $ctx );
	if ( null !== $collection ) {
		$schemas[] = $collection;
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
