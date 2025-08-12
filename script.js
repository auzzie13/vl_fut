const form = $("#myForm");
const file = $("#myFile");
const submitBtn = $("#btn");

form.on("submit", (e) => {
  e.preventDefault();

  let validated = true;
  let errorMessage = '';

  if (!$("input[name='radio']:checked").val()) {
    validated = false;
    errorMessage += "Please select which data set you are wanting to import.\n";
  }

  if (!$("#myFile").val()) {
    validated = false;
    errorMessage += "Please select a file.\n";
  }

  if (!validated) {
    alert(errorMessage);
    return;
  }

  let formData = new FormData(document.getElementById("myForm"));

  // Disable submit button and change cursor to spinner
  submitBtn.prop('disabled', true);
  $('body').css('cursor', 'wait');  // spinner cursor on whole page

  $.ajax({
    url: "handler.php",
    type: "POST",
    data: formData,
    contentType: false,
    cache: false,
    processData: false,
    dataType: 'json',
    success: function (data) {
      alert("Success! The form was submitted.");
      form[0].reset();        // reset form fields
      submitBtn.prop('disabled', false);
      $('body').css('cursor', 'default'); // reset cursor
      location.reload();      // reload page after OK on alert
    },
    error: function (error) {
      alert("An error occurred. Please try again.");
      submitBtn.prop('disabled', false);
      $('body').css('cursor', 'default'); // reset cursor
      console.error("response error:", error);
    }
  });
});
