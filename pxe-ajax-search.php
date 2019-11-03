<?php
/**
 * Plugin Name:       Ajax Search
 * Plugin URI:        
 * Description:       Permite crear Buscador basado en Ajax
 * Version:           1.0.0
 * Author:            Vicente Scópise
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class PXE_Ajax_Search_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct( 
            'pxe-ajax-search', 
            'Ajax Search', 
            array( 
                'classname' => 'pxe_ajax_search',
                'description' => 'Buscador Ajax',
            ) 
        );
        if ( is_active_widget( false, false, $this->id_base ) ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
        }
        add_action( 'wp_ajax_nopriv_pxe_ajax_search', array( $this, 'ajax_callback'));
        add_action( 'wp_ajax_pxe_ajax_search', array( $this, 'ajax_callback'));
    }

    public function form( $instance ) {	
        $defaults = array('title' => '');
        extract( wp_parse_args( ( array ) $instance, $defaults ) ); 
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Título</label>
            <input class="widefat" 
                   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                   type="text" 
                   value="<?php echo esc_attr( $title ); ?>" 
            />
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
                
        $instance['title'] = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
        return $instance;
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        ?>
        <?php if ('' !== $instance['title']) : ?>
        <h3 class="widget-title"><?php echo $instance['title'] ?></h3>
        <?php endif; ?>
        <div class="input-container">
            <input type="search" placeholder="¿Qué quiere buscar?" class="search-field form-control" />
            <div class="icon-container">
                <i class="loader"></i>
                <i class="close"></i>
            </div>
        </div>
        <div class="ajax-results"></div>
        <?php
        echo $args['after_widget'];
    }

    function enqueue_script() {
        wp_enqueue_script( 
            'pxe_ajax_search', 
            plugins_url( '/includes/pxe-ajax-search.js', __FILE__ ), 
            array('jquery') 
        );
        wp_enqueue_style(
            'pxe_ajax_search_styles',
            plugins_url( '/includes/pxe-ajax-search-styles.css', __FILE__ )
        );
        wp_localize_script( 'pxe_ajax_search', 'ajax_search_object', array(
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'pxe_ajax_search' )
        ));
    }
        
    function ajax_callback() {
        $nonce = filter_input( INPUT_POST, 'nonce' );
        if ( ! wp_verify_nonce( $nonce, 'pxe_ajax_search' ) ) { 
            wp_send_json_error();
        }
        $input = filter_input( INPUT_POST, 'input' );
        $query = new WP_Query( array( 'posts_per_page' => 10, 's' => $input ));
        $results = array();
        if ($query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
            $title = get_the_title();
            $link = get_the_permalink();
            array_push($results, array( 'title' => $title, 'link' => $link ) );
        endwhile; endif;
        wp_send_json( array(
            'results' => $results
        ));
    }
    
}

// Register the widget
function PXE_register_Ajax_Search() {
	register_widget( 'PXE_Ajax_Search_Widget' );
}
add_action( 'widgets_init', 'PXE_register_Ajax_Search' );