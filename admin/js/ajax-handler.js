(function ($) {
  "use strict";

  // Extract i18n functions for cleaner code
  const { __, _n, sprintf } = wp.i18n;

  // Global variables
  var processing = false;
  var totalProcessed = 0;
  var totalTrashed = 0;

  // DOM Ready Handler
  $(document).ready(function () {
    // Form Submission Handler
    $("#rdc-duplicate-form").on("submit", function (e) {
      e.preventDefault();

      // Check if already processing
      if (processing) {
        return;
      }

      // Validate at least one checkbox is checked
      var checkedStatuses = $('input[name="statuses[]"]:checked');
      if (checkedStatuses.length === 0) {
        // Show error
        var errorNotice = $("<div>").addClass("notice notice-error");
        var errorParagraph = $("<p>");
        errorParagraph.append(
          $("<strong>").text(__("Error:", "remove-duplicate-comments") + " ")
        );
        errorParagraph.append(
          document.createTextNode(
            __(
              "Please select at least one comment status to check.",
              "remove-duplicate-comments"
            )
          )
        );
        errorNotice.append(errorParagraph);
        $("#rdc-results").html(errorNotice).show();
        $("html, body").animate(
          { scrollTop: $("#rdc-results").offset().top - 50 },
          500
        );
        return;
      }

      // Start processing
      processing = true;
      totalProcessed = 0;
      totalTrashed = 0;

      // Update UI
      $("#rdc-start-button").prop("disabled", true);
      $("#rdc-start-button").siblings(".spinner").addClass("is-active");
      $("#rdc-results").hide().empty();
      $("#rdc-progress").show();
      $(".rdc-progress-bar-fill").css("width", "0%");
      $("#rdc-processed-count").text("0");
      $("#rdc-trashed-count").text("0");

      // Get selected statuses
      var statuses = checkedStatuses
        .map(function () {
          return $(this).val();
        })
        .get();

      // Start processing
      processBatch(statuses);
    });
  });

  // Batch Processing Function
  function processBatch(statuses) {
    // Prepare AJAX data
    var ajaxData = {
      action: rdcAjax.action,
      nonce: rdcAjax.nonce,
      statuses: statuses,
    };

    // Make AJAX call
    $.ajax({
      url: rdcAjax.ajaxurl,
      method: "POST",
      data: ajaxData,
      dataType: "json",
      success: function (response) {
        // Check if response has success property
        if (response.success) {
          var data = response.data;

          // Support legacy payloads that used "deleted" instead of "trashed"
          var trashedThisBatch =
            typeof data.trashed !== "undefined"
              ? data.trashed
              : data.deleted || 0;

          // Update cumulative counters
          totalProcessed += data.processed;
          totalTrashed += trashedThisBatch;

          // Update UI counters
          $("#rdc-processed-count").text(totalProcessed);
          $("#rdc-trashed-count").text(totalTrashed);

          // Update plural-aware labels
          $("#rdc-processed-label").text(
            _n(
              "comment",
              "comments",
              totalProcessed,
              "remove-duplicate-comments"
            )
          );
          $("#rdc-trashed-label").text(
            _n(
              "duplicate",
              "duplicates",
              totalTrashed,
              "remove-duplicate-comments"
            )
          );

          // Update progress bar
          if (data.completed === true) {
            $(".rdc-progress-bar-fill").css("width", "100%");
            handleCompletion();
          } else {
            // Recursive call for next batch
            processBatch(statuses);
          }
        } else {
          // Handle error response
          handleError(
            response.data.message ||
              __(
                "An error occurred during processing.",
                "remove-duplicate-comments"
              )
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        handleError(
          __(
            "An unexpected error occurred. Please try again.",
            "remove-duplicate-comments"
          )
        );
      },
    });
  }

  // Completion Handler Function
  function handleCompletion() {
    processing = false;

    // Hide progress
    $("#rdc-progress").hide();

    // Enable button
    $("#rdc-start-button").prop("disabled", false);
    $("#rdc-start-button").siblings(".spinner").removeClass("is-active");

    // Display success notice
    var noticeDiv = $("<div>").addClass("notice notice-success");
    var messageP = $("<p>");

    var processedText = sprintf(
      _n(
        "%d comment",
        "%d comments",
        totalProcessed,
        "remove-duplicate-comments"
      ),
      totalProcessed
    );
    var trashedText = sprintf(
      _n(
        "%d duplicate",
        "%d duplicates",
        totalTrashed,
        "remove-duplicate-comments"
      ),
      totalTrashed
    );
    var successMessage = sprintf(
      __(
        "Processed %s and moved %s to the trash.",
        "remove-duplicate-comments"
      ),
      processedText,
      trashedText
    );

    messageP.append(
      $("<strong>").text(__("Success!", "remove-duplicate-comments") + " ")
    );
    messageP.append(document.createTextNode(successMessage));
    noticeDiv.append(messageP);
    $("#rdc-results").html(noticeDiv).show();

    // Scroll to results
    $("html, body").animate(
      { scrollTop: $("#rdc-results").offset().top - 50 },
      500
    );
  }

  // Error Handler Function
  function handleError(message) {
    processing = false;

    // Hide progress
    $("#rdc-progress").hide();

    // Enable button
    $("#rdc-start-button").prop("disabled", false);
    $("#rdc-start-button").siblings(".spinner").removeClass("is-active");

    // Display error notice
    var noticeDiv = $("<div>").addClass("notice notice-error");
    var messageP = $("<p>");
    messageP
      .append(
        $("<strong>").text(__("Error:", "remove-duplicate-comments") + " ")
      )
      .append(document.createTextNode(message));
    noticeDiv.append(messageP);
    $("#rdc-results").html(noticeDiv).show();

    // Scroll to results
    $("html, body").animate(
      { scrollTop: $("#rdc-results").offset().top - 50 },
      500
    );
  }
})(jQuery);
