<?php
/**
 * Title: Tools Grid
 * Slug: mybuddyclaude/tools-grid
 * Categories: mybuddyclaude
 * Inserter: true
 *
 * @package mybuddyclaude
 */

?>
<!-- wp:query {"queryId":3,"query":{"perPage":6,"pages":0,"offset":0,"postType":"mbc_tool","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"layout":{"type":"default"}} -->
<div class="wp-block-query">

	<!-- wp:post-template {"layout":{"type":"grid","columnCount":3},"style":{"spacing":{"blockGap":"1.25rem"}}} -->

		<!-- wp:group {"className":"mbc-card","style":{"spacing":{"padding":{"top":"1.75rem","bottom":"1.75rem","left":"1.75rem","right":"1.75rem"}},"border":{"radius":"12px","color":"var:preset|color|border","width":"1px","style":"solid"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"space-between"}} -->
		<div class="wp-block-group mbc-card" style="border:1px solid var(--wp--preset--color--border);border-radius:12px;padding:1.75rem;">

			<!-- wp:group {"layout":{"type":"default"},"style":{"spacing":{"blockGap":"0.75rem","margin":{"bottom":"1.5rem"}}}} -->
			<div class="wp-block-group" style="margin-bottom:1.5rem;">
				<!-- wp:post-title {"isLink":true,"style":{"typography":{"fontWeight":"700","letterSpacing":"-0.01em","fontSize":"1.2rem"}},"fontFamily":"fraunces"} /-->
				<!-- wp:post-excerpt {"moreText":"","showMoreOnNewLine":false,"excerptLength":25,"style":{"color":{"text":"var:preset|color|muted"},"typography":{"fontSize":"0.875rem","lineHeight":"1.5"}}} /-->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","alignItems":"center"}} -->
			<div class="wp-block-group">
				<!-- wp:post-terms {"term":"tool_category","style":{"color":{"text":"var:preset|color|muted"},"typography":{"fontSize":"0.72rem","fontWeight":"500","letterSpacing":"0.04em","textTransform":"uppercase"}},"fontFamily":"mono"} /-->
				<!-- wp:post-date {"format":"Y","style":{"color":{"text":"var:preset|color|muted"},"typography":{"fontSize":"0.72rem"}},"fontFamily":"mono"} /-->
			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	<!-- /wp:post-template -->

	<!-- wp:query-no-results -->
	<!-- wp:group {"style":{"spacing":{"padding":{"top":"3rem","bottom":"3rem"}}},"layout":{"type":"constrained"}} -->
	<div class="wp-block-group" style="padding-top:3rem;padding-bottom:3rem;">
		<!-- wp:paragraph {"align":"center","style":{"color":{"text":"var:preset|color|muted"},"typography":{"fontSize":"1rem"}}} -->
		<p class="has-text-align-center" style="color:var(--wp--preset--color--muted);font-size:1rem;">Tools are in the works — check back soon.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- /wp:query-no-results -->

</div>
<!-- /wp:query -->
