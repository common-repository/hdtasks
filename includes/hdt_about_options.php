<?php
/*
HDTasks About and Options page
 */
?>
	<div id = "hdt_meta_forms">
		<div id = "hdt_wrapper">
			<div id="hdt_form_wrapper">
				<div class = "hdt_tab hdt_tab_active">
					<h1>
						HDTasks
					</h1>

					<p>
						HDTasks was designed and developed to be one of the easiest and hassle free team and project managment task lists. If you have any questions, or need support, please contact me at the <a href = "https://wordpress.org/support/plugin/hdtasks" target = "_blank">official WordPress HDTasks support forum</a>.
					</p>
					<p>
						As I continue to develop HDTasks, more features, options, customizations, and settings will be introduced. If you have enjoyed HDTasks, then I would sure appreciate it if you could <a href="https://wordpress.org/support/plugin/hdtasks/reviews/#new-post" target = "_blank">leave an honest review</a>. It's the little things that make building systems like this worthwhile ❤.
					</p>

					<div class = "hdt_highlight">
						<p>
							HDTasks is 100% free and developed in my spare time. If you are enjoying HDTasks and would like to show your support, please consider contributing to my <a href = "https://www.patreon.com/harmonic_design" target ="_blank">patreon page</a> to help continued development. Every little bit helps, and I am fuelled by coffee.
						</p>
					</div>

					<br/>

					<h2>Upcoming Features</h2>
					<p>I am developing HDtasks in my spare time, but still plan to add the following features at some point</p>
					<ul class = "hdt_list">
						<li>[option] New tasks go to top of list instead of bottom</li>
						<li>[enhancement] Detect when another user is editing a task and show warning</li>
						<li>[enhancement] If the Required By Date is close, highlight it</li>
						<li style = "list-style:none">&nbsp;&nbsp; - yellow if due tomorrow, orange if today, and red if due date has past</li>
						<li>[feature] Ability to upload files to tasks</li>
						<li>[feature] Task changelog</li>
						<li>[dope] Build a PWA for ultimate mobile experience</li>
						<li>[feature] User membership access</li>
						<li style = "list-style:none">&nbsp;&nbsp; - Users have to log into the site to access project</li>
						<li style = "list-style:none">&nbsp;&nbsp; - Alternatively, users can view but not add or edit unless logged in</li>
						<li>[enhancement] Translation ready (including the admin area)</li>
					</ul>
					<br/>

					<h2>Quick Documentation</h2>
					<p>HDTasks was designed to be as easy and intuitive to use as possible. However, I understand that some guidance might still be needed.</p>
					<p>This system works off of <strong>Tasks</strong> and <strong>Projects</strong>, where all Tasks belong to a Project. So to get started, all you have to do is create a new Project. This can be done by selecting <strong>HDTasks</strong> from the left sidebar.</p>
					<p>Once a Project has been created, you will be able to set any individual project settings (eventually, settings such as if you want to allow file uploads, logins, etc), and you will also get a unique and secret URL for the project that you can share with your team or clients.</p>
					<p>All Projects and Tasks are blocked from being indexed, so the only way someone will find your project page is if you provide them with the secret link.</p>
					<h3>
						Add "tags" to Tasks
					</h3>
					<p>You can add as many tags to a task title as you want by prepending the tags to the start of a task title. Tags are made by encapsulating the tag beteen <code>[</code> and <code>]</code>.</p>
					<p>This can be useful for assigning a task to a specific team memeber, or categorizing your tasks.</p>
					<p>Exmaple task title with two tags: <code>[Dylan] [Important] Complete wireframe mockups and send</code>.</p>
					<p>The above example takes the title "Complete wireframe mockups and send" and creates two tags; Dylan, Important.</p>
					<h3>
						Generate Custom Member URLs
					</h3>
					<p>
						By default, HDTasks does not require any login or password to access; all that's needed is the secret URL. However, the downside to not having logins is that there is no way for HDTasks to know who you are (*without angering GDPR compliancy) until you enter your name. And even then, the name would only be stored in your browser for that single session.
					</p>
					<p>
						Because of this, I created a simple mechanism that will allow you to create a custom URL for each team member that already has their name embedded. This works by appending <code>?user=NAME</code> to the end of the project URL. So for example, let's assume your Project URL is <code>https://yourdomain.com/client-project/GoLeafs</code>. You could change it to any of the following examples:
					</p>
					<p>
						<code>https://yourdomain.com/client-project/GoLeafs?user=Dylan</code><br/>
						<code>https://yourdomain.com/client-project/GoLeafs?user=Jessica</code><br/>
						<code>https://yourdomain.com/client-project/GoLeafs?user=Prit</code><br/>
						<code>https://yourdomain.com/client-project/GoLeafs?user=Alex</code>
					</p>
					<p>
						and send those links to each of the people you want to give access to.
					</p>
					<br/>
					<h2>Other Harmonic Design Plugins</h2>

					<div id = "hdt_admin_plugins">


					<?php
$data = wp_remote_get("https://harmonicdesign.ca/plugins/additional_plugins.txt");
if (is_array($data)) {

    $data = $data["body"];
    $data = stripslashes(html_entity_decode($data));
    $data = json_decode($data);

    foreach ($data as $value) {
        $title = sanitize_text_field($value[0]);
        $subtitle = sanitize_text_field($value[1]);
        $image = sanitize_text_field($value[2]);
        $link = sanitize_text_field($value[3]);
        $description = sanitize_text_field($value[4]);
        ?>

						<div class="product">
							<h3><?php echo $title; ?></h3>
							<p class="tagline"><?php echo $subtitle; ?></p>
							<img src="<?php echo $image; ?>" alt="<?php echo $title; ?>">
							<p><?php echo $description; ?></p>
							<p><a href="<?php echo $link; ?>" class="btn">download</a></p>
						</div>


							<?php }
} else {
    echo '<h2>There was an error loading additional Harmonic Design Plugins.</h2>';
}
?>

					</div>


					<div class = "clear"></div>
				</div>
			</div>
		</div>
	</div>
