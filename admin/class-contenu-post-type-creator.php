<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * Taxonomy Creator Post Type Class
 *
 * All functionality pertaining to post types in Taxonomy Creator.
 *
 * @package WordPress
 * @subpackage Contenu
 * @category Plugin
 * @author Matty
 * @since 1.0.0
 */
class Contenu_Post_Type {
	/**
	 * The post type token.
	 * @access public
	 * @since  1.0.0
	 * @var    string
	 */
	public $post_type;

	/**
	 * The post type singular label.
	 * @access public
	 * @since  1.0.0
	 * @var    string
	 */
	public $singular;

	/**
	 * The post type plural label.
	 * @access public
	 * @since  1.0.0
	 * @var    string
	 */
	public $plural;

	/**
	 * The post type args.
	 * @access public
	 * @since  1.0.0
	 * @var    array
	 */
	public $args;

	/**
	 * The taxonomies for this post type.
	 * @access public
	 * @since  1.0.0
	 * @var    array
	 */
	public $taxonomies;

	/**
	 * Constructor function.
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct( $post_type = 'thing', $singular = '', $plural = '', $args = array(), $taxonomies = array() ) {
		$this->post_type = $post_type;
		$this->singular = $singular;
		$this->plural = $plural;
		$this->args = $args;
		$this->taxonomies = $taxonomies;

		$this->register_post_type();

		if ( is_admin() ) {
			global $pagenow;

			add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

		}

	} // End __construct()


	/**
	 * Register the post type.
	 * @access public
	 * @return void
	 */
	public function register_post_type () {
		$labels = array(
			'name' => sprintf( _x( '%s', 'post type general name', 'type-creator' ), $this->plural ),
			'singular_name' => sprintf( _x( '%s', 'post type singular name', 'type-creator' ), $this->singular ),
			'add_new' => _x( 'Add New', $this->post_type, 'type-creator' ),
			'add_new_item' => sprintf( __( 'Add New %s', 'type-creator' ), $this->singular ),
			'edit_item' => sprintf( __( 'Edit %s', 'type-creator' ), $this->singular ),
			'new_item' => sprintf( __( 'New %s', 'type-creator' ), $this->singular ),
			'all_items' => sprintf( __( 'All %s', 'type-creator' ), $this->plural ),
			'view_item' => sprintf( __( 'View %s', 'type-creator' ), $this->singular ),
			'search_items' => sprintf( __( 'Search %a', 'type-creator' ), $this->plural ),
			'not_found' => sprintf( __( 'No %s Found', 'type-creator' ), $this->plural ),
			'not_found_in_trash' => sprintf( __( 'No %s Found In Trash', 'type-creator' ), $this->plural ),
			'parent_item_colon' => '',
			'menu_name' => $this->plural,
		);

		$single_slug = apply_filters( 'type-creator_single_slug', _x( sanitize_title_with_dashes( $this->singular ), 'single post url slug', 'type-creator' ) );
		$archive_slug = apply_filters( 'type-creator_archive_slug', _x( sanitize_title_with_dashes( $this->plural ), 'post archive url slug', 'type-creator' ) );

		$defaults = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => false,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'menu_position' => 5,
			'menu_icon' => 'dashicons-smiley',
		);

		$args = wp_parse_args( $this->args, $defaults );

		register_post_type( $this->post_type, $args );
	} // End register_post_type()

	/**
	 * Update messages for the post type admin.
	 * @since  1.0.0
	 * @param  array $messages Array of messages for all post types.
	 * @return array           Modified array.
	 */
	public function updated_messages ( $messages ) {
		global $post, $post_ID;

		$messages[$this->post_type] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( '%1$s updated. ', 'type-creator' ), $this->singular ),
			2 => __( 'Custom field updated.', 'type-creator' ),
			3 => __( 'Custom field deleted.', 'type-creator' ),
			4 => sprintf( __( '%s updated.', 'type-creator' ), $this->singular ),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( '%s restored to revision from %s', 'type-creator' ), $this->singular, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( '%1$s published. ', 'type-creator' ), $this->singular ),
			7 => sprintf( __( '%s saved.', 'type-creator' ), $this->singular ),
			8 => sprintf( __( '%s submitted. %sPreview %s%s', 'type-creator' ), $this->singular, strtolower( $this->singular ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">', '</a>' ),
			9 => sprintf( __( '%s scheduled for: %1$s. %2$sPreview %s%3$s', 'type-creator' ), $this->singular, strtolower( $this->singular ),
			// translators: Publish box date format, see http://php.net/date
			'<strong>' . date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) . '</strong>', '<a target="_blank" href="' . esc_url( get_permalink($post_ID) ) . '">', '</a>' ),
			10 => sprintf( __( '%s draft updated. %sPreview %s%s', 'type-creator' ), $this->singular, strtolower( $this->singular ), '<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">', '</a>' ),
		);

		return $messages;
	} // End updated_messages()



	/**
	 * Run on activation.
	 * @access public
	 * @since 1.0.0
	 */
	public function activation () {
		$this->flush_rewrite_rules();
	} // End activation()

	/**
	 * Flush the rewrite rules
	 * @access public
	 * @since 1.0.0
	 */
	private function flush_rewrite_rules () {
		$this->register_post_type();
		flush_rewrite_rules();
	} // End flush_rewrite_rules()

	/**
	 * Ensure that "post-thumbnails" support is available for those themes that don't register it.
	 * @access public
	 * @since  1.0.0
	 */
	public function ensure_post_thumbnails_support () {
		if ( ! current_theme_supports( 'post-thumbnails' ) ) { add_theme_support( 'post-thumbnails' ); }
	} // End ensure_post_thumbnails_support()
} // End Class
