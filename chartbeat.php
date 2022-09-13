<php 
     
function chartbeat_configs() {
	$domain = 'bandab.com.br';
	$user_id = 66994;
	
	$cb_configs['domain'] = $domain;
	$cb_configs['uid'] = $user_id;
	
	if (is_single()) {
		$post = get_queried_object();

		/* só verificar essa parte, porque sei que existe um campo personalizado para puxar o nome do autor no WP */ 
		$author = get_the_author_meta('display_name', $post->post_author);
		$cb_configs['author'] = apply_filters( 'chartbeat_config_author', $author );
	
		$cats = get_the_terms($post->ID, 'category');
		if ($cats) {
			$cat_names = array();
			foreach ( $cats as $cat ) {
				$cat_names[] = $cat->name;
			}
		}

		$sections = (array)apply_filters( 'chartbeat_config_sections', $cat_names );
		$cb_configs['sections'] = implode( ", ", $sections);
	}

	return $cb_configs;

}


function add_chartbeat_head() {
	$cb_configs = chartbeat_configs();
    $cb_headline_testing = true;
	?>
		<script type="text/javascript">
			var _sf_async_config = window._sf_async_config = (window._sf_async_config || {});
			_sf_async_config.uid = <?php echo esc_js($cb_configs["uid"]); ?>;
			_sf_async_config.domain = "<?php echo esc_js($cb_configs["domain"]); ?>";
            _sf_async_config.useCanonical = true;
            _sf_async_config.useCanonicalDomain = true;
	<?php if (is_single()) {?>
            _sf_async_config.authors = "<?php echo esc_js($cb_configs["author"]); ?>";
            _sf_async_config.sections = "<?php echo esc_js($cb_configs["sections"]); ?>";
    <?php } ?>
        (function() {
            function loadChartbeat() {var e = document.createElement('script');var n = document.getElementsByTagName('script')[0];e.type = 'text/javascript';e.async = true;e.src = '//static.chartbeat.com/js/chartbeat.js';n.parentNode.insertBefore(e, n);} loadChartbeat();
        <?php if ($cb_headline_testing == 1) {?>
            function loadChartbeatMAB() {var e = document.createElement('script');var n = document.getElementsByTagName('script')[0];e.type = 'text/javascript';e.async = true;e.src = '//static.chartbeat.com/js/chartbeat_mab.js';n.parentNode.insertBefore(e, n);} loadChartbeatMAB();
        <?php } ?>
        })();
        </script>         
<?php
}

add_action('wp_head', 'add_chartbeat_head');


function chartbeat_amp_add_analytics( $analytics ) {
  if ( ! is_array( $analytics ) ) {
      $analytics = array();
  }

	$cb_configs = chartbeat_configs(); 

	$analytics['chartbeat'] = array(
	'type' => 'chartbeat',
	'attributes' => array(),
	'config_data' => array(
		'vars' => array(
			'uid' => esc_js($cb_configs["uid"]),
			'domain' => esc_js($cb_configs["domain"]),
		)
	),
	);
	if (is_single()) { 
		$analytics['chartbeat']['config_data']['vars']['authors'] = esc_js($cb_configs['author']);
		$analytics['chartbeat']['config_data']['vars']['sections'] = esc_js($cb_configs['sections']);
	}
	return $analytics;
}

add_filter( 'amp_post_template_analytics', 'chartbeat_amp_add_analytics' );

use Facebook\InstantArticles\Elements\Analytics;

add_action( 'instant_articles_after_transform_post', function ($ia_post) {
    $instant_article = $ia_post->instant_article;
	$cb_configs = chartbeat_configs(); 
	$cbia_start = '<script type="text/javascript"
							(function() {
								var _sf_async_config = window._sf_async_config = (window._sf_async_config || {});
								_sf_async_config.path = window.ia_document.shareURL;
								_sf_async_config.title = window.ia_document.title; ';
	$cbia_config = '_sf_async_config.uid = "' . $cb_configs["uid"] . '"; _sf_async_config.domain = "' . $cb_configs["domain"] . '"; ';    
    if (is_single()) {
		$the_config .= '_sf_async_config.authors = "' . $cb_configs["author"] . '";_sf_async_config.sections = "' . $cb_configs["sections"] . '";'; 
  	}
    $cbia_end = '})();</script><script src="//static.chartbeat.com/js/chartbeat_fia.js"></script>';
	$cbia_script = $cbia_start . $cbia_config . $cbia_end;
	$instant_article->addChild(Analytics::create()->withHTML($cbia_script));

});

?>
