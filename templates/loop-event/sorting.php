<?php
/**
 * Events ordering options
 * 
 * Override this template by copying it to yourtheme/loop-event/sorting.php
 *
 * @author 	Digital Factory
 * @package Events Maker/Templates
 * @since 	1.2.0
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $wp_query;

if ($wp_query->found_posts == 1)
	return;

?>

<form class="event-maker-ordering" method="get">
	
	<select name="orderby" class="orderby">
		
		<?php
		$orderby = apply_filters('em_events_orderby', array(
			'menu_order'	=> __('Default sorting', 'events-maker'),
			'popularity'	=> __('Sort by popularity', 'events-maker'),
			'date'			=> __('Sort by newness', 'events-maker'),
			'date-desc'		=> __('Sort by newness', 'events-maker'),
			'title'			=> __('Sort by price: low to high', 'events-maker'),
			'title-desc'	=> __('Sort by price: high to low', 'events-maker')
		));

		foreach ($orderby as $id => $name)
			echo '<option value="' . esc_attr($id) . '" ' . selected($orderby, $id, false) . '>' . esc_attr($name) . '</option>';
		?>
		
	</select>
	
	<?php
		// Keep query string vars intact
		foreach ($_GET as $key => $val)
		{	
			if ('orderby' === $key || 'submit' === $key)
				continue;
			
			if (is_array($val))
			{
				foreach($val as $_val)
					?>
					<input type="hidden" name="<?php echo esc_attr($key) . '[]'; ?>" value="<?php echo esc_attr($_val); ?>" />
					<?php
			}
			else
			{
				?>
				<input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($val); ?>" />
				<?php
			}
		}
	?>
</form>