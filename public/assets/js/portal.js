$(document).ready(function () {
  new WOW().init();
  App.initPage();
  App.initLeftSideBar();
  App.initCounter();
  App.initNiceScroll();
  App.initPanels();
  App.initProgressBar();
  App.initSlimScroll();
  App.initNotific8();
  App.initTooltipster();
  App.initStyleSwitcher();
  App.initMenuSelected();
  App.initRightSideBar();
  App.initEmail();
  App.initSummernote();
  App.initAccordion();
  App.initModal();
  App.initPopover();
  App.initOwlCarousel();
  DashboardGreen.initDateRange();
  DashboardGreen.initTodoList();
});

function ValidateIPaddress(ipaddress) {
  if (
    /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(
      myForm.emailAddr.value
    )
  ) {
    return true;
  }

  alert("You have entered an invalid IP address!");

  return false;
}

function blockSpecialChar(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function blockSpecialCharemi(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function blockSpecialCharip(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function blockSpecialCharport(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function blockSpecialCharLogs(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function blockSpecialCharSleep(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function blockSpecialCharTransmission(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function isNumberKey(evt, element) {
  var charCode = evt.which ? evt.which : event.keyCode;

  var limit;

  if (
    charCode > 31 &&
    (charCode < 48 || charCode > 57) &&
    !(charCode == 46 || charCode == 8) &&
    !(limit >= 6)
  )
    return false;
}

function blockSpecialCharName(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function blockSpecialChaUserIp(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function blockSpecialChaUserPort(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function blockSpecialChaUserLog(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function blockSpecialChaUserSleep(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function blockSpecialChaUserTransmission(e) {
  var k;

  document.all ? (k = e.keyCode) : (k = e.which);

  return (
    (k > 64 && k < 91) ||
    (k > 96 && k < 123) ||
    k == 8 ||
    k == 32 ||
    (k >= 48 && k <= 57)
  );
}

function isNumberKeyPass(evt, element) {
  var charCode = evt.which ? evt.which : event.keyCode;

  var limit;

  if (
    charCode > 31 &&
    (charCode < 48 || charCode > 57) &&
    !(charCode == 46 || charCode == 8) &&
    !(limit >= 6)
  )
    return false;
}

function isNumberKeyPassEditUser(evt, element) {
  var charCode = evt.which ? evt.which : event.keyCode;

  var limit;

  if (
    charCode > 31 &&
    (charCode < 48 || charCode > 57) &&
    !(charCode == 46 || charCode == 8) &&
    !(limit >= 6)
  )
    return false;

  z;
}

$(function () {
  $("#template_name").on("keypress", function (e) {
    if (e.which == 32) {
      return false;
    }
  });

  $("#ip").on("keypress", function (e) {
    if (e.which == 32) {
      return false;
    }
  });

  $("#port").on("keypress", function (e) {
    if (e.which == 32) {
      return false;
    }
  });

  $("#logs_interval").on("keypress", function (e) {
    if (e.which == 32) {
      return false;
    }
  });

  $("#sleep_interval").on("keypress", function (e) {
    if (e.which == 32) {
      return false;
    }
  });

  $("#trans_interval").on("keypress", function (e) {
    if (e.which == 32) {
      return false;
    }
  });

  $("#password").on("keypress", function (e) {
    if (e.which == 32) {
      return false;
    }
  });

  $("#cmobile").on("keypress", function (e) {
    if (e.which == 32) {
      return false;
    }
  });

  $("#cemail").on("keypress", function (e) {
    if (e.which == 32) {
      return false;
    }
  });

  $("#cmobile").keydown(function (event) {
    var key = event.which || event.keyCode;

    // Allow numeric keys (main keyboard and keypad), backspace, and arrow keys for navigation
    if (
      (key >= 48 && key <= 57) ||
      (key >= 96 && key <= 105) ||
      key === 8 ||
      (key >= 37 && key <= 40)
    ) {
      // Continue normal input processing
      return true;
    } else {
      // Prevent any other keys from being pressed
      event.preventDefault();
      return false;
    }
  });

  $("#port")
    .unbind("keyup change input paste")
    .bind("keyup change input paste", function (e) {
      var $this = $(this);

      var val = $this.val();

      var valLength = val.length;

      var maxCount = $this.attr("max");

      if (valLength > maxCount) {
        $this.val($this.val().substring(0, maxCount));
      }
    });
});

function maxLengthCheck(object) {
  if (object.value.length > object.maxLength)
    object.value = object.value.slice(0, object.maxLength);
}

function maxLengthCheckSleep(object) {
  if (object.value.length > object.maxLength)
    object.value = object.value.slice(0, object.maxLength);
}

function maxLengthCheckTransmission(object) {
  if (object.value.length > object.maxLength)
    object.value = object.value.slice(0, object.maxLength);
}

function maxLengthCheckPassword(object) {
  if (object.value.length > object.maxLength)
    object.value = object.value.slice(0, object.maxLength);
}

function maxLengthCheckPort(object) {
  if (object.value.length > object.maxLength)
    object.value = object.value.slice(0, object.maxLength);
}

function CheckIPAddress(MyIPAddress) {
  var CheckIP = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/;

  if (CheckIP.test(MyIPAddress)) {
    alert("Your IP Address Is Valid.");
    f;
  } else {
    alert("Your IP Address Is Not Valid.");
  }
}

$(document).ready(function () {
  // Initialize select2
  $('input[type="text"][name^="configuration"]').on('keypress', function(event) {
    if (event.which === 32) { // 32 is the ASCII code for space
      event.preventDefault(); // Prevent the space from being entered
    }
  });
  $("#templates").select2();
});

// $(document).ready(function(){

//   // Initialize select2

//   $("#user_id").select2();

// });

$(function () {
  $.validator.addMethod(
    "IP4Checker",
    function (value) {
      var ip =
        "^(?:(?:25[0-5]2[0-4][0-9][01]?[0-9][0-9]?).){3}" +
        "(?:25[0-5]2[0-4][0-9][01]?[0-9][0-9]?)$";

      return value.match(ip);
    },
    "Invalid IP address"
  );

  $("#ip").validate({
    rules: {
      ip: {
        required: true,

        IP4Checker: true,
      },
    },
  });
});

$(function () {
  $("#cpassword").keypress(function (e) {
    var keyCode = e.keyCode || e.which;

    $("#lblError").html("");

    //Regex for Valid Characters i.e. Alphabets and Numbers.

    var regex = /^[A-Za-z0-9]+$/;

    //Validate TextBox value against the Regex.

    var isValid = regex.test(String.fromCharCode(keyCode));

    if (!isValid) {
      $("#lblError").html("Only Alphabets and Numbers allowed.");
    }

    return isValid;
  });
});

$(function () {
  $("#LoginPassword").keypress(function (e) {
    var keyCode = e.keyCode || e.which;

    $("#lblError").html("");

    //Regex for Valid Characters i.e. Alphabets and Numbers.

    var regex = /^[A-Za-z0-9]+$/;

    //Validate TextBox value against the Regex.

    var isValid = regex.test(String.fromCharCode(keyCode));

    if (!isValid) {
      $("#lblError").html("Only Alphabets and Numbers allowed.");
    }

    return isValid;
  });
});

$("#cpassword").on("keypress", function (evt) {
  var keycode = evt.charCode || evt.keyCode;

  if (keycode == 46) {
    return false;
  }
});

$("#Loginpassword").on("keypress", function (evt) {
  var keycode = evt.charCode || evt.keyCode;

  if (keycode == 46) {
    return false;
  }
});

$("#password").on("keypress", function (evt) {
  var keycode = evt.charCode || evt.keyCode;

  if (keycode == 46) {
    return false;
  }
});

var userName = document.querySelector("#imei");

// userName.addEventListener("input", restrictNumber);

function restrictNumber(e) {
  var newValue = this.value.replace(new RegExp(/[^\d]/, "ig"), "");

  this.value = newValue;
}

$(document).ready(function(){
  $("#searchUser").select();
})

