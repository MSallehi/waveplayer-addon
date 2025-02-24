jQuery(document).ready(function ($) {
  var $container = $("#playlist-tracks");

  // Initialize sortable
  $container.find(".tracks-container").sortable({
    handle: ".track-handle",
    axis: "y",
    update: updateTrackNumbers,
  });

  // Get Track Data
  getTrackData();

  // Add Track Button
  $container.on("click", ".add-track", function (e) {
    e.preventDefault();

    var $button = $(this);
    var $tracksContainer = $container.find(".tracks-container");
    var index = $tracksContainer.children().length;

    // Disable button and show loading state
    $button
      .prop("disabled", true)
      .addClass("button-loading")
      .html(
        '<span class="spinner is-active"></span> ' +
          wp.i18n.__("Adding...", "waveplayer-addon")
      );

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "wpa_add_track",
        nonce: $("#playlist_tracks_nonce").val(),
        index: index,
      },
      success: function (response) {
        if (response.success) {
          $tracksContainer.append(response.data.html);
          updateTrackNumbers();
        }
      },
      complete: function () {
        // Re-enable button and remove loading state
        $button
          .prop("disabled", false)
          .removeClass("button-loading")
          .html(wp.i18n.__("Add Track", "waveplayer-addon"));
      },
    });
  });

  // Remove Track Button
  $container.on("click", ".remove-track", function (e) {
    e.preventDefault();

    var $button = $(this);
    var $track = $button.closest(".track-row");

    // Disable button and show loading state
    $button
      .prop("disabled", true)
      .addClass("button-loading")
      .html('<span class="spinner is-active"></span>');

    // Add fade out animation
    $track.animate(
      {
        opacity: 0.5,
      },
      200,
      function () {
        $track.remove();
        updateTrackNumbers();
      }
    );
  });

  // Upload Audio Button
  $container.on("click", ".select-audio", function (e) {
    e.preventDefault();
    var $button = $(this);
    var $track = $button.closest(".track-row");

    // Create media uploader
    var uploader = wp.media({
      title: wp.i18n.__("Select Audio File", "waveplayer-addon"),
      button: {
        text: wp.i18n.__("Use this audio", "waveplayer-addon"),
      },
      multiple: false,
      library: {
        type: "audio",
      },
    });

    // When audio is selected
    uploader.on("select", function () {
      var attachment = uploader.state().get("selection").first().toJSON();

      $track.find(".audio-url").val(attachment.url);

      // Auto-fill title if empty
      var $titleInput = $track.find('input[name*="[title]"]');
      if (!$titleInput.val()) {
        $titleInput.val(attachment.title);
      }
    });

    uploader.open();
  });

  // Helper function to update track numbers
  function updateTrackNumbers() {
    $container.find(".track-row").each(function (index) {
      // Update input names to maintain proper array indexes
      $(this)
        .find("input")
        .each(function () {
          var name = $(this).attr("name");
          if (name) {
            name = name.replace(/\[\d+\]/, "[" + index + "]");
            $(this).attr("name", name);
          }
        });
    });
  }

  function getTrackData() {
    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "wpa_get_track",
        nonce: $("#get_playlist_tracks_nonce").val(),
        post_id: $("#playlist_post_id").val(),
      },
      beforeSend: function () {
        $("#playlist-spinner").show();
      },
      success: function (response) {
        if (response.success) {
          var $tracksContainer = $("#playlist-tracks .tracks-container");
          $tracksContainer.html(response.data.html);
          updateTrackNumbers();
        }
        console.log(response);
      },
      complete: function () {
        // Re-enable button and remove loading state

        $("#playlist-spinner").hide();
      },
    });
  }
});