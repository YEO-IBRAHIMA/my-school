<?php 
namespace BTSC;

if ( !defined( 'ABSPATH' ) ) { exit; }

class Register {
	private $postType = 'blocks-to-shortcode';

	function __construct() {
		add_action( 'init', [ $this, 'init' ] );
		add_shortcode( 'btsc', [$this, 'addShortcode'] );
		add_action( 'manage_'. $this->postType .'_posts_custom_column', [$this, 'manageCustomColumns'], 10, 2 );
		add_filter( 'manage_'. $this->postType .'_posts_columns', [$this, 'manageColumns'], 10 );
		add_action( 'admin_enqueue_scripts', [ $this, 'adminEnqueueScripts' ] );
	}

	function init() {
		register_post_type( $this->postType, [
			'labels'				=> [
				'name'			=> __( 'Blocks to Shortcode', 'blocks-to-shortcode' ),
				'singular_name'	=> __( 'Blocks to Shortcode', 'blocks-to-shortcode' ),
				'add_new'		=> __( 'Create Shortcode', 'blocks-to-shortcode' ),
				'add_new_item'	=> __( 'Create New Shortcode', 'blocks-to-shortcode' ),
				'edit_item'		=> __( 'Edit Shortcode', 'blocks-to-shortcode' ),
				'new_item'		=> __( 'New Shortcode', 'blocks-to-shortcode' ),
				'view_item'		=> __( 'View Shortcode', 'blocks-to-shortcode' ),
				'search_items'	=> __( 'Search Shortcode', 'blocks-to-shortcode' ),
				'not_found'		=> __( 'Sorry, we couldn\'t find the that you are looking for.', 'blocks-to-shortcode' )
			],
			'public'				=> false,
			'show_ui'				=> true, 		
			'show_in_rest'			=> true,							
			'publicly_queryable'	=> false,
			'exclude_from_search'	=> true,
			'menu_position'			=> 14,
			'menu_icon'				=> 'dashicons-shortcode',		
			'has_archive'			=> false,
			'hierarchical'			=> false,
			'capability_type'		=> 'page',
			'rewrite'				=> [ 'slug' => 'blocks-to-shortcode' ],
			'supports'				=> [ 'title', 'editor' ]
		] );
	}

	function addShortcode( $atts ) {
		$postId = $atts['id'] ?? null;

		if ( !isset( $postId ) ) {
			return '';
		}

		$post = get_post( $atts['id'] );

		if ( !$post ) {
			return '';
		}

		if ( post_password_required( $post ) ) {
			return get_the_password_form( $post );
		}

		switch ( $post->post_status ) {
			case 'publish':
				return $this->showBlocks( $post );

			case 'private':
				if ( current_user_can( 'read_private_posts' ) ) {
					return $this->showBlocks( $post );
				}
				return '';

			case 'draft':
			case 'pending':
			case 'future':
				if ( current_user_can( 'edit_post', $postId ) ) {
					return $this->showBlocks( $post );
				}
				return '';

			default:
				return '';
		}
	}

	function showBlocks( $post ) {
		$blocks = parse_blocks( $post->post_content );

		if ( empty( $blocks ) ) {
			return '';
		}

		global $allowedposttags;
		$commonAttr = [ 'aria-controls' => 1, 'aria-current' => 1, 'aria-describedby' => 1, 'aria-details' => 1, 'aria-expanded' => 1, 'aria-hidden' => 1, 'aria-label' => 1, 'aria-labelledby' => 1, 'aria-live' => 1, 'class' => 1, 'data-*' => 1,'height' => 1, 'id' => 1, 'style' => 1, 'width' => 1 ];
		$svgAttr = [ 'fill' => 1, 'stroke' => 1, 'stroke-width' => 1, 'transform' => 1 ] + $commonAttr;

		$allowedHTML = wp_parse_args( [
			'style' => [],
			'form' => $commonAttr + [ 'action' => 1, 'method' => 1, 'enctype' => 1 ],
			'select' => $commonAttr + [ 'name' => 1 ],
			'option' => $commonAttr + [ 'value' => 1, 'selected' => 1 ],
			'input' => $commonAttr + [ 'type' => 1, 'name' => 1, 'value' => 1, 'checked' => 1 ],
			'svg' => $svgAttr + [ 'xmlns' => 1, 'viewbox' => 1 ],
			'circle' => $svgAttr + [ 'cx' => 1, 'cy' => 1, 'r' => 1, 'pathlength' => 1 ],
			'clipPath' => $commonAttr + [ 'clippathunits' => 1 ],
			'desc' => $commonAttr,
			'defs' => $commonAttr,
			'ellipse' => $svgAttr + [ 'cx' => 1, 'cy' => 1, 'rx' => 1, 'ry' => 1 ],
			'g' => $svgAttr,
			'line' => $svgAttr + [ 'x1' => 1, 'x2' => 1, 'y1' => 1, 'y2' => 1 ],
			'linearGradient' => $commonAttr + [ 'gradientUnits' => 1, 'gradientTransform' => 1, 'href' => 1, 'x1' => 1, 'x2' => 1, 'y1' => 1, 'y2' => 1 ],
			'path' => $svgAttr + [ 'd' => 1, 'pathlength' => 1 ],
			'polygon' => $svgAttr + [ 'points' => 1, 'pathlength' => 1 ],
			'polyline' => $svgAttr + [ 'points' => 1, 'pathlength' => 1 ],
			'rect' => $svgAttr + [ 'x' => 1, 'y' => 1, 'rx' => 1, 'ry' => 1, 'pathlength' => 1 ],
			'stop' => $svgAttr + [ 'offset' => 1, 'stop-color' => 1, 'stop-opacity' => 1 ],
			'title' => $commonAttr,
			'iframe' => $commonAttr + [ 'allow' => 1, 'allowfullscreen' => 1, 'loading' => 1, 'name' => 1, 'referrerpolicy' => 1, 'sandbox' => 1, 'src' => 1, 'srcdoc' => 1, 'frameborder' => 1, 'title' => 1 ]
		], $allowedposttags );

		$content = '';
		foreach ( $blocks as $block ) {
			// if( 'core/embed' === $block['blockName'] ) {
			// 	$content .= apply_filters( 'the_content', render_block( $block ) );
			// }else if( 'core/shortcode' === $block['blockName'] ) {
			// 	$content .= do_shortcode( render_block( $block ) );
			// }else {
			// 	$content .= render_block( $block );
			// }

			if( 'core/embed' === $block['blockName'] ) {
				$content .= apply_filters( 'the_content', render_block( $block ) );
			}else {
				$content .= do_shortcode( render_block( $block ) );
			}
		}

		$content = apply_filters( 'the_content', $content );
		return wp_kses( $content, $allowedHTML );
	}

	function manageCustomColumns( $column, $id ) {
		if ( $column === 'shortcode' ) {
			echo sprintf( '<div class="pevPostShortcode" id="pevPostShortcode-%1$s">
				<input value="[btsc id=%1$s]" onclick="copyPEVPostShortcode(%1$s)">
				<span>Click to Copy</span>
			</div>', esc_attr( $id ) );
		}
	}

	function manageColumns( $columns ) {
		unset( $columns['date'] );
		$columns['shortcode'] = __( 'Shortcode', 'blocks-to-shortcode' );
		$columns['date'] = 'Date';
		return $columns;
	}

	function adminEnqueueScripts( $path ) {
		if( 'edit.php' === $path && isset( $_GET['post_type'] ) && $_GET['post_type'] === $this->postType ) {
			wp_enqueue_style( 'btsc-admin-post', BTSC_DIR_URL . 'admin/css/post.css', [], BTSC_VERSION );
			wp_enqueue_script( 'btsc-admin-post', BTSC_DIR_URL . 'admin/js/post.js', [], BTSC_VERSION, true );
		}
	}
}
new Register();