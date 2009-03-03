$(document).ready(function() {
  // validate signup form on keyup and submit
  var validator = $("#setupform").validate({
    debug: false,
    onsubmit: true,
    onfocusout: true,
    success: function(label) {
      label.addClass('valid');
    },
    submitHandler: function(form) {
      // do other stuff for a valid form
      $(form).submit();
    },
    /*
    invalidHandler: function(form, validator) {
      alert(message);
      var errors = validator.numberOfInvalids();
      if (errors) {
        var message = errors == 1
          ? 'You missed 1 field. It has been highlighted'
	    : 'You missed ' + errors + ' fields. They have been highlighted';
        //$("div.error span").html(message);
        //$("div.error").show();
      } else {
	//$("div.error").hide();
      }
    },
    */
    rules: {
      site: "required",
      dbpass: {
        remote: {
          url: "verify.php",
          type: "post",
          data: {
            method: 'dbauth',
            dbuser: function() { return $('#dbuser').val(); },
            dbpass: function() { return $('#dbpass').val(); },
            dbhost: function() { return $('#dbhost').val(); }
          }
        }
      },
      dbname: {
        required: true,
        remote: {
          url: "verify.php",
          type: "post",
          data: {
            method: 'dbname',
            dbuser: function() { return $('#dbuser').val(); },
            dbpass: function() { return $('#dbpass').val(); },
            dbhost: function() { return $('#dbhost').val(); },
            dbname: function() { return $('#dbname').val(); }
          }
        }
      },
      twpass: {
        required: true,
        remote: {
          url: "verify.php",
          type: "post",
          data: {
            method: 'twitter',
            username: function() { return $('#twuser').val(); },
            password: function() { return $('#twpass').val(); }
          }
        }
      }
    },
    messages: {
      tag: {
        required: 'Enter a hash tag (e.g. "#foo") to filter against.'
      },
      site: {
        required: "Please enter a site title."
      },
      twpass: {
        remote: jQuery.format("That is not a valid username/password")
      },
      dbpass: {
        remote: jQuery.format("Could not connect to database")
      },
      dbname: {
        remote: jQuery.format("Could not connect to database name")
      }
    }
  });
});
