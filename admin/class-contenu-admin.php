<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Contenu
 * @subpackage Contenu/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Contenu
 * @subpackage Contenu/admin
 * @author     Your Name <email@example.com>
 */
class Contenu_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $contenu    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Save the page hook for enqueueing styles.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $page_hook    Page hook name.
	 */
	private $page_hook;


	/**
	 * List of registered post types.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $post_types    List of post types.
	 */
	private $post_types;

	/**
	 * Cached version of the record from the database.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $_types_cache    List of post types.
	 */
	private $_types_cache;

	/**
	 * Determine if the user has the correct permissions.
	 */
	private function can_save_data() {

		/** Check for nonce variable. */
		$nonce = null;

		/**
		 * Check the two common places for the variable in the post body and in the header.
		 */
		if ( isset( $_REQUEST['_wp_rest_nonce'] ) ) {
			$nonce = $_REQUEST['_wp_rest_nonce'];
		} elseif ( isset( $_SERVER['HTTP_X_WP_NONCE'] ) ) {
			$nonce = $_SERVER['HTTP_X_WP_NONCE'];
		}

		/**
		 * If the $nonce variable is still null. Reset the authentication and return false.
		 */
		if ( null === $nonce ) {
			// No nonce at all, so act as if it's an unauthenticated request.
			wp_set_current_user( 0 );
			return false;
		}

		/**
		 * Verify and return false if it isn't correct.
		 */
		if ( ! wp_verify_nonce( $nonce, 'contenu' ) ) {
			return false;
		}

		/**
		 * Double check for some reason this isn't happening.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		/**
		 * This global is set in the admin. Since this is the only place
		 * this plugin should run.
		 */
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( current_user_can( 'manage_options' ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Validates the type. Might be overkill. But WHATEVER.
	 * @param  array $body Record unsanitized.
	 * @return array       Record sanitized.
	 */
	private function sanitize_type( $body ) {

		$record 					= (array) array();
		$record['fields'] = (array) array();

		if ( isset( $body->id ) ) {
			$record['id'] = sanitize_text_field( $body->id );
		}

		if ( isset( $body->name ) ) {
			$record['name'] = sanitize_text_field( $body->name );
		}

		if ( isset( $body->plural ) ) {
			$record['plural'] = sanitize_text_field( $body->plural );
		}

		if ( isset( $body->single ) ) {
			$record['single'] = (bool) $body->single;
		}

		if ( isset( $body->description ) ) {
			$record['description'] = sanitize_text_field( $body->description );
		}

		if ( isset( $body->fields ) && is_array( $body->fields ) ) {

			foreach ( $body->fields as $field ) {
				$_field = array(
					'name' 				=> ( isset( $field->name ) ) ? sanitize_text_field( $field->name ) : '',
					'private'			=> (bool) $field->private,
					'value'				=> (bool) $field->value,
					'description' => ( isset( $field->description ) ) ? sanitize_text_field( $field->description ) : '',
					'type'				=> ( isset( $field->type ) ) ? sanitize_text_field( $field->type ) : 'text',
					'combined'		=> ( isset( $field->combined ) ) ? sanitize_text_field( $field->combined ) : '',
					'relation'		=> ( isset( $field->relation ) ) ? sanitize_text_field( $field->relation ) : '',
					'width'				=> intval( ( isset( $field->width ) && $field->width !== '' ) ? sanitize_text_field( $field->width ) : 0 ),
					'options'			=> array(),
				);

				if ( isset( $field->options ) && is_array( $field->options ) ) {
					foreach ( $field->options as $option ) {
						$_field['options'][] = array( 'name' => sanitize_text_field( $option->name ) );
					}
				}

				$record['fields'][] = $_field;

			}
		}

		return $record;
	}

	/**
	 * Checks the cache for the types before querying again.
	 * @return array Either the types cache variable or get_option result.
	 */
	private function query_types() {

			$this->_types_cache = get_option( 'contenu-types' );
			return $this->_types_cache;
	}

	/**
	 * Searches arrays for ids.
	 * @param  array  $array Array to search.
	 * @param  string $value Id to search for
	 * @return reference     Array reference to edit.
	 */
	private function search_for_id( $array, $value ) {
		$result = false;

		foreach ( $array as $index => $subarray ) {
			if ( isset( $subarray['id'] ) && $subarray['id'] === $value ) {
				$result = $index;
				break;
			}
		}

		return $result;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $contenu       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Contenu_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Contenu_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/contenu-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Contenu_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Contenu_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'angular', plugin_dir_url( __FILE__ ) . 'js/angular.min.js', array( 'jquery' ), '1.4.7', false );
		wp_enqueue_script( 'angular-sortable', plugin_dir_url( __FILE__ ) . 'js/sortable.js', array( 'angular', 'jquery-ui-sortable' ), '1.4.7', false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/contenu-admin.js', array( 'angular-sortable' ), $this->version, false );

		wp_localize_script(
			$this->plugin_name,
			$this->plugin_name . '_ajax',
			array(
				'url' 	=> admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'contenu' ),
			)
		);

	}

	/**
	 * Add the shortcode to CF7.
	 */
	public function custom_add_shortcode_datatable() {
		if ( class_exists( 'WPCF7_Shortcode' ) ) {
			wpcf7_add_shortcode( 'datatable', array( $this, 'custom_datatable_shortcode_handler' ) );
		}
	}

	/**
	 * Renders the table in the form.
	 * @param  array $tag List of things from CF7.
	 * @return string      Output
	 */
	public function custom_datatable_shortcode_handler( $tag ) {

		$tag = new WPCF7_Shortcode( $tag );

		$type = (array) array( $tag->options[0] );

		if ( class_exists( 'Contenu_Public' ) ) {
			$contenu = new Contenu_Public( 'contenu', '1.0' );
			return $contenu->render_datatable( $type, true );
		}
	}


	/**
	 * Register the post type.
	 *
	 * @since    1.0.0
	 */
	public function add_plugins_page() {
		$this->page_hook = add_options_page( 'Content', 'Content', 'manage_options', 'contenu', array( $this, 'plugins_page' ) );

		add_action( 'admin_print_scripts-'.$this->page_hook, array( $this, 'enqueue_styles' ) );
		add_action( 'admin_print_scripts-'.$this->page_hook, array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Register the css to fix wordpress stupid #post-body-content margin.
	 *
	 * @since    1.0.0
	 */
	public function add_post_specific_css() {

		$record = (array) $this->query_types();
		$ids = (array) array();
		$style = (string) '<style>';

		foreach ( $record as $type ) {
			$style .= sprintf( '.post-type-%1$s #post-body-content { display: none; } ', esc_attr( $type['id'] ) );
		}

		$style .= '</style>';

		echo $style;

	}

	/**
	 * Sets the post columns to match the fields.
	 * @return array Column names and value.
	 */
	public function setup_post_columns() {

		$record = (array) $this->query_types();

		foreach ( $record as $type ) {


			$columns 							= (array) array();
			$fields 							= $type['fields'];

			if ( count( $fields ) === 0 ) {
				return;
			}

			$first_three = (array) array();

			$count = 0;
			foreach ( $fields as $field ) {

				if ( ! isset( $field['name'] ) || '' === $field['name'] ) {
					return;
				}

				(string) $name 			= ( $field['name'] ) ? esc_attr( $field['name'] ) : 'Default Name';
				(string) $meta_key 	= $type['id'] . sanitize_title( $name );

				$first_three[ $meta_key ] = $name;

				$count++;
				if ( $count >= 3 ) {
					break;
				}
			}


			$set_function_name = function( $columns ) use ( $first_three ) {
				unset( $columns['author'] );
				unset( $columns['title'] );

				$count = 0;
				foreach ( $first_three as $key => $name ) {

					$columns[ $key ] = __( $name, 'contenu' );

					$count++;
					if ( $count >= 3 ) {
						break;
					}
				}

				$date_data = $columns['date'];
				unset( $columns['date'] );
				$columns['date'] = $date_data;

				return $columns;
			};

			$custom_function_name = function( $column, $post_id ) {
        echo get_post_meta( $post_id, $column, true );
			};

			add_filter( 'manage_' . $type['id'] . '_posts_columns', $set_function_name );
			add_action( 'manage_' . $type['id'] . '_posts_custom_column' , $custom_function_name, 10, 2 );

		}
	}

	/**
	 * Show the admin page.
	 *
	 * @since    1.0.0
	 */
	public function plugins_page() {

		/* Require the plugins HTML file */
		require_once plugin_dir_path( __FILE__ ) . 'partials/contenu-admin-display.php';
	}


	/**
	 * Add plugin options.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_options() {
		add_option( 'contenu-types', array() );
	}

	/**
	 * Register custom post types meta boxes.
	 *
	 * @since    1.0.0
	 */
	public function register_post_type_boxes() {

		$record = (array) $this->query_types();

		foreach ( $record as $type ) {

			$meta_box = new_cmb2_box( array(
				'id'            => $type['id'] . 'metabox',
				'title'         => sprintf( '%1$s Options', $type['name'] ),
				'object_types'  => array( $type['id'] ), // Post type.
				// 'show_on_cb' => 'yourprefix_show_if_front_page', // function should return a bool value
				// 'context'    => 'normal',
				// 'priority'   => 'high',
				// 'show_names' => true, // Show field names on the left
				// 'cmb_styles' => false, // false to disable the CMB stylesheet
				// 'closed'     => true, // true to keep the metabox closed by default.
			) );

			$fields = $type['fields'];

			foreach ( $fields as $field ) {

				if ( ! array_key_exists( 'name', $field ) || ! array_key_exists( 'type', $field ) ) {
					return;
				}

				(string) $name = ( isset( $field['name'] ) ) ? esc_attr( $field['name'] ) : 'Default Name';
				(string) $kind = ( isset( $field['type'] ) ) ? esc_attr( $field['type'] ) : 'text';

				$args = array(
					'name' 	=> $name,
					'id' 		=> $type['id'] . sanitize_title( $name ),
					'type' 	=> $kind,
				);

				if ( in_array( $kind, array( 'select', 'multicheckbox', 'multicheckbox_inline' ) ) ) {
					$args['options'] = array();

					if ( array_key_exists( 'options', $field ) ) {
						foreach ( $field['options'] as $option ) {
							$args['options'][ esc_attr( $option['name'] ) ] = esc_attr( $option['name'] );
						}
					}
				}

				$meta_box->add_field( $args );
			}
		}
	}

	/**
	 * Register custom post types.
	 *
	 * @since    1.0.0
	 */
	public function register_post_types() {

		$record = (array) $this->query_types();

		foreach ( $record as $type ) {

			$slug = sanitize_title( $type['id'] );

			$plural = ( isset( $type['plural'] ) && '' !== $type['plural'] ) ? $type['plural'] : $type['name'];

			$this->post_types[ $slug ] = new Contenu_Post_Type( $slug, __( $type['name'], 'type-creator' ), __( $plural, 'type-creator' ), array( 'menu_icon' => 'dashicons-format-aside', 'supports' => false ) );
		}

	}

	/**
	 * Update Order.
	 *
	 * @since    1.0.0
	 */
	public function update_order() {

		$body = json_decode( file_get_contents( 'php://input' ) );

		$record = get_option( 'contenu-types' );

		$new_record = (array) array();

		foreach ( $body as $index => $id ) {
			foreach ( $record as $value ) {
				if ( $id === $value['id'] ) {
					$new_record[] = $value;
				}
			}
		}

		update_option( 'contenu-types', $new_record );

		wp_send_json_success( $body );
	}

	/**
	 * Save plugin options.
	 *
	 * @since    1.0.0
	 */
	public function save_type() {

		/**
		 * Gets the input contents.
		 * @var array (object)
		 */
		$body = json_decode( file_get_contents( 'php://input' ) );

		if ( ! $this->can_save_data() ) {
			wp_send_json_error( 'Unauthorized Request' );
		}

		$record = get_option( 'contenu-types' );

		if ( isset( $body->id ) ) {

			/**
			 * Ensure that the body->id is a string.
			 * @var string
			 */
			$id = sanitize_text_field( $body->id );

			/**
			 * Uses the search_for_id function to search for the record.
			 * If nothing is found false is returned.
			 * @var stdObjectr
			 */
			$single_record = $this->search_for_id( $record, $id );

			/**
			 * Sets the record values.
			 */
			if ( is_numeric( $single_record ) ) {
				$record[ $single_record ] = $this->sanitize_type( $body );
			} else {
				wp_send_json_error( $body );
			}
		} else {

			/**
			 * If there is not a body->id set, it's a new type.
			 */
			$body->id = (string) uniqid( 'co_' );
			$record[] = $this->sanitize_type( $body );
		}

		update_option( 'contenu-types', $record );

		wp_send_json_success( $body );
	}

	/**
	 * Get plugin options.
	 *
	 * @since    1.0.0
	 */
	public function get_types() {

		if ( ! $this->can_save_data() ) {
			wp_send_json_error( 'Unauthorized Request' );
		}

		// update_option( 'contenu-types', [] );

		$record = get_option( 'contenu-types' );

		wp_send_json_success( $record );
	}

	/**
	 * Get plugin options.
	 *
	 * @since    1.0.0
	 */
	public function delete_type() {

		/**
		 * Gets the input contents.
		 * @var array (object)
		 */
		$body = json_decode( file_get_contents( 'php://input' ) );

		if ( ! $this->can_save_data() ) {
			wp_send_json_error( 'Unauthorized Request' );
		}

		$record = get_option( 'contenu-types' );

		if ( isset( $body ) && array_key_exists( 'id', $body ) ) {

			/**
			 * Ensure that the body->id is a string.
			 * @var string
			 */
			$id = sanitize_text_field( $body->id );

			/**
			 * Filters the $model array removing the item with the id.
			 * @var array
			 */

			$re_record = array_values( array_filter( $record, function( $k ) use ( $id ) {
				if ( $id === $k['id'] ) {
					return false;
				} else {
					return true;
				}
			} ) );

			if ( count( $re_record ) !== count( $record ) ) {
				/**
				 * Delete the post types from the database.
				 */
				global $wpdb;

				$posts_table = $wpdb->posts;

				$query = "
				  DELETE FROM {$posts_table}
				  WHERE post_type = '{$id}'
				";

				$wpdb->query( $query );
			}

			/**
			 * No further sanitization is needed here. Let's save the $model.
			 */
			update_option( 'contenu-types', $re_record );

			wp_send_json_success( $record );

		} else {

			wp_send_json_error( $body );

		}
	}
}
