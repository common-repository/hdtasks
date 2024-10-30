<?php
// STOP PAGE CACHING
// W3 Total Cache, WP Super Cache, WP Rocket, Comet Cache, Cachify
define('DONOTCACHEPAGE', true);
define('DONOTCACHEDB', true);

// remove all scripts and style created by other plugins or the theme
// then ensure that only HDTasks stuff is running
function hdt_denqueue_enqueue()
{
    // start with the scripts
    global $wp_scripts;
    $wp_scripts->queue = array(); // empty it

	wp_enqueue_script('hdt_moment', plugin_dir_url(__FILE__) . './moment.js?v=' . HDT_PLUGIN_VERSION, array(), null, true);
    wp_enqueue_script('hdt_sortable', plugin_dir_url(__FILE__) . './sortable.js?v=' . HDT_PLUGIN_VERSION, array('jquery'), null, true);
    wp_enqueue_script('hdt_main_script', plugin_dir_url(__FILE__) . './script.js?v=' . HDT_PLUGIN_VERSION, array('jquery'), null, true);
    wp_enqueue_script('hdt_ve', plugin_dir_url(__FILE__) . './editor/trumbowyg.min.js', array('jquery'), null, true);

    $queried_object = get_queried_object();
    $term_id = $queried_object->term_id;
    if (isset($_GET["user"])) {
        $user = $_GET["user"];
    } else {
        $user = "default";
    }
    wp_localize_script('hdt_main_script', 'hdt_ajax', admin_url('admin-ajax.php'));
    wp_localize_script('hdt_main_script', 'project_id', [$term_id]);
    wp_localize_script('hdt_main_script', 'current_user', $user);

    // now styles
    global $wp_styles;
    $wp_styles = new stdClass(); // stops "Creating default object from empty value" warning. Not sure why styles shows this error but scripts does not
    $wp_styles->queue = array();
    wp_enqueue_style('hdv_ve_css', plugin_dir_url(__FILE__) . './editor/ui/trumbowyg.min.css');
}
add_action('wp_print_scripts', 'hdt_denqueue_enqueue', 100); // I could also use wp_print_styles, but see no reason to separate them

?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<meta name="robots" content="noindex">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HDTasks | <?php echo single_tag_title('', false); ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__FILE__); ?>style.css?v=<?php echo HDT_PLUGIN_VERSION; ?>" media="all" />
    <meta name="theme-color" content="#222" />
	<?php wp_head();?>
  </head>

  <body>
	  <?php
// WP Fastest Cache
echo '<!-- [wpfcNOT] -->';
?>
    <h1 id="project_title"><?php echo single_tag_title('', false); ?></h1>

    <div id="update_task_order">
      <button id="save_new_task_order" data-id="0">save new order</button>
    </div>

    <div id="add_new_task_wrap">
      <button id="add_new_task" data-id="0">+ task</button>
    </div>

    <div id="editor" class = "contenteditable">
      <div id="loader">
        <div class="cs-loader-inner">
          <label> ●</label>
          <label> ●</label>
          <label> ●</label>
          <label> ●</label>
          <label> ●</label>
          <label> ●</label>
        </div>
      </div>
      <div id="editor_top">
        <input
          type="text"
          id="editor_task_title"
          placeholder="enter short task title"
        />
        <div class="select">
          <select
            id="editor_task_status"
            name="editor_task_status"
            class="selectbox select-hidden"
          >
            <option value="hide" selected="">status</option>
            <option value="">in progress</option>
            <option value="">completed</option>
            <option value="">cancelled</option>
            <option value="">default</option>
          </select>
        </div>
      </div>
      <div id="editor_content" class = "contenteditable"></div>
      <div id="editor_toolbar">
        <input type="text" id="editor_author" placeholder="your name" />
		<?php echo hdt_get_date_picker(); ?>
        <div><button id="editor_save" data-id="0">save</button></div>
      </div>
    </div>


    <div id="tasks">

	<?php
// WP_Query arguments
$queried_object = get_queried_object();
$term_id = $queried_object->term_id;
$args = array(
    'post_type' => array('hdt_tasks'),
    'nopaging' => true,
    'posts_per_page' => '-1',
    'post_parent' => '0',
    'orderby' => 'menu_order',
    'order' => 'ASC',
    'tax_query' => array(
        array(
            'taxonomy' => 'hdt_projects',
            'field' => 'term_id',
            'terms' => $term_id,
        ),
    ),
);

// The Query
$query = new WP_Query($args);

// The Loop
if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $status = get_post_meta(get_the_ID(), 'hdt_status', true);
        if ($status == "in progress") {
            $status = 'in_progress';
        }
        $due_date = get_post_meta(get_the_ID(), 'hdt_date', true);

        if ($due_date == "" || $due_date == null) {
            $status = $status . " one_col";
        }
        $task_title = get_the_title();
        $task_tags = explode("]", $task_title);

        if (sizeof($task_tags) > 1) {
            $task_title = "";
            foreach ($task_tags as $key => $tag) {
                if ($key < sizeof($task_tags) - 1) {
                    $task_title = $task_title . '<span class = "tag">';
                    $tag = ltrim($tag, ' ');
                    $tag = ltrim($tag, '[');
                    $task_title = $task_title . $tag;
                    $task_title = $task_title . '</span>';
                }
            }
            $task_title = $task_title . $task_tags[sizeof($task_tags) - 1];
        }
        ?>

              <div class="task <?php echo $status; ?>" data-id="<?php echo get_the_id(); ?>" data-position="0">
				<div class = "task_header">
					<div class = "task_order">≡</div>
					<div class="task_title"><?php echo $task_title; ?></div>
					<div class="task_assigned">
						<div class = "task_assigned_readable" title = "Task to be completed on <?php echo $due_date; ?>">
							<?php echo $due_date; ?>
						</div>
					</div>
				</div>
              </div>

			  <div id = "temp" style = "display:none"></div>

              <?php }
} else {
    // no posts found
}
// Restore original Post Data
wp_reset_postdata();
?>
    </div>


    <?php
wp_nonce_field('hdt_nonce', 'hdt_nonce');
wp_footer();
?>
  </body>
</html>
