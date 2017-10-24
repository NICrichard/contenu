<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Contenu
 * @subpackage Contenu/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Contenu
 * @subpackage Contenu/public
 * @author     Your Name <email@example.com>
 */
class Contenu_Public {

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
	 * Finds the type by the id or the name.
	 * @param  array  $record The ID or Name of the collections.
	 * @param  string $value The ID or Name of the collections.
	 * @return object        Whatever it finds or nothing.
	 */
	private function search_for_id_or_name( $record, $value ) {
		$result = false;

		foreach ( $record as $type ) {
			if ( ( isset( $type['id'] ) && $type['id'] === $value ) || ( isset( $type['name'] ) && $type['name'] === $value ) ) {
				$result = $type;
				break;
			}
		}

		return $result;
	}

	/**
	 * Creates a map for the javascript datatable.
	 * @param  array $fields Array of fields from the record.
	 * @param  bool  $form   Whether or not is a form.
	 * @return array         Of columns but of course.
	 */
	private function orderable_map( $fields, $form ) {

		$columns = (array) array();

		foreach ( $fields as $field ) {
			if ( ! $field['private'] ) {
				if ( isset( $field['width'] ) && $field['width'] ) {
					$columns[] = array( 'width' => strval( $field['width'] . '%' ) );
				} else {
					$columns[] = null;
				}
			}
		}

		return $columns;
	}

	/**
	 * Creates a map for the css values.
	 * @param  string $table_id Yup, it's the table id.
	 * @param  array  $fields   Of fields from the record.
	 * @param  bool   $form     If it is in a form.
	 * @return string           Rendered out css.
	 */
	private function css_map( $table_id, $fields, $form ) {

		$columns = (string) '';
		$index = 0;

		foreach ( $fields as $field ) {
			$index++;
			if ( ! $field['private'] ) {
				$columns .= '#' . $table_id . ' td:nth-of-type(' . $index . '):before { content: \'' . $field['name'] . '\'; }';
			}
		}

		return $columns;
	}

	/**
	 * Renders correct information for boolean values.
	 * @param  string $value On or null.
	 * @param  array  $field Field Values.
	 * @return string        Yes or No.
	 */
	private function checkbox_value( $value, $field ) {
		if ( 'checkbox' === $field['type'] ) {
			return ( 'on' === $value ) ? 'Yes' : 'No';
		} else {
			return $value;
		}
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/contenu-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'datatables', plugin_dir_url( __FILE__ ) . 'css/datatables-bootstrap.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( 'datatables', plugin_dir_url( __FILE__ ) . 'js/jquery.datatables.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'bootstrap-datatables', plugin_dir_url( __FILE__ ) . 'js/datatables.bootstrap.js', array( 'datatables' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/contenu-public.js', array( 'datatables' ), $this->version, true );
	}

	/**
	 * Gets the layout and renders the HTML Content.
	 * @param  string $layout Vertical, horizontal for now.
	 * @param  array  $info   Additional information to go along with the layout choice.
	 * @return string         HTML Layout
	 */
	private function layout( $layout, $info ) {

		$img_height = ( 'auto' !== $info['img-height'] ) ? $info['img-height'].'px' : 'auto';
		$image 			= ( ! empty( $info['image'] ) ) ? sprintf( '<a href="%3$s" class="no-icon-link"><img src="%2$s" alt="%1$s" class="img-responsive" style="height: %4$s"></a>', $info['name'], $info['image'], $info['link'], $img_height ) : '';

		if ( 'vertical' === $layout ) {
			$sprint = '
				<div class="row">
					<div class="col-md-12">
						<div class="vl-image">
							%2$s
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<a href="%3$s"><h3 class="vl-heading">%1$s</h3></a>
						<div class="vl-body">
							%4$s
						</div>
					</div>
				</div>';
		} elseif ( 'horizontal' === $layout ) {
			$sprint = '
				<div class="row">
					<div class="col-sm-4">
						<div class="hl-image">
							%2$s
						</div>
					</div>
					<div class="col-sm-8">
						<a href="%3$s"><h3 class="hl-heading">%1$s</h3></a>
						<div class="hl-body">
							%4$s
						</div>
					</div>
				</div>';
		} else {
			$layout = '%1$s%2$s%3$s%4$s';
		}

		return sprintf( $sprint, $info['name'], $image, $info['link'], $info['content'] );
	}

	/**
	 * Renders dat sweet content layout
	 * @param  array $atts Wordpress stuff that gets passed yo.
	 * @return string       Sexy HTML layout.
	 */
	public function render_content( $atts ) {

		$content = '<div class="row content-layout">';

    if ( count( $atts ) < 1 ) {
      return '';
    }

		$layout_id = $atts[0];
		$params = (array) shortcode_atts( array(
			'layout' 	=> 'horizontal',
			'cols' => '2',
			'img-height' => 'auto',
		), $atts );

		$record = get_option( 'contenu-types' );
		$record = $this->search_for_id_or_name( $record, $layout_id );

		$columns_name = 'col-sm-'.( 12 / (int) $params['cols'] );

		$args = array(
			'post_type' 			=> $record['id'],
			'post_status' 		=> 'publish',
			'posts_per_page'	=> -1,
		);

		$query = new WP_Query( $args );
		$posts = $query->get_posts();

		if ( $posts ) {
			$rowCount = 0;
			foreach ( $posts as $post ) {

				$info = array(
					'name'		=> '',
					'link'		=> false,
					'content' => '',
					'image'		=> false,
					'img-height' => $params['img-height'],
				);

				$index = 0;
				foreach( $record['fields'] as $field ) {

					$meta_value = get_post_meta( $post->ID, strtolower( $record['id'].sanitize_title( $field['name'] ) ), true );

					if ( 'text' === $field['type'] ) {

						if ( ! $index ) {
							$info['name'] = $meta_value;
						} elseif ( strpos( $meta_value, 'http' ) !== false ) {
							$info['link'] = $meta_value;
						} else {
							$info['content'] .= '<p>' . $meta_value . '</p>';
						}

					} elseif ( $field['type'] === 'file' ) {
						$info['image'] = $meta_value;
					} else {
						$info['content'] .= '<p>' . $meta_value . '</p>';
					}

					$index++;
				}

				/**
				 * Here we'll call the function that gets the sprintf string. The arguments will go:
				 * image, link, text.
				 */

				$content .= sprintf( '<div class="%1$s">%2$s</div>', $columns_name, $this->layout( $params['layout'], $info ) );

				$rowCount = $rowCount + ( 12 / (int) $params['cols'] );

				if ( 0 === $rowCount % 12 ) {
					$content .= '</div><div class="row content-layout">';
				}
			}
		}
		return $content.'</div>';
	}

	/**
	 * Renders dat sweet table of data.
	 * @param  array $atts Wordpress stuff that gets passed yo.
	 * @param  bool  $form Whether or not is a form.
	 * @return string       Sexy HTML table.
	 */
	public function render_datatable( $atts, $form ) {

		/** Get some definition going. */
		$table						= (string) '';
		
		/** Sort out the attributes from the shortcode. */
		$table_id = $atts[0];
				
		$table_filter = ( isset( $atts['filter'] ) ) ? $atts['filter'] : false;
		$table_limit = ( isset( $atts['limit'] ) ) ? $atts['limit'] : 10;
		$table_sortable = ( isset( $atts['sortable'] ) ) ? $atts['sortable'] : 'true';
				
		// Get the types from the admin side.
		$record = get_option( 'contenu-types' );
		$record = $this->search_for_id_or_name( $record, $table_id );

		$table_css_id			= (string) 'table-'.$record['id'];

		/**
		 * Fail safe.
		 */
		if ( ! $record ) {
			return '';
		}

		// Introduce the table.
		$table = sprintf( '<table id="%1$s" class="table table-striped dataTable no-footer table-hover" data-name="%2$s"><thead><tr>', esc_attr( $table_css_id ), strtolower( $record['name'] ) );

		// Loop and add each table header.
		foreach ( $record['fields'] as $header ) {
			if ( ! $header['private'] ) {
				$table .= sprintf( '<td>%s</td>', esc_attr( $header['name'] ) );
			}
		}

		// Close the table header section.
		$table .= '</tr></thead><tbody>';

		// Close the table.
		$table .= '</tbody></table>';

		if ( isset( $form ) && $form ) {
			$table .= sprintf( '<input type="hidden" name="%1$s">', strtolower( $record['name'] ) );
		}

		// Add the javascript control to the table.
		$table .= sprintf( '
			<script>
				jQuery(document).ready(function($) {
					$("#%1$s").DataTable({
						"serverSide": true,
						"processing": true,
						"pageLength": %9$d,
						"columns" : %5$s,
						"bSort": %10$s,
						"oLanguage": {
				      "sSearch": "Find in results:"
				    },
						"ajax": {
							url:"%2$s",
							type:"POST",
							data: {
								action: "datatable",
								nounce: "%3$s",
								id: "%4$s",
								filter: "%8$s",
								form: %7$d,
							}
						}
					});

					$("head").append("<style type=\'text/css\'>%6$s</style>");
				});
			</script>',
			esc_attr( $table_css_id ),
			admin_url( 'admin-ajax.php' ),
			wp_create_nonce( 'datatable' ),
			esc_attr( $record['id'] ),
			json_encode( $this->orderable_map( $record['fields'], $form ) ),
			$this->css_map( $table_css_id, $record['fields'], $form ),
			( is_bool( $form ) && $form ) ? true : false,
			$table_filter,
			$table_limit,
			$table_sortable
		);

		// Return the table.
		return $table;
	}

	public function datatable_ajax() {

		global $wp_query;

		// Check the nounce key.
		check_ajax_referer( 'datatable', 'nounce' );
		header( 'Content-Type: application/json' );

		$body = json_decode( file_get_contents( 'php://input' ) );

		// Get and sanatize variables from datatable ajax.
		$table_id				= (string) sanitize_text_field( $_POST['id'] );
		$table_draw			= (int) sanitize_text_field( $_POST['draw'] );
		$table_start		= (int) sanitize_text_field( $_POST['start'] );
		$table_length		= (int) sanitize_text_field( $_POST['length'] );
		$table_form			= (boolean) $_POST['form'];
		$table_search		= (string) sanitize_text_field( $_POST['search']['value'] );
		$table_filter		= (string) sanitize_text_field( $_POST['filter'] );
		$table_posts		= (int) 0;
		$table_total		= (int) 0;
		$table_data			= (array) array();
		$table_order		= (string) sanitize_text_field( $_POST['order'][0]['dir'] );
		$table_sort			= (int) sanitize_text_field( $_POST['order'][0]['column'] );

		$record = get_option( 'contenu-types' );
		$table_record = $this->search_for_id_or_name( $record, $table_id );

		$mori = function () use ( $table_draw, $table_record ) {
			echo wp_json_encode( array(
				'data' 						=> array(),
				'recordsTotal' 		=> (int) 0,
				'recordsFiltered' => 0,
				'draw' 						=> $table_draw,
				'mori'						=> true,
				'record'					=> $table_record,
			) );

			exit;
		};

		if ( ! $table_record ) {
			$mori();
		}

		$table_filter = explode( ':', $table_filter );

		// Clear up the confusion.
		if ( ! isset( $table_record['fields'][ $table_sort ]['private'] ) || $table_record['fields'][ $table_sort ]['private'] ) {
			$table_sort	= $table_record['fields'][ $table_sort ]['name'];
		} else {
			foreach ( $table_record['fields'] as $field ) {
				if ( ! $field['private'] ) {
					$table_sort	= $field['name'];
					break;
				}
			}
		}

		$args = array(
			'post_type' 			=> $table_record['id'],
			'posts_per_page' 	=> $table_length,
			'offset' 					=> $table_start,
			'post_status' 		=> 'publish',
			'order'						=> strtoupper( $table_order ),
			'meta_query'			=> array(),
		);

		$search_query = false;

		if ( $table_search ) {

			$search_query = array(
				'value' => $table_search,
				'compare' => 'LIKE',
			);

		}

		if ( $table_filter && 2 === count( $table_filter ) && '' !== $table_filter[1] ) {
			$search_filter = array(
				'key' => $table_record['id'].sanitize_title( $table_filter[0] ),
				'value' => $table_filter[1],
			);

			if ( $search_query ) {
				$search_query = array(
					'relation' => 'or',
					$search_query,
					$search_filter
				);
			} else {
				$search_query = $search_filter;
			}
		}

		if ( $search_query ) {
			$args['meta_query'] = $search_query;
		}

		$args['orderby'] 	= 'meta_value';
		$args['meta_key'] = strtolower( $table_record['id'].sanitize_title( $table_sort ) );

		$query = new WP_Query( $args );
		$posts = $query->get_posts();

		$table_posts = $query->post_count;
		$table_total = $query->found_posts;

		if ( $posts ) {
			foreach ( $posts as $post ) {
				$current_row = array();

				foreach ( $table_record['fields'] as $field ) {
					if ( ! $field['private'] ) {

						$meta_value = get_post_meta( $post->ID, strtolower( $table_record['id'].sanitize_title( $field['name'] ) ), true );

						$value = sprintf('<span class="contenu-field-%1$s">%2$s</span>',
							sanitize_title( $field['name'] ),
							$this->checkbox_value( $meta_value, $field )
						);

						foreach ( $table_record['fields'] as $field_r ) {
							if ( $field_r['private'] && $field_r['combined'] === $field['name'] ) {

								$meta_value_r = get_post_meta( $post->ID, strtolower( $table_record['id'].sanitize_title( $field_r['name'] ) ), true );

								if ( isset( $field_r['relation'] ) && $field_r['relation'] === 'link' && ! empty( $meta_value_r ) ) {
									$value = sprintf('<a class="contenu-field-%1$s" href="%2$s">%3$s</a>',
										sanitize_title( $field_r['name'] ),
										esc_url( $this->checkbox_value( $meta_value_r, $field_r ) ),
										$value
									);
								} else {
									$value .= sprintf('<br><span class="contenu-field-%1$s">%2$s</span>',
										sanitize_title( $field_r['name'] ),
										$this->checkbox_value( $meta_value_r, $field_r )
									);
								}


							}
						}

						if ( $table_form && isset( $field['value'] ) && $field['value'] ) {
							if ( isset( $table_record['single'] ) && $table_record['single'] ) {

								$current_row[] = sprintf('<div class="radio"><label><input type="radio" value="%1$s" name="%2$s">%3$s</label></div>',
									esc_attr( $meta_value ),
									strtolower( esc_attr( $table_record['name'] ) ),
									$value
								);

							} else {

								$current_row[] = sprintf('<div class="checkbox"><label><input type="checkbox" value="%1$s" name="%2$s[]">%3$s</label></div>',
									esc_attr( $meta_value ),
									strtolower( esc_attr( $table_record['name'] ) ),
									$value
								);

							}
						} else {
							$current_row[] = $value;
						}
					}
				}

				$table_data[] = $current_row;
			}
		}

		// Response output.
		echo wp_json_encode( array(
			'data' 						=> $table_data,
			'recordsTotal' 		=> (int) $table_total,
			'recordsFiltered' => $table_total,
			'draw' 						=> $table_draw,
			'args'						=> $args,
		) );

		// IMPORTANT: don't forget to "exit".
		wp_reset_postdata();
		exit;
	}
}
