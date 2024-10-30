let hdt_enter_notification = false;
jQuery("#hdt_form_wrapper").on("keyup", "#hdt_new_project_name", function(e) {
  e.preventDefault();
  if (e.which == 13) {
    hdt_enter_notification = false;
    let hdt_project_name = jQuery("#hdt_new_project_name").val();
    if (hdt_project_name.length > 1) {
      jQuery(".hdt_input_notification").fadeOut();
      jQuery("#hdt_new_project_name").val("");

      jQuery.ajax({
        type: "POST",
        data: {
          action: "hdt_add_new_project",
          hdt_admin_nonce: jQuery("#hdt_admin_nonce").val(),
          project_name: hdt_project_name,
          project_id: 0
        },
        url: ajaxurl,
        success: function(data) {
          // add block
          let json = JSON.parse(data);
          if (json[0] > 0) {
            console.log(json);
            let block =
              '<div class = "hdt_project_item hdt_project_item_' +
              json[0] +
              '" data-id = "' +
              json[0] +
              '">\
										<div class = "hdt_project_title">\
											' +
              json[1] +
              '\
										</div>\
										<div class = "hdt_project_link hdt_icons">\
											<a href = "' +
              json[2] +
              '" target = "_blank"><span class="dashicons dashicons-admin-links" title = "View project"></span></a>\
										</div>\
										<div class = "hdt_project_settings hdt_icons">\
											<span class="dashicons dashicons-admin-settings" data-id = "' +
              json[0] +
              '" title = "Configure project"></span>\
										</div>\
										<div class = "hdt_project_delete hdt_icons">\
											<span class="dashicons dashicons-trash" data-id = "' +
              json[0] +
              '" title = "Delete this project and all attached tasks"></span>\
										</div>\
									</div>';

            jQuery("#hdt_list_projects").prepend(block);
          } else {
            console.log("Permission denied. Bad JSON");
          }
        },
        error: function() {
          console.log("Permission denied");
        }
      });
    }
  } else {
    let content = jQuery(this).val();
    if (content != "" && content != null) {
      hdt_press_enter_notificiation("#hdt_new_project_name");
    } else {
      jQuery(".hdt_input_notification").fadeOut();
      hdt_enter_notification = false;
    }
  }
});

function hdt_press_enter_notificiation(elem) {
  if (!hdt_enter_notification) {
    hdt_enter_notification = true;
    setTimeout(function() {
      let content = jQuery(elem).val();
      if (content != "" && content != null) {
        jQuery(elem)
          .next(".hdt_input_notification")
          .fadeIn();
      }
    }, 3000);
  }
}

jQuery("#hdt_form_wrapper").on(
  "click",
  ".hdt_project_delete .dashicons",
  function(e) {
    let project_id = jQuery(this).attr("data-id");
    let data =
      '<h3>Warning:</h3><p>You are about to permanently delete this project and all attached tasks.</p><p><button data-id = "' +
      project_id +
      '" id = "hdt_confirm_delete" class = "hdt_button hdt_continue">Continue</button><button id = "hdt_cancel_delete" class = "hdt_button hdt_cancel">Cancel</button></p>';
    hdt_show_message(data);
  }
);

jQuery("#hdt_message_wrap").on("click", "#hdt_cancel_delete", function(e) {
  hdt_hide_message();
});

jQuery("#hdt_message_wrap").on("click", "#hdt_confirm_delete", function(e) {
  let project_id = jQuery(this).attr("data-id");
  jQuery(this).fadeOut();
  jQuery.ajax({
    type: "POST",
    data: {
      action: "hdt_delete_project",
      hdt_admin_nonce: jQuery("#hdt_admin_nonce").val(),
      project_id: project_id
    },
    url: ajaxurl,
    success: function(data) {
      // remove block
      console.log("hdt_project_item_" + project_id);
      jQuery(".hdt_project_item_" + project_id).fadeOut();
      setTimeout(function() {
        jQuery(".hdt_project_item_" + project_id).remove();
      }, 1000);
    },
    error: function() {
      console.log("Permission denied");
    },
    complete: function() {
      hdt_hide_message();
    }
  });
});

jQuery("#hdt_form_wrapper").on(
  "click",
  ".hdt_project_settings .dashicons",
  function(e) {
    jQuery("#hdt_list_projects").fadeOut();
    let project_id = jQuery(this).attr("data-id");
    jQuery(this).fadeOut();
    jQuery.ajax({
      type: "POST",
      data: {
        action: "hdt_edit_project",
        hdt_admin_nonce: jQuery("#hdt_admin_nonce").val(),
        project_id: project_id
      },
      url: ajaxurl,
      success: function(data) {
        // hide project list
        // show project edit
        jQuery("#hdt_list_projects").html(data);
      },
      error: function() {
        console.log("Permission denied");
      },
      complete: function() {
        jQuery("#hdt_list_projects").fadeIn();
      }
    });
  }
);

jQuery("#hdt_form_wrapper").on("click", "#hdt_update_project", function(e) {
  let project_id = jQuery(this).attr("data-id");
  let project_name = jQuery("#hdt_project_name").val();
  let project_slug = jQuery("#hdt_project_slug").val();
  let data = {
    projectName: project_name,
    projectSlug: project_slug
  };

  data = JSON.stringify(data);

  if (project_name != "" && project_slug != "") {
    jQuery(this).fadeOut();
    jQuery.ajax({
      type: "POST",
      data: {
        action: "hdt_edit_save_project",
        hdt_admin_nonce: jQuery("#hdt_admin_nonce").val(),
        project_id: project_id,
        data: data
      },
      url: ajaxurl,
      success: function(data) {
        // show update message
        console.log(data);
      },
      error: function() {
        console.log("Permission denied");
      },
      complete: function() {
        jQuery("#hdt_update_project").fadeIn();
      }
    });
  }
});

function hdt_show_message(data) {
  jQuery("#hdt_message").html(data);
  jQuery("#hdt_message_wrap")
    .css("display", "flex")
    .hide()
    .fadeIn();
}

function hdt_hide_message() {
  jQuery("#hdt_message").html("");
  jQuery("#hdt_message_wrap").fadeOut();
}
