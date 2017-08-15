<?php
/**
 * Class for the sidebar login widget.
 *
 * @since 2.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class widget_wpmemwidget extends WP_Widget {

	/**
	 * Sets up the WP-Members login widget.
	 */
	function __construct() {
		parent::__construct(
			'widget_wpmemwidget',
			'WP-Members Login',
			array( 
				'classname'   => 'wp-members', 
				'description' => __( 'Displays the WP-Members sidebar login.', 'wp-members' ), 
			)
		);
	}

	/**
	 * Displays the WP-Members login widget settings 
	 * controls on the widget panel.
	 *
	 * @param array $instance
	 */
	function form( $instance ) {
	
		/* Default widget settings. */
		$defaults = array( 
			'title'       => __('Login Status', 'wp-members'),
			'redirect_to' => '',
		);
		$instance = wp_parse_args( ( array ) $instance, $defaults );
		
		/* Title input */ ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'wp-members'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:95%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'redirect_to' ); ?>"><?php _e('Redirect to (optional):', 'wp-members'); ?></label>
			<input id="<?php echo $this->get_field_id( 'redirect_to' ); ?>" name="<?php echo $this->get_field_name( 'redirect_to' ); ?>" value="<?php echo $instance['redirect_to']; ?>" style="width:95%;" />
		</p>
		<?php
	}

	/**
	 * Update the WP-Members login widget settings.
	 *
	 * @param  array $new_instance
	 * @param  array $old_instance
	 * @return array $instance
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Strip tags for title to remove HTML.
		$instance['title']       = strip_tags( $new_instance['title'] );
		$instance['redirect_to'] = strip_tags( $new_instance['redirect_to'] );

			return $instance;
	}

	/**
	 * Displays the WP-Members login widget.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {

		// Get the Widget Title
		$title = apply_filters('widget_title', ( array_key_exists( 'title', $instance ) ) ? $instance['title'] : __( 'Login Status', 'wp-members' ), $instance, $this->id_base);
		$redirect_to = ( array_key_exists( 'redirect_to', $instance ) ) ? $instance['redirect_to'] : '';

		echo $args['before_widget'];
		/**
		 * Filter the widget ID.
		 *
		 * @since ?.?
		 *
		 * @param string The ID for the sidebar widget.
		 */
		echo '<div id="' . apply_filters( 'wpmem_widget_id', 'wp-members' ) . '">';

			/**
			 * Filter the widget title.
			 *
			 * @since ?.?
			 *
			 * @param string $title The widget title.
			 */
			echo $args['before_title'] . apply_filters( 'wpmem_widget_title', $title ) . $args['after_title'];

			// The Widget
			if ( function_exists( 'wpmem_do_sidebar' ) ) { 
				wpmem_do_sidebar( $redirect_to ); 
			}

		echo '</div>';
		echo $args['after_widget'];
	}
}