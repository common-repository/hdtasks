<?php
/*
 * Basic HDT ajax functions
 */

/* get task data
------------------------------------------------------- */
function hdt_get_task()
{
    $hdt_nonce = sanitize_text_field($_POST['hdt_nonce']);
    if (wp_verify_nonce($hdt_nonce, 'hdt_nonce') != false) {
        $task_id = intval($_POST['task_id']);
        if ($task_id != null && get_post_type($task_id) == 'hdt_tasks') {
            $task = new stdClass;
            $task->taskID = $task_id;
            $task->title = get_the_title($task_id);
            $task->status = sanitize_text_field(get_post_meta($task_id, 'hdt_status', true));
            $task->author = sanitize_text_field(get_post_meta($task_id, 'hdt_author', true));
            $task->dueDate = sanitize_text_field(get_post_meta($task_id, 'hdt_date', true));

            $content_post = get_post($task_id);
            $content = $content_post->post_content;
            $content = apply_filters('the_content', wp_kses_post($content));
            $content = str_replace(']]>', ']]&gt;', $content);
            $task->description = $content;

            // now get task comments
            $task_comments = array();
            $queried_object = get_queried_object();
            $term_id = $queried_object->term_id;
            $args = array(
                'post_type' => array('hdt_tasks'),
                'nopaging' => true,
                'posts_per_page' => '-1',
                'post_parent' => $task_id,
                'orderby' => 'date',
                'order' => 'DESC',
            );

            $query = new WP_Query($args);
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $hdt_id = get_the_ID();
                    $author = sanitize_text_field(get_post_meta($hdt_id, 'hdt_author', true));
                    $comment = get_the_content();
                    $comment = apply_filters('the_content', $comment);
                    $comment = str_replace(']]>', ']]&gt;', $comment);
					$hdt_publish_date = get_the_date();
                    array_push($task_comments, array($hdt_id, $author, $hdt_publish_date, $comment));
                }
            }
            wp_reset_postdata();
            $task->comments = $task_comments;
            $json = json_encode($task);
            echo $json;
        } else {
            echo 'permission denied. This post does not belong to HDTasks';
        }
    } else {
        echo 'Permission denied. Nonce failed';
    }
    die();
}
add_action('wp_ajax_hdt_get_task', 'hdt_get_task');
add_action('wp_ajax_nopriv_hdt_get_task', 'hdt_get_task');

/* Update the task order / priority
------------------------------------------------------- */
function hdt_update_task_order()
{
    $hdt_nonce = sanitize_text_field($_POST['hdt_nonce']);
    if (wp_verify_nonce($hdt_nonce, 'hdt_nonce') != false) {
        $tasks = sanitize_text_field($_POST['tasks']);
        $tasks = json_decode(stripslashes($tasks), false);
        if ($tasks) {
            foreach ($tasks as $q) {
                $taskID = intval($q[0]);
                if ($taskID != null && get_post_type($taskID) == 'hdt_tasks') {
                    $post = array();
                    $post['ID'] = $taskID;
                    $post['menu_order'] = intval($q[1]);
                    wp_update_post($post);
                }
            }
        }
    } else {
        echo 'Permission denied. Nonce failed';
    }
    die();
}
add_action('wp_ajax_hdt_update_task_order', 'hdt_update_task_order');
add_action('wp_ajax_nopriv_hdt_update_task_order', 'hdt_update_task_order');

/* Save a task
------------------------------------------------------- */
function hdt_add_new_task()
{
    $hdt_nonce = sanitize_text_field($_POST['hdt_nonce']);
    if (wp_verify_nonce($hdt_nonce, 'hdt_nonce') != false) {
        $data = stripslashes(html_entity_decode($_POST['data']));
        $data = json_decode($data);
        $task_id = intval($data->id);
        $project_id = intval($_POST['project']);

        // sanitize data
        $task_title = sanitize_text_field($data->title);
        $task_status = sanitize_text_field($data->status);
        $task_description = wp_kses_post($data->description);
        $task_author = sanitize_text_field($data->author);
        $task_date = sanitize_text_field($data->date);

        if ($task_id == "" || $task_id == null || $task_id == 0) {

            $task_position = 0;
            $term = get_term($project_id, "hdt_projects");
            $task_position = $term->count - 1;

            // this is a new task
            $post_information = array(
                'post_title' => $task_title,
                'post_content' => $task_description,
                'post_type' => 'hdt_tasks',
                'menu_order' => $task_position,
                'post_status' => 'publish',
            );
            $post_id = wp_insert_post($post_information);
            echo $post_id;

            // set project term
            wp_set_post_terms($post_id, [$project_id], 'hdt_projects');
            // add meta
            add_post_meta($post_id, 'hdt_author', $task_author, false);
            add_post_meta($post_id, 'hdt_status', $task_status, false);
            add_post_meta($post_id, 'hdt_date', $task_date, false);

        } else {
            // this is an old task
            if ($task_id != null && get_post_type($task_id) == 'hdt_tasks') {
                echo $task_id;
                update_post_meta($task_id, 'hdt_author', $task_author, false);
                update_post_meta($task_id, 'hdt_status', $task_status, false);
                update_post_meta($task_id, 'hdt_date', $task_date, false);
                // update post title too
                $hdt_post = array(
                    'ID' => $task_id,
                    'post_title' => $task_title,
                    'post_content' => $task_description,
                );
                wp_update_post($hdt_post);
            } else {
                echo 'permission denied. This post does not belong to HDTasks';
            }
        }
    } else {
        echo 'Permission denied. Nonce failed';
    }
    die();
}
add_action('wp_ajax_hdt_add_new_task', 'hdt_add_new_task');
add_action('wp_ajax_nopriv_hdt_add_new_task', 'hdt_add_new_task');

/* Save a comment
------------------------------------------------------- */
function hdt_add_new_task_comment()
{
    $hdt_nonce = sanitize_text_field($_POST['hdt_nonce']);
    if (wp_verify_nonce($hdt_nonce, 'hdt_nonce') != false) {
        $data = stripslashes(html_entity_decode($_POST['data']));
        $data = json_decode($data);
        $task_id = intval($data->id);
        $task_parent = intval($data->parent);
        $project_id = intval($_POST['project']);

        // sanitize data
        $task_description = wp_kses_post($data->description);
        $task_author = sanitize_text_field($data->author);

        if ($task_id == "" || $task_id == null || $task_id == 0) {

            // this is a new task
            $post_information = array(
                'post_title' => "Reply to task",
                'post_content' => $task_description,
                'post_type' => 'hdt_tasks',
                'menu_order' => $task_position,
                'post_status' => 'publish',
                'post_parent' => $task_parent,
            );
            $post_id = wp_insert_post($post_information);
            echo $post_id;

            // set project term
            wp_set_post_terms($post_id, [$project_id], 'hdt_projects');
            // add meta
            add_post_meta($post_id, 'hdt_author', $task_author, false);

        } else {
            // this is an old task
            if ($task_id != null && get_post_type($task_id) == 'hdt_tasks') {
                echo $task_id;
                update_post_meta($task_id, 'hdt_author', $task_author, false);
                // update post title too
                $hdt_post = array(
                    'ID' => $task_id,
                    'post_content' => $task_description,
                );
                wp_update_post($hdt_post);
            } else {
                echo 'permission denied. This post does not belong to HDTasks';
            }
        }
    } else {
        echo 'Permission denied. Nonce failed';
    }
    die();
}
add_action('wp_ajax_hdt_add_new_task_comment', 'hdt_add_new_task_comment');
add_action('wp_ajax_nopriv_hdt_add_new_task_comment', 'hdt_add_new_task_comment');

/* Add new project
------------------------------------------------------- */
function hdt_add_new_project()
{
    $hdt_nonce = sanitize_text_field($_POST['hdt_admin_nonce']);
    if (wp_verify_nonce($hdt_nonce, 'hdt_admin_nonce') != false) {
        if (current_user_can('edit_others_pages')) {
            $project_id = intval($_POST['project_id']);
            $project_name = sanitize_text_field($_POST['project_name']);

            if ($project_id == "" || $project_id == null || $project_id == 0) {
                // new project
                $project = wp_insert_term(
                    $project_name, // the term
                    'hdt_projects' // the taxonomy
                );
                $data = array();
                array_push($data, $project["term_id"]);
                array_push($data, $project_name);
                array_push($data, get_term_link($project["term_id"], 'hdt_projects'));
                $data = json_encode($data, JSON_FORCE_OBJECT);
                echo $data;
            } else {
                // this is an old project
            }
        } else {
            echo 'Permission denied. You do not have permission';
        }
    } else {
        echo 'Permission denied. Nonce failed';
    }
    die();
}
add_action('wp_ajax_hdt_add_new_project', 'hdt_add_new_project');

/* Delete project and all attached tasks
------------------------------------------------------- */
function hdt_delete_project()
{
    $hdt_nonce = sanitize_text_field($_POST['hdt_admin_nonce']);
    if (wp_verify_nonce($hdt_nonce, 'hdt_admin_nonce') != false) {
        if (current_user_can('edit_others_pages')) {
            $project_id = intval($_POST['project_id']);

            if ($project_id == "" || $project_id == null || $project_id == 0) {
                echo 'Permission denied. There is an issue with your project ID';
            } else {
                // delete all attached tasks

                $args = array(
                    'post_type' => array('hdt_tasks'),
                    'nopaging' => true,
                    'posts_per_page' => '-1',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'hdt_projects',
                            'field' => 'term_id',
                            'terms' => $project_id,
                        ),
                    ),
                );

                $query = new WP_Query($args);
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        wp_delete_post(get_the_id(), true);
                    }
                }
                wp_reset_postdata();
                wp_delete_term($project_id, 'hdt_projects');
            }
        } else {
            echo 'Permission denied. You do not have permission';
        }
    } else {
        echo 'Permission denied. Nonce failed';
    }
    die();
}
add_action('wp_ajax_hdt_delete_project', 'hdt_delete_project');

/* Show project edit screen
------------------------------------------------------- */
function hdt_edit_project()
{
    $hdt_nonce = sanitize_text_field($_POST['hdt_admin_nonce']);
    if (wp_verify_nonce($hdt_nonce, 'hdt_admin_nonce') != false) {
        if (current_user_can('edit_others_pages')) {
            $project_id = intval($_POST['project_id']);
            if ($project_id == "" || $project_id == null || $project_id == 0) {
                echo 'Permission denied. There is an issue with your project ID';
            } else {
                $project = get_term($project_id, 'hdt_projects');
                ?>
					<div class = "hdt_tab hdt_tab_active">
						<p>
							Additional options and settings will become avaialable as HDTasks matures. Please check the upcoming feature list to see what's in the works, or leave a request on the <a href = "https://wordpress.org/support/plugin/hdtasks/" target = "_blank">support forum</a>.
						</p>

						<h2 style = "font-size: 1.6em;">Editing <?php echo $project->name; ?></h2>
						<div class = "hdt_one_half">
							<div class = "hdt_row">
								<label for = "hdt_project_name">Project Name</label>
								<input type = "text" id = "hdt_project_name" name = "hdt_project_name" class = "hdt_input" value = "<?php echo $project->name; ?>"/>
							</div>
						</div>
						<div class = "hdt_one_half hdt_last">
							<div class = "hdt_row">
								<label for = "hdt_project_slug">Project Slug</label>
								<input type = "text" id = "hdt_project_slug" name = "hdt_project_slug" class = "hdt_input"  value = "<?php echo $project->slug; ?>"/>
							</div>
						</div>
						<div class = "hdt_clear"></div>
						<div class = "hdt_row">
							<p style = "text-align:right">
								<button id = "hdt_update_project" class = "hdt_button hdt_continue" data-id = "<?php echo $project_id; ?>">SAVE</button>
							</p>
						</div>
					</div>
				<?php
}
        } else {
            echo 'Permission denied. You do not have permission';
        }
    } else {
        echo 'Permission denied. Nonce failed';
    }
    die();
}
add_action('wp_ajax_hdt_edit_project', 'hdt_edit_project');

/* Update project
------------------------------------------------------- */
function hdt_edit_save_project()
{
    $hdt_nonce = sanitize_text_field($_POST['hdt_admin_nonce']);
    if (wp_verify_nonce($hdt_nonce, 'hdt_admin_nonce') != false) {
        if (current_user_can('edit_others_pages')) {
            $project_id = intval($_POST['project_id']);
            $project_data = stripslashes(html_entity_decode($_POST['data']));
            $project_data = json_decode($project_data);
            $projectName = sanitize_text_field($project_data->projectName);
            $projectSlug = sanitize_title($project_data->projectSlug);

            if ($project_id == "" || $project_id == null || $project_id == 0) {
                echo 'Permission denied. There is no project id';
            } else {
                wp_update_term($project_id, 'hdt_projects', array(
                    'name' => $projectName,
                    'slug' => $projectSlug,
                ));
            }
        } else {
            echo 'Permission denied. You do not have permission';
        }
    } else {
        echo 'Permission denied. Nonce failed';
    }
    die();
}
add_action('wp_ajax_hdt_edit_save_project', 'hdt_edit_save_project');

/* Print custom date picker
------------------------------------------------------- */
function hdt_get_date_picker()
{
    ?>
<div class="hdt_date_picker_wrap">
  <div class="hdt_date_picker">
    required by date
  </div>
  <div class="hdt_date_picker_model">
	<div>
    <div class="hdt_date_picker_model_month hdt_model_item">
      <div class="hdt_date_item" data-id="01">
        January
      </div>
      <div class="hdt_date_item" data-id="02">
        February
      </div>
      <div class="hdt_date_item" data-id="03">
        March
      </div>
      <div class="hdt_date_item" data-id="04">
        April
      </div>
      <div class="hdt_date_item" data-id="05">
        May
      </div>
      <div class="hdt_date_item" data-id="06">
        June
      </div>
      <div class="hdt_date_item" data-id="07">
        July
      </div>
      <div class="hdt_date_item" data-id="08">
        August
      </div>
      <div class="hdt_date_item" data-id="09">
        September
      </div>
      <div class="hdt_date_item" data-id="10">
        October
      </div>
      <div class="hdt_date_item" data-id="11">
        November
      </div>
      <div class="hdt_date_item" data-id="12">
        December
      </div>
    </div>
    <div class="hdt_date_picker_model_day hdt_model_item">
      <div class="hdt_date_item" data-id="01">01</div>
      <div class="hdt_date_item" data-id="02">02</div>
      <div class="hdt_date_item" data-id="03">03</div>
      <div class="hdt_date_item" data-id="04">04</div>
      <div class="hdt_date_item" data-id="05">05</div>
      <div class="hdt_date_item" data-id="06">06</div>
      <div class="hdt_date_item" data-id="07">07</div>
      <div class="hdt_date_item" data-id="08">08</div>
      <div class="hdt_date_item" data-id="09">09</div>
      <div class="hdt_date_item" data-id="10">10</div>
      <div class="hdt_date_item" data-id="11">11</div>
      <div class="hdt_date_item" data-id="12">12</div>
      <div class="hdt_date_item" data-id="13">13</div>
      <div class="hdt_date_item" data-id="14">14</div>
      <div class="hdt_date_item" data-id="15">15</div>
      <div class="hdt_date_item" data-id="16">16</div>
      <div class="hdt_date_item" data-id="17">17</div>
      <div class="hdt_date_item" data-id="18">18</div>
      <div class="hdt_date_item" data-id="19">19</div>
      <div class="hdt_date_item" data-id="20">20</div>
      <div class="hdt_date_item" data-id="21">21</div>
      <div class="hdt_date_item" data-id="22">22</div>
      <div class="hdt_date_item" data-id="23">23</div>
      <div class="hdt_date_item" data-id="24">24</div>
      <div class="hdt_date_item" data-id="25">25</div>
      <div class="hdt_date_item" data-id="26">26</div>
      <div class="hdt_date_item" data-id="27">27</div>
      <div class="hdt_date_item" data-id="28">28</div>
      <div class="hdt_date_item" data-id="29">29</div>
      <div class="hdt_date_item" data-id="30">30</div>
      <div class="hdt_date_item" data-id="31">31</div>
    </div>
    <div class="hdt_date_picker_model_year hdt_model_item"></div>
	<div class = "hdt_date_picker_cancel hdt_date_item">REMOVE DATE</div>
	</div>
  </div>
</div>
 <?php
}