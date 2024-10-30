<?php
/*
    HDTasks Projects Page
*/
?>
	<div id = "hdt_meta_forms">
		<div id = "hdt_message_wrap"><div id = "hdt_message"></div></div>
		
		<div id = "hdt_wrapper">
			<div id="hdt_form_wrapper">
				<div class = "hdt_tab hdt_tab_active">
					<h1>
						HDTasks - Projects
					</h1>					
					<input type="text" name="hdt_new_project_name" id="hdt_new_project_name" class="hdt_input" placeholder="add new project">
					<div class="hdt_input_notification"><span></span>Press "ENTER" to add the project</div>
					
					<p>
					HDTasks is a 100% free plugin developed in my spare time, and as such, I get paid in nothing but good will and positive reviews. If you are enjoying HD Quiz and would like to show your support, please consider contributing to my <a href="https://www.patreon.com/harmonic_design" target="_blank">patreon page</a> to help continued development, or <a href = "https://wordpress.org/support/plugin/hdtasks/reviews/#new-post">write an honest review here</a>. Every little bit helps, and I am fuelled by <img draggable="false" class="emoji" alt="☕" src="https://s.w.org/images/core/emoji/11.2.0/svg/2615.svg">.
				</p>
					
					<div id = "hdt_list_projects">
							<?php
							$taxonomy = 'hdt_projects';
							$term_args=array(
							  'hide_empty' => false,
							  'orderby' => 'name',
							  'order' => 'ASC'
							);
							$tax_terms = get_terms($taxonomy, $term_args);

							if (! empty($tax_terms) && ! is_wp_error($tax_terms)) {
								foreach ($tax_terms as $tax_terms) {
									?>

									<div class = "hdt_project_item hdt_project_item_<?php echo $tax_terms->term_id; ?>" data-id = "<?php echo $tax_terms->term_id; ?>">
										<div class = "hdt_project_title">
											<?php echo mb_strimwidth($tax_terms->name, 0, 50, "..."); ?>
										</div>
										<div class = "hdt_project_link hdt_icons">
											<a href = "<?php echo get_term_link($tax_terms->term_id, 'hdt_projects') ?>" target = "_blank"><span class="dashicons dashicons-admin-links" title = "View project"></span></a>
										</div>
										<div class = "hdt_project_settings hdt_icons">
											<span class="dashicons dashicons-admin-settings" data-id = "<?php echo $tax_terms->term_id; ?>" title = "Configure project"></span>
										</div>
										<div class = "hdt_project_delete hdt_icons">
											<span class="dashicons dashicons-trash" data-id = "<?php echo $tax_terms->term_id; ?>" title = "Delete this project and all attached tasks"></span>
										</div>
									</div>
							<?php
								}
							}
							?>				
					</div>					
				</div>
			</div>
		</div>
	</div>
	<?php wp_nonce_field('hdt_admin_nonce', 'hdt_admin_nonce'); ?>