/*
  Plugin Name: HDTeams JS Script
  Plugin URI: https://harmonicdesign.ca
  Author: Harmonic Design
*/

const EDIT_ICON =
  '<?xml version="1.0" encoding="UTF-8"?><svg width="22px" height="22px" viewBox="0 -1 401.52289 401" xmlns="http://www.w3.org/2000/svg"><path d="m370.59 250.97c-5.5234 0-10 4.4766-10 10v88.789c-0.019532 16.562-13.438 29.984-30 30h-280.59c-16.562-0.015625-29.98-13.438-30-30v-260.59c0.019531-16.559 13.438-29.98 30-30h88.789c5.5234 0 10-4.4766 10-10 0-5.5195-4.4766-10-10-10h-88.789c-27.602 0.03125-49.969 22.398-50 50v260.59c0.03125 27.602 22.398 49.969 50 50h280.59c27.602-0.03125 49.969-22.398 50-50v-88.793c0-5.5234-4.4766-10-10-10z"/><path d="m376.63 13.441c-17.574-17.574-46.066-17.574-63.641 0l-178.41 178.41c-1.2227 1.2227-2.1055 2.7383-2.5664 4.4023l-23.461 84.699c-0.96484 3.4727 0.015624 7.1914 2.5625 9.7422 2.5508 2.5469 6.2695 3.5273 9.7422 2.5664l84.699-23.465c1.6641-0.46094 3.1797-1.3438 4.4023-2.5664l178.4-178.41c17.547-17.586 17.547-46.055 0-63.641zm-220.26 184.91 146.01-146.02 47.09 47.09-146.02 146.02zm-9.4062 18.875 37.621 37.625-52.039 14.418zm227.26-142.55-10.605 10.605-47.094-47.094 10.609-10.605c9.7617-9.7617 25.59-9.7617 35.352 0l11.738 11.734c9.7461 9.7734 9.7461 25.59 0 35.359z"/></svg>';

// eventually build HDTask global option for custom translations
const LEAVE_REPLY_TEXT = "Leave A Reply";
const UPDATE_TASK_TEXT = "Update This Task";

let taskInfo = {}; // reserve

jQuery(document).ready(function() {
  hdt_set_defaults();
  initEditor();
  hdt_start_moment();
  hdt_click_events();
  hdt_start_sortable();
  hdt_create_select_boxes();
  hdt_date_picker();
});

function hdt_set_defaults() {
  if (current_user == "default") {
    current_user = "";
  }
  project_id = parseInt(project_id[0]); // stupid scalar wp_localize_script
}

function hdt_start_moment() {
  let hdt_required_by_dates = document.getElementsByClassName(
    "task_assigned_readable"
  );
  for (let i = 0; i < hdt_required_by_dates.length; i++) {
    if (
      hdt_required_by_dates[i].innerText != "" &&
      hdt_required_by_dates[i].innerText != null
    ) {
      let hdt_required_date = hdt_required_by_dates[i].innerText;
      hdt_required_date = moment(hdt_required_date, "MM-DD-YYYY").toDate();
      let hdt_required_date_readable = hdt_get_date_formated(hdt_required_date);
      hdt_required_by_dates[i].innerHTML = hdt_required_date_readable;
      let hdt_from_now = moment.duration(
        moment(new Date()).diff(hdt_required_date)
      );
      hdt_from_now = hdt_from_now.asDays();
      if (hdt_from_now > 0) {
        hdt_required_by_dates[i].closest(".task").classList.add("late");
      }
    }
  }

  function hdt_get_date_formated(date) {
    var other_dates = moment(date).fromNow();
    var calback = function() {
      return "[" + other_dates + "]";
    };
    return moment(date).calendar(null, {
      sameDay: "[Today]",
      nextDay: "[Tomorrow]",
      nextWeek: "dddd MMM Do",
      lastDay: "[Yesterday]",
      lastWeek: "[Last] dddd",
      sameElse: "MMM Do, YYYY"
    });
  }
}

function hdt_start_sortable() {
  jQuery("#tasks").sortable({
    ghostClass: "sorting_placeholder",
    draggable: ".task",
    handle: ".task_order",
    onEnd: updateOrder
  });
}

function initEditor() {
  jQuery("#editor_content").trumbowyg({
    btns: [["strong", "em"], ["link"], ["unorderedList", "orderedList"]],
    autogrow: true
  });
}

function hdt_click_events() {
  jQuery("#save_new_task_order").click(function() {
    jQuery("#update_task_order").fadeOut();
    let tasks = [];
    let menu_number = 0;
    jQuery(".task").each(function() {
      let task = [];
      let id = jQuery(this).attr("data-id");
      task = [id, menu_number];
      tasks.push(task);
      menu_number = menu_number + 1;
    });
    tasks = JSON.stringify(tasks);

    jQuery.ajax({
      type: "POST",
      data: {
        action: "hdt_update_task_order",
        tasks: tasks,
        hdt_nonce: jQuery("#hdt_nonce").val()
      },
      url: hdt_ajax,
      success: function(data) {
        console.log(data);
      }
    });
  });

  jQuery("#tasks").on("click", ".task_header", function() {
    if (
      !jQuery(this)
        .parent()
        .hasClass("task_edit")
    ) {
      jQuery(".task_edit").removeClass("task_edit");
      jQuery(this)
        .parent()
        .addClass("task_edit");
      jQuery("#loader").fadeIn();
      let task_id = jQuery(this)
        .parent()
        .attr("data-id");
      get_task(task_id);
    } else {
      jQuery(".task_edit").removeClass("task_edit");
      jQuery("#task_content").fadeOut();
    }
  });

  jQuery("#tasks").on("click", "#edit_parent", function() {
    jQuery("#editor").fadeIn();
    let editor = jQuery("#editor").offset().top;
    editor = editor - 22;
    jQuery("html, body").animate({ scrollTop: editor + "px" });
    jQuery("#editor_save").attr("data-id", jQuery(this).attr("data-id")); // set the task id
    jQuery("#leave_reply_heading").text(UPDATE_TASK_TEXT);
    populate_editor(taskInfo);
  });

  // save the task or comment
  jQuery("#editor_save").click(function() {
    if (!jQuery("#editor").hasClass("editor_comment")) {
      addNewTask();
    } else {
      addNewComment();
    }
  });

  jQuery("#add_new_task").click(function() {
    resetEditor();
    jQuery("#editor").fadeIn();
    let editor = jQuery("#editor").offset().top;
    editor = editor - 22;
    jQuery("html, body").animate({ scrollTop: editor + "px" });
    // reset editor
    reset_editor();
  });
}

function addNewTask() {
  let task = get_task_data();
  if (
    task.title != null &&
    task.title != "" &&
    task.author != null &&
    task.author != ""
  ) {
    jQuery("#loader").fadeIn();

    var task_id = task.id;
    task = JSON.stringify(task);
    jQuery.ajax({
      type: "POST",
      data: {
        action: "hdt_add_new_task",
        data: task,
        hdt_nonce: jQuery("#hdt_nonce").val(),
        project: project_id
      },
      url: hdt_ajax,
      success: function(data) {
        if (isNaN(data)) {
          console.log(data);
        } else {
          // dynamically add task
          if (task_id == 0) {
            add_task_to_tasklist(data);
          } else {
            update_tasklist_task(data);
          }
          resetEditor();
        }
      },
      complete: function() {
        jQuery("#loader").fadeOut();
      }
    });
  } else {
    alert("please at least fill out a title and your name");
  }
}

function addNewComment() {
  let task = get_task_data();
  if (
    task.author != null &&
    task.author != "" &&
    task.description != null &&
    task.description != ""
  ) {
    jQuery("#loader").fadeIn();

    var task_id = task.id;
    task = JSON.stringify(task);
    jQuery.ajax({
      type: "POST",
      data: {
        action: "hdt_add_new_task_comment",
        data: task,
        hdt_nonce: jQuery("#hdt_nonce").val(),
        project: project_id
      },
      url: hdt_ajax,
      success: function(data) {
        if (isNaN(data)) {
          console.log(data);
        } else {
          // dynamically add comment
          if (task_id == 0) {
            add_comment_to_task(data, task);
          } else {
            update_comment(data);
          }
          reset_editor();
        }
      },
      complete: function() {
        jQuery("#loader").fadeOut();
      }
    });
  } else {
    alert("please at least fill out a title and your name");
  }
}

function add_comment_to_task(data, task) {
  task = JSON.parse(task);
  let comment =
    '<div class = "task_comment" data-id = "' +
    data +
    '">' +
    task.description +
    '<div class = "task_comment_author">-' +
    task.author +
    '</div><div class = "clear"></div></div>';
  jQuery("#task_comments").append(comment);
}

function update_comment(data) {
  // reserved for next version
  // only logged in editors/admins will be able to edit comments
}

function resetEditor() {
  jQuery(".task_edit").removeClass("task_edit");
  jQuery("#editor").prependTo("#tasks");
  jQuery("#editor").removeClass("editor_comment");
  jQuery("#editor_save").text("save");
  jQuery("#task_content").remove();
}

// update task order after drag and drop
function updateOrder() {
  let tasks = document.getElementsByClassName("task");
  for (let i = 0; i < tasks.length; i++) {
    tasks[i].setAttribute("data-position", i);
  }
  jQuery("#update_task_order").fadeIn();
}

function get_task(task_id) {
  jQuery.ajax({
    type: "POST",
    data: {
      action: "hdt_get_task",
      task_id: task_id,
      hdt_nonce: jQuery("#hdt_nonce").val()
    },
    url: hdt_ajax,
    success: function(data) {
      data = JSON.parse(data);
      // populate_editor(data);
      populate_task(data);
      taskInfo = data;
    },
    complete: function() {
      jQuery("#loader").fadeOut();
    }
  });
}

function populate_task(data) {
  jQuery("#editor").appendTo("#temp"); // move editor to temporary node
  jQuery("#task_content").remove(); // clear all task_content
  let taskContent =
    '<div id = "task_content">\
<div class = "parent_task"><div class = "task_description">' +
    data.description +
    '</div></div><div id = "edit_parent" data-id = "' +
    data.taskID +
    '">' +
    EDIT_ICON +
    '</div><div class = "task_author">-' +
    data.author +
    '</div><div class = "clear"></div><div id = "task_comments"></div><h4 id = "leave_reply_heading">Leave A Reply:</h4></div>';
  jQuery(".task_edit").append(taskContent);
  jQuery("#editor").appendTo("#task_content");
  jQuery("#editor").addClass("editor_comment");
  jQuery("#editor_save").text("leave comment");
  jQuery("#leave_reply_heading").text(LEAVE_REPLY_TEXT);
  jQuery("#editor").fadeIn();
  reset_editor();
  populate_comments(data.comments);
}

function populate_comments(data) {
  for (let i = 0; i < data.length; i++) {
    let comment =
      '<div class = "task_comment" data-id = "' +
      data[i][0] +
      '">' +
      data[i][3] +
      '<div class = "task_comment_author">-' +
      data[i][1] +
      '<div class = "task_comment_date">' +
      data[i][2] +
      '</div></div><div class = "clear"></div></div>';
    jQuery("#task_comments").prepend(comment);
  }

  let task = jQuery(".task_edit").offset().top;
  task = task - 22;
  jQuery("html, body").animate({ scrollTop: task + "px" });
}

function populate_editor(data) {
  jQuery("#editor").removeClass("editor_comment");
  jQuery("#editor_save").text("save");

  let status = data.status;
  if (status == "default") {
    status = "status";
  }

  let duedate = data.dueDate;
  if (duedate == "" || duedate == null) {
    duedate = "required by date";
  }
  jQuery("#editor_save").fadeIn();
  jQuery("#editor_task_title").val(data.title);

  jQuery("#editor_content").trumbowyg("html", data.description);
  jQuery("#editor_author").val(data.author);
  jQuery(".select-styled").html(status);
  jQuery(".hdt_date_picker").html(duedate);
  jQuery("#user_birth").val(data.dueDate);
  jQuery("#editor_save").attr("data-id", data.taskID);
}

function reset_editor() {
  jQuery("#loader").fadeOut();
  jQuery("#editor_save").fadeIn();
  jQuery("#editor_task_title").val("");
  jQuery("#editor_content").trumbowyg("empty");
  jQuery("#editor_author").val(current_user);
  jQuery("#editor_task_status").removeAttr("selected");
  jQuery(".select-styled").html("status");
  jQuery(".hdt_date_picker").html("required by date");
  jQuery("#user_birth").val("");
  jQuery("#editor_save").attr("data-id", "0"); // set the task id to zero
  jQuery("#editor_task_title").focus();
}

function update_tasklist_task(data) {
  let task_title = jQuery("#editor_task_title").val();
  let task_tags = task_title.split("]");
  if (task_title.length > 1) {
    task_title = "";
    for (let i = 0; i < task_tags.length - 1; i++) {
      task_title = task_title + '<span class = "tag">';
      let tag = task_tags[i].trim();
      tag = tag.slice(1);
      task_title = task_title + tag;
      task_title = task_title + "</span>";
    }
    task_title = task_title + task_tags[task_tags.length - 1];
  }

  let task_status = jQuery(".select-styled").text();
  if (task_status == "status") {
    task_status = "default";
  } else if (task_status == "in progress") {
    task_status = "in_progress";
  }

  let task_date = jQuery(".hdt_date_picker").text();
  if (task_date == "required by date") {
    task_date = "";
    jQuery(".task_edit").addClass("one_col");
  } else {
    jQuery(".task_edit").removeClass("one_col");
  }

  jQuery(".task_edit .task_title").html(task_title);
  jQuery(".task_edit .task_assigned_readable").html(task_date);

  jQuery(".task_edit").removeClass("completed");
  jQuery(".task_edit").removeClass("in_progress");
  jQuery(".task_edit").removeClass("cancelled");
  jQuery(".task_edit").addClass(task_status);

  jQuery(".task_edit").removeClass("task_edit");
  reset_editor();
  jQuery("#editor").fadeOut();
}

function get_task_data() {
  let id = jQuery("#editor_save").attr("data-id");
  let parent = jQuery(".task_edit").attr("data-id");
  let title = jQuery("#editor_task_title").val();
  let status = jQuery(".select-styled").text();
  if (status == "status") {
    status = "default";
  }
  let description = jQuery("#editor_content").trumbowyg("html");
  let author = jQuery("#editor_author").val();
  let date = jQuery(".hdt_date_picker").text();
  if (date == "required by date") {
    date = "";
  }
  let data = {
    id: id,
    parent: parent,
    title: title,
    status: status,
    description: description,
    author: author,
    date: date
  };
  return data;
}

function add_task_to_tasklist(task_id) {
  let task_title = jQuery("#editor_task_title").val();
  let task_author = jQuery("#editor_author").val();
  if (current_user == "" || current_user == null) {
    current_user = task_author;
  }
  let task_tags = task_title.split("]");
  if (task_title.length > 1) {
    task_title = "";
    for (let i = 0; i < task_tags.length - 1; i++) {
      task_title = task_title + '<span class = "tag">';
      let tag = task_tags[i].trim();
      tag = tag.slice(1);
      task_title = task_title + tag;
      task_title = task_title + "</span>";
    }
    task_title = task_title + task_tags[task_tags.length - 1];
  }

  let task_status = jQuery(".select-styled").text();
  if (task_status == "status") {
    task_status = "default";
  } else if (task_status == "in progress") {
    task_status = "in_progress";
  }

  let task_date = jQuery(".hdt_date_picker").text();
  if (task_date == "required by date") {
    task_date = "";
    jQuery(".task_edit").addClass("one_col");
  } else {
    jQuery(".task_edit").removeClass("one_col");
  }

  let data =
    '<div class="task ' +
    task_status +
    '" data-id="' +
    task_id +
    '" data-position="0"><div class = "task_header"><div class="task_order">≡</div><div class="task_title">' +
    task_title +
    '</div><div class="task_assigned"><div class = "task_assigned_readable"' +
    task_date +
    "</div></div></div></div>";
  jQuery("#tasks").append(data);
  reset_editor();
  jQuery("#editor").fadeOut();
}

function hdt_create_select_boxes() {
  jQuery(".selectbox").each(function() {
    let jQuerythis = jQuery(this),
      numberOfOptions = jQuery(this).children("option").length;

    jQuerythis.addClass("select-hidden");
    jQuerythis.after('<div class="select-styled"></div>');

    let jQuerystyledSelect = jQuerythis.next("div.select-styled");
    jQuerystyledSelect.text(
      jQuerythis
        .children("option")
        .eq(0)
        .text()
    );

    let jQuerylist = jQuery("<ul />", {
      class: "select-options"
    }).insertAfter(jQuerystyledSelect);

    for (let i = 0; i < numberOfOptions; i++) {
      jQuery("<li />", {
        text: jQuerythis
          .children("option")
          .eq(i)
          .text(),
        rel: jQuerythis
          .children("option")
          .eq(i)
          .val()
      }).appendTo(jQuerylist);
    }

    let jQuerylistItems = jQuerylist.children("li");

    jQuery(this)
      .next(".select-styled")
      .html(
        jQuery(this)
          .find(":selected")
          .text()
      );

    jQuerystyledSelect.click(function(e) {
      e.stopPropagation();
      jQuery("div.select-styled.active")
        .not(this)
        .each(function() {
          jQuery(this)
            .removeClass("active")
            .next("ul.select-options")
            .hide();
        });
      jQuery(this)
        .toggleClass("active")
        .next("ul.select-options")
        .toggle();
    });

    jQuerylistItems.click(function(e) {
      e.stopPropagation();
      jQuerystyledSelect.text(jQuery(this).text()).removeClass("active");
      jQuerythis.val(jQuery(this).attr("rel"));
      jQuerylist.hide();
    });

    jQuery(document).click(function() {
      jQuerystyledSelect.removeClass("active");
      jQuerylist.hide();
    });
  });
}

function hdt_date_picker() {
  const currentTime = moment(new Date());
  const this_year = parseInt(moment(currentTime).format("YYYY"));
  const this_month = parseInt(moment(currentTime).format("MM"));
  const this_day = parseInt(moment(currentTime).format("DD"));

  jQuery(".hdt_date_picker_model_month .hdt_date_item")
    .eq(this_month - 1)
    .addClass("date_today");

  jQuery(".hdt_date_picker_model_day .hdt_date_item")
    .eq(this_day - 1)
    .addClass("date_today");

  let chosenDate = [];

  populate_years();

  jQuery(".hdt_date_picker").click(function() {
    chosenDate = [];
    jQuery(".hdt_date_picker_model")
      .css("display", "flex")
      .hide()
      .fadeIn();
    jQuery(".hdt_date_picker_model_month")
      .css("display", "grid")
      .hide()
      .fadeIn();
  });

  jQuery(".hdt_date_picker_cancel").click(function() {
    chosenDate = [];
    jQuery(".hdt_date_picker_model").hide();
    jQuery(".hdt_date_picker_model_month").hide();
    jQuery(".hdt_date_picker_model_day").hide();
    jQuery(".hdt_date_picker_model_year").hide();
    jQuery(".hdt_date_picker").html("required by date");
  });

  jQuery(".hdt_date_picker_model_month .hdt_date_item").click(function() {
    let selectedDate = jQuery(this).attr("data-id");
    chosenDate.push(selectedDate);
    jQuery(".hdt_date_picker_model_month").hide();
    jQuery(".hdt_date_picker_model_day")
      .css("display", "grid")
      .hide()
      .fadeIn();
  });

  jQuery(".hdt_date_picker_model_day .hdt_date_item").click(function() {
    let selectedDate = jQuery(this).attr("data-id");
    chosenDate.push(selectedDate);
    jQuery(".hdt_date_picker_model_day").hide();
    jQuery(".hdt_date_picker_model_year")
      .css("display", "grid")
      .hide()
      .fadeIn();
  });

  jQuery(".hdt_date_picker_model_year .hdt_date_item").click(function() {
    let selectedDate = jQuery(this).attr("data-id");
    chosenDate.push(selectedDate);
    jQuery(".hdt_date_picker_model_year").hide();
    jQuery(".hdt_date_picker_model").hide();
    jQuery(".hdt_date_picker").text(chosenDate.join("-"));
  });

  function populate_years() {
    let max_year = this_year + 4;
    for (let i = this_year - 1; i < max_year; i++) {
      let extra = "";
      if (i === this_year) {
        extra = "date_today";
      }
      let data =
        '<div class="hdt_date_item ' +
        extra +
        '" data-id="' +
        i +
        '">' +
        i +
        "</div>";
      jQuery(".hdt_date_picker_model_year").append(data);
    }
  }
}
