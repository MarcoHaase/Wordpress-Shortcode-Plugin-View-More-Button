<?php
/**
 * Plugin Name: View More Shortcode
 * Plugin URI: http://www.uva.de
 * Description: Return "View More" button
 * Version: 1.0
 * Author: Marco Haase
 * Author URI: http://www.uva.de
 */

 // Direkten Aufruf verhindern
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add data attributes to the query block to describe the block query.
 *
 * @param string $block_content Default query content.
 * @param array  $block Parsed block.
 * @return string
 */
function mhplg_query_render_block( $block_content, $block ) {
	if ( 'core/query' === $block['blockName'] ) {
		$query_id      = $block['attrs']['queryId'];
		$container_end = strpos( $block_content, '>' );

		$paged = absint( $_GET[ 'query-' . $query_id . '-page' ] ?? 1 );
		$custom_posts = new WP_Query();
		$custom_posts->query('post_type=post');
		$block['attrs']['query']['pages'] = ceil($custom_posts->post_count/$block['attrs']['query']['perPage']);
	    $jsonblock = wp_json_encode( $block );
		$jsonblock = do_shortcode($jsonblock);
		$block_content = substr_replace( $block_content, ' data-paged="' . esc_attr( $paged ) . '" data-attrs="' . esc_attr( $jsonblock ) . '"', $container_end, 0 );
	}
	return $block_content;
}
\add_filter( 'render_block', __NAMESPACE__ . '\mhplg_query_render_block', 10, 2 );
function uva_f_return_view_more_button( $atts = [], $content = null, $tag = '' ) {
	$atts = array_change_key_case( (array) $atts, CASE_LOWER );
	$a = shortcode_atts( array(
		"link" => "#",
		"div_class" => "wp-block-query-vm-button",
		"button_text" => "View More",
	), $atts, $tag );
	$block_content = sprintf( "<div class='%s'><a href='%s' class='view-more-query button'>%s</a></div>", 
		esc_attr( $a['div_class'] ),
		esc_attr( $a['link'] ),
		esc_html( $a['button_text'] )
	);
	return $block_content;
}

function uva_f_return_view_more_button_init() {
	add_shortcode( 'view_more_button', 'uva_f_return_view_more_button' );
}
add_action('init', 'uva_f_return_view_more_button_init');

/**
 * AJAX function render more posts.
 *
 * @return void
 */
function mhplg_query_pagination_render_more_query() {
	if (isset($_GET['attrs'])) :
		$block = json_decode( stripslashes( $_GET['attrs'] ), true );
		$paged = absint( $_GET['paged'] ?? 1 );

		if ( $block ) {
			$block['attrs']['query']['offset'] += $block['attrs']['query']['perPage'] * $paged;
			echo render_block( $block );
		}
	endif;
}
add_action( 'wp_enqueue_scripts', 'mhplg_query_pagination_render_more_query' );


function mhplg_my_theme_scripts() {
    wp_enqueue_script( 'my-great-script', '/wp-content/plugins/uva-view-more/js/jquery-3.6.0.min.js', array( 'jquery' ), '3.6.0', true );
    wp_enqueue_script( 'uva-view-more', '/wp-content/plugins/uva-view-more/js/uva-view-more.js', array( 'jquery' ), '0.0.1', true );
}
add_action( 'wp_enqueue_scripts', 'mhplg_my_theme_scripts' );
