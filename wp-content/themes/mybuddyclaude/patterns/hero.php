<?php
/**
 * Title: Hero
 * Slug: mybuddyclaude/hero
 * Categories: mybuddyclaude
 * Inserter: true
 *
 * @package mybuddyclaude
 */

?>
<!-- wp:group {"className":"mbc-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}},"border":{"bottom":{"color":"var:preset|color|border","width":"1px","style":"solid"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group mbc-hero" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);border-bottom:1px solid var(--wp--preset--color--border);">

	<!-- wp:paragraph {"className":"mbc-hero__eyebrow","style":{"typography":{"fontSize":"0.7rem","fontWeight":"500","letterSpacing":"0.1em","textTransform":"uppercase"},"color":{"text":"var:preset|color|accent"}},"fontFamily":"mono"} -->
	<p class="mbc-hero__eyebrow has-mono-font-family has-accent-color has-text-color" style="font-size:0.7rem;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;">A human-AI build log</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":1,"className":"mbc-hero__headline","style":{"typography":{"fontStyle":"normal","fontWeight":"700","lineHeight":"1.0","letterSpacing":"-0.03em","fontSize":"clamp(3rem, 7vw, 5.5rem)"},"spacing":{"margin":{"bottom":"1.5rem"}}},"fontFamily":"fraunces"} -->
	<h1 class="wp-block-heading mbc-hero__headline has-fraunces-font-family" style="font-size:clamp(3rem, 7vw, 5.5rem);font-style:normal;font-weight:700;letter-spacing:-0.03em;line-height:1.0;margin-bottom:1.5rem;">Building things<br>together—<br><em>in public.</em></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"mbc-hero__subheadline","style":{"typography":{"fontSize":"clamp(1.1rem, 2vw, 1.3rem)","lineHeight":"1.65"},"color":{"text":"var:preset|color|muted"},"spacing":{"margin":{"bottom":"2.5rem"}}}} -->
	<p class="mbc-hero__subheadline has-muted-color has-text-color" style="font-size:clamp(1.1rem, 2vw, 1.3rem);line-height:1.65;margin-bottom:2.5rem;">A running log of tools, experiments, and discoveries from an ongoing collaboration between a human and Claude. Everything here was built together.</p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"1rem"}}} -->
	<div class="wp-block-buttons">
		<!-- wp:button {"className":"is-style-accent","style":{"spacing":{"padding":{"top":"0.875rem","bottom":"0.875rem","left":"1.75rem","right":"1.75rem"}},"typography":{"fontSize":"0.9rem","fontWeight":"600","letterSpacing":"0.01em"}}} -->
		<div class="wp-block-button is-style-accent"><a class="wp-block-button__link wp-element-button" href="/tools" style="padding-top:0.875rem;padding-bottom:0.875rem;padding-left:1.75rem;padding-right:1.75rem;font-size:0.9rem;font-weight:600;letter-spacing:0.01em;">Explore the tools</a></div>
		<!-- /wp:button -->
		<!-- wp:button {"className":"is-style-ghost","style":{"spacing":{"padding":{"top":"0.875rem","bottom":"0.875rem","left":"1.75rem","right":"1.75rem"}},"typography":{"fontSize":"0.9rem","fontWeight":"600","letterSpacing":"0.01em"}}} -->
		<div class="wp-block-button is-style-ghost"><a class="wp-block-button__link wp-element-button" href="/journal" style="padding-top:0.875rem;padding-bottom:0.875rem;padding-left:1.75rem;padding-right:1.75rem;font-size:0.9rem;font-weight:600;letter-spacing:0.01em;">Read the journal</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
