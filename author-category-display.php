<?php
/*
Plugin Name: Author Category Display
Description: Displays Author that sell products (works on category pages)
*/
/* Start Adding Functions Below this Line */

// Register and load the widget
function comprar_author_category_display() {
	register_widget( 'comprar_author_cat_widget' );
}
add_action( 'widgets_init', 'comprar_author_category_display' );

// Creating the widget 
class comprar_author_cat_widget extends WP_Widget {

function __construct() {
parent::__construct(

// Base ID of your widget
'comprar_author_cat_widget', 

// Widget name will appear in UI
__('Comprar Author Category Display Widget', 'wpb_widget_domain'), 

// Widget description
array( 'description' => __( 'Place this widget in category page to output distributors selling products', 'wpb_widget_domain' ), ) 
);
}

// Creating widget front-end

public function widget() {
if ( is_tag() ) {
    $current_tag = get_term( $GLOBALS['wp_the_query']->get_queried_object() );
    $transient_name = 'wpse241557_' . md5( json_encode( $current_tag ) );

    // Check if transient is set
    if ( false === ( $user_query = get_transient( $transient_name ) ) ) {

        $args = [
            'wpse_post_author'       => true, // To trigger our filter
            'posts_per_page'         => -1,
            'orderby'                => 'author',
            'order'                  => 'ASC',
            'suppress_filters'       => false, // Allow filters to alter query
            'cache_results'          => false, // Do not cache posts
            'update_post_meta_cache' => false, // Do not cache custom field data
            'update_post_term_cache' => false, // Do not cache post terms
            'tax_query'              => [
                [
                    'taxonomy'         => $current_tag->taxonomy,
                    'terms'            => $current_tag->term_id,
                    'include_children' => true
                ]
            ]
        ];
        $posts_array = get_posts( $args );

        $user_query = false;

        if ( $posts_array ) {
            // Get all the post authors from the posts
            $post_author_ids = wp_list_pluck( $posts_array, 'post_author' );

            // Get a unique array of ids
            $post_author_ids = array_unique( $post_author_ids );

            $user_args = [
                'include' => $post_author_ids
            ];
            $user_query = new \WP_User_Query( $user_args );
        }

        // Set the transient for 3 days, adjust as needed
        set_transient( $transient_name, $user_query, 72 * HOUR_IN_SECONDS );
   }

    if (    false !== $user_query
         && $user_query->results 
    ) {
    	$category_name = $current_tag->name;
    	echo '<h3 id="losdistribuidores">Líderes mayoristas que venden '.$category_name.'</h3>';
        foreach ( $user_query->results as $user ) {
            $name = $user->display_name;
            $link = $user->user_url;
            $profile = $user->user_nicename;
            
            
            echo '<p>'.$name.': <a href="'.$link.'" target="_blank" title="'.$name.'" style="color:#c4001a;">website</a> | <a href="/author/'.$profile.'" title="What else '.$name.' sells" style="color:#c4001a;">inventario</a></p>';
          
 

        }
    }
}
if ( is_category() ) {
    $current_category = get_term( $GLOBALS['wp_the_query']->get_queried_object() );
    $transient_name = 'wpse331557_' . md5( json_encode( $current_category ) );

    // Check if transient is set
    if ( false === ( $user_query = get_transient( $transient_name ) ) ) {

        $args = [
            'wpse_post_author'       => true, // To trigger our filter
            'posts_per_page'         => -1,
            'orderby'                => 'author',
            'order'                  => 'ASC',
            'suppress_filters'       => false, // Allow filters to alter query
            'cache_results'          => false, // Do not cache posts
            'update_post_meta_cache' => false, // Do not cache custom field data
            'update_post_term_cache' => false, // Do not cache post terms
            'tax_query'              => [
                [
                    'taxonomy'         => $current_category->taxonomy,
                    'terms'            => $current_category->term_id,
                    'include_children' => true
                ]
            ]
        ];
        $posts_array = get_posts( $args );

        $user_query = false;

        if ( $posts_array ) {
            // Get all the post authors from the posts
            $post_author_ids = wp_list_pluck( $posts_array, 'post_author' );

            // Get a unique array of ids
            $post_author_ids = array_unique( $post_author_ids );



            $user_args = [
                'include' => $post_author_ids
            ];
            $user_query = new \WP_User_Query( $user_args );
        }

        // Set the transient for 3 days, adjust as needed
        set_transient( $transient_name, $user_query, 72 * HOUR_IN_SECONDS );
   }

    if (    false !== $user_query
         && $user_query->results 
    ) {
        $category_name = $current_category->name;
        echo '<h3 id="losdistribuidores">Líderes mayoristas que venden '.$category_name.'</h3>';
        foreach ( $user_query->results as $user ) {
            $name = $user->display_name;
            $link = $user->user_url;
            $profile = $user->user_nicename;

            
// var_dump($user);
            
            echo '<p>'.$name.': <a href="'.$link.'" target="_blank" title="'.$name.'" style="color:#c4001a;">website</a> | <a href="/author/'.$profile.'" title="Que mas vende '.$name.' sells" style="color:#c4001a;">inventario</a></p>';
          
 

        }
    }
}
}
		
// Widget Backend 
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'New title', 'wpb_widget_domain' );
}
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php 
}
	
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
return $instance;
}
} // Class wpb_widget ends here

// Creating query to display author that sell product on category page

add_filter( 'posts_fields', function ( $fields, \WP_Query $q ) use ( &$wpdb )
{
    remove_filter( current_filter(), __FUNCTION__ );

    // Only target a query where the new wpse_post_author parameter is set to true
    if ( true === $q->get( 'wpse_post_author' ) ) {
        // Only get the post_author column
        $fields = "
            $wpdb->posts.post_author
        ";
    }

    return $fields;
}, 10, 2);

add_action( 'transition_post_status', function () use ( &$wpdb )
{
    $wpdb->query( "DELETE FROM $wpdb->options WHERE `option_name` LIKE ('_transient%_wpse241557_%')" );
    $wpdb->query( "DELETE FROM $wpdb->options WHERE `option_name` LIKE ('_transient_timeout%_wpse241557_%')" );
    $wpdb->query( "DELETE FROM $wpdb->options WHERE `option_name` LIKE ('_transient%_wpse331557_%')" );
    $wpdb->query( "DELETE FROM $wpdb->options WHERE `option_name` LIKE ('_transient_timeout%_wpse331557_%')" );
});





/* Stop Adding Functions Below this Line */
?>