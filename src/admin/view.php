<?php

namespace LINK_ANALYZER;

/**
 * View for the admin page
 */
function admin_page_view() {
	?>
	<h1>
	WP Link Analyzer
	</h1>
	<p>
	DB Version: <?php echo get_option( 'link_analyzer_db_version' ); ?>
	</p>
	<?php
}
