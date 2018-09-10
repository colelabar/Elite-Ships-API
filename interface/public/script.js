$(document).ready(function() {
  $("#shipForm").submit(function(event) {
    var form = $(this);
    event.preventDefault();
    $.ajax({
      type: "POST",
      url: "http://localhost:8080/api/ships",
      data: form.serialize(),
      success: function(data) {
        window.location.replace("http://localhost:8080/interface");
      }
    });
  });
  $("#shipEditForm").submit(function(event) {
    var form = $(this);
    var shipID = $(this).attr("data-id");
    event.preventDefault();
    $.ajax({
      type: "PUT",
      url: "http://localhost:8080/api/ships/" + shipID,
      data: form.serialize(),
      success: function(data) {
        window.location.replace("http://localhost:8080/interface");
      }
    });
  });
  $( ".deletebtn" ).click(function() {
    var delButton = $(this).attr("data-id");
    if(window.confirm("Are you sure you want to delete this entry?")) {
      $.ajax({
        type: "DELETE",
        url: "http://localhost:8080/interface/ships/" + delButton,
        success: function(data) {
          window.location.reload();
        }
      });
    }
  });
});
