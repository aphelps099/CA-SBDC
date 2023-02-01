<?php


// read in main scripts file
$blockImports = file_get_contents(plugin_dir_path(__FILE__).'blocks.js');

// remove comments
$blockImports = preg_replace('/\/\*(?:.|\s)+?(?=.|\s)*\*\//', '', $blockImports);
$blockImports = preg_replace('/\/\/[^\n]*/', '', $blockImports);

$blocks = array();
foreach(array_map('trim', explode("\n", $blockImports)) as $line) {
	if(preg_match('/import\s+[\'"]\.\/(.+)(?=\/)\/block\.js/', $line, $matches)) {
		if(file_exists(plugin_dir_path(__FILE__).$matches[1].'/block.php')) {
			include_once plugin_dir_path(__FILE__).$matches[1].'/block.php';
		}
	}
}

// load assets for specific blogs
$theme = wp_get_theme();

if ( $theme->stylesheet == 'norcal-sbdc-ptac' ) {
	add_action('enqueue_block_editor_assets', function() {
		wp_enqueue_script('ptac-crown-blocks', site_url().'/wp-content/plugins/crown-blocks/assets/ptac/ptac-crown-blocks.js', array('lodash'));
	});
}
