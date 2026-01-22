<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="apple-mobile-web-app-capable" content="yes" />
   <meta name="description" content="">
   <meta name="keywords" content="thema bootstrap template, thema admin, bootstrap, admin template, bootstrap admin">
   <meta name="author" content="LanceCoder">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <link rel="shortcut icon" href="">
   <title>GPS Control Panel</title>
   <!-- Start Global plugin css -->
   <link href="../../../assets/css/global-plugins.css" rel="stylesheet">
   <link href="../../../assets/vendors/jquery-icheck/skins/all.css" rel="stylesheet" />
   <!-- Custom styles for this template -->
   <link href="../../../assets/css/theme.css" rel="stylesheet">
   <link href="../../../assets/css/style-responsive.css" rel="stylesheet" />
   <link href="../../../assets/css/class-helpers.css" rel="stylesheet" />
   <!--Color schemes-->
   <link href="../../../assets/css/colors/green.css" rel="stylesheet">
   <!--Fonts-->
   <link href="../../../assets/fonts/Indie-Flower/indie-flower.css" rel="stylesheet" />
   <link href="../../../assets/fonts/Open-Sans/open-sans.css?family=Open+Sans:300,400,700" rel="stylesheet" />
</head>

<body id="default-scheme" class="form-background">
   <!--main content start-->
   <div class="bg-overlay"></div>
   <section class="registration-login-wrapper">
      <!--======== START LOGIN ========-->
      <div class="row page-registration ">
         <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
            <div class="form-header bg-white padding-10 text-center">
               <h2><strong>Forgot Password</strong></h2>
            </div>
            <div class="form-body bg-white padding-20 forgotPasswordForm">
               <form method="POST" name="forgotPassword" id="forgotPassword" action="" onsubmit="return false" aria-label="{{ __('Login') }}">
                  @csrf
                  <div class="form-group ">
                     <label for="email-address">{{ __('E-Mail Address') }} <sup class='text-danger'>*</sup></label>
                     <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus @if ($errors->has('email')) />
                     <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                     </span>
                     @endif
                  </div>
                  <div class="row bgx-margin-top-1">
                     <div class="col-md-12 text-center">
                        <button type="submit" name="submit" id="submit" class="btn btn-green btn-raised btn-flat"> {{ __('Get code') }}</button>
                     </div>
                  </div>
                  <div class="row bgx-margin-top-1">
                     <div class="col-md-12 text-center">
                     <p><a href="javascript:void(0);" onclick="window.history.back();">Back</a></p>
                     </div>
                  </div>
               </form>
            </div>

            <!--/form-body-->
         </div>
         <div class="form-body bg-white padding-20 otpCheckForm" style='display: none;'>
            <p class="info">An otp has been sent to <span id="getSendMail"></span></p>
            <form method="POST" name="verifyCode" id="verifyCode" action="" onsubmit="return false" aria-label="{{ __('Login') }}">
               @csrf
               <div class="form-group">
                  <label for="email-address">{{ __('OTP Code') }} <sup class='text-danger'>*</sup></label>
                  <div class="otp-input-fields">
                     <input type="text" name="otp[]" class="otp__digit otp__field__1" maxlength="1" required>
                     <input type="text" name="otp[]" class="otp__digit otp__field__2" maxlength="1" required>
                     <input type="text" name="otp[]" class="otp__digit otp__field__3" maxlength="1" required>
                     <input type="text" name="otp[]" class="otp__digit otp__field__4" maxlength="1" required>
                     <input type="text" name="otp[]" class="otp__digit otp__field__5" maxlength="1" required>
                     <input type="text" name="otp[]" class="otp__digit otp__field__6" maxlength="1" required>
                  </div>
                  <!-- <input id="otp" type="text" class="form-control" name="otp" value="" required autofocus /> -->
               </div>
               <div class="row bgx-margin-top-1">
                  <div class="col-md-12 text-center">
                     <button type="submit" name="submit" id="submit" class="btn btn-green btn-raised btn-flat"> {{ __('Verify Code') }}</button>
                     <input type='hidden' name='verifyEmail' id='verifyEmail' value='' />

                  </div>
               </div>
               <div class="row bgx-margin-top-1">
                  <div class="col-md-12 text-center">
                     <p>didn't get Code? <a href="#">resend</a></p>
                  </div>
               </div>
               <div class="row bgx-margin-top-1">
                  <div class="col-md-12 text-center">
                     <p><a href="javascript:void(0);" onclick="window.history.back();">Back</a></p>
                  </div>
               </div>
            </form>
         </div>
         <div class="form-body bg-white padding-20 setpassword" style='display: none;'>
            <form method="POST" name="setNewPassword" id="setNewPassword" action="" onsubmit="return false" aria-label="{{ __('Login') }}">
               @csrf
               <span class="text-danger errorsText"></span>
               <div class="form-group">
                  <label for="password">{{ __('Password') }} <sup class='text-danger'>*</sup></label>
                  <input id="newPassword" type="password" class="form-control" name="newPassword" value="" required autofocus />
               </div>
               <div class="form-group">
                  <label for="confirmNewPassword">{{ __('Confirm Password') }} <sup class='text-danger'>*</sup></label>
                  <input id="confirmNewPassword" type="password" class="form-control" name="confirmNewPassword" value="" required autofocus />
               </div>
               <div class="row bgx-margin-top-1">
                  <div class="col-md-12 text-center">
                     <button type="submit" name="submit" id="submit" class="btn btn-green btn-raised btn-flat"> {{ __('Submit') }}</button>
                     <input type='hidden' name='userId' id='userId' value='' />
                  </div>
               </div>
               <div class="row bgx-margin-top-1">
                  <div class="col-md-12 text-center">
                     <p><a href="javascript:void(0);" onclick="window.history.back();">Back</a></p>
                  </div>
               </div>
            </form>
         </div>
         <!--/col-md-12-->
      </div>
      <!--/row-->
      <!--======== END LOGIN ========-->
      <!--======== END LOGIN ========-->
   </section>
   <script src="../../../assets/js/global-plugins.js"></script>
   <!--common script init for all pages-->
   <script src="../../../assets/js/theme.js" type="text/javascript"></script>
   <!-- For Form Elements Page Only -->
   <script src="../../../assets/js/forms.js"></script>
   <script src="../../../assets/js/form-validation.js"></script>
   <script src="../../../assets/js/form-wizard.js"></script>
   <script src="../../../assets/js/form-plupload.js"></script>
   <script src="../../../assets/js/form-x-editable.js"></script>
   <!-- For Login and registration page Only -->
   <script src="../../../assets/vendors/backstretch/jquery.backstretch.min.js"></script>
   <script src="../../../assets/js/registration-login.js"></script>
   <script type="text/javascript">
      $(document).ready(function() {  
         $('#forgotPassword').submit(function() {
            var formData = $(this).serialize();
            $.ajax({
               url: "{{ route('send.otp') }}",
               type: "POST",
               data: formData,
               success: function(response) {
                  let result = JSON.parse(response);
                  if (result.status == 200) {
                     $('.forgotPasswordForm').hide();
                     $('.otpCheckForm').show();
                     $('#verifyEmail').val(result.email);
                     var email = result.email; // Replace with your actual email address
                     var hiddenEmail = email.charAt(0) + "********" + email.slice(email.indexOf("@"));

                     $('#getSendMail').text(hiddenEmail);
                  } else {
                     alert(result.message)
                  }
                  // Update your HTML with the received data
               },
               error: function(xhr) {
                  console.log(xhr.responseText); // Handle error
               }
            });

         });
         $('#verifyCode').submit(function() {
            var formData = $(this).serialize();
            $.ajax({
               url: "{{ route('verify.otp') }}",
               type: "POST",
               data: formData,
               success: function(response) {
                  let result = JSON.parse(response);
                  if (result.status == 200) {
                     $('.otpCheckForm').hide();
                     $('.setpassword').show();
                     $('#userId').val(result.id);
                  } else {
                     alert(result.message)
                  }
                  // Update your HTML with the received data
               },
               error: function(xhr) {
                  console.log(xhr.responseText); // Handle error
               }
            });
         });
         $('#setNewPassword').submit(function() {
            let password = $('#newPassword').val();
            let passwordLength = password.length;
            if(passwordLength < 4){
                $('.errorsText').text("password should be a greater than 4")
                return;
            }
            var formData = $(this).serialize();
            $.ajax({
               url: "{{ route('reset.password') }}",
               type: "POST",
               data: formData,
               success: function(response) {
                  let result = JSON.parse(response);
                  if (result.status == 200) {
                     alert(result.message);
                     window.history.back();
                  } else {
                     alert(result.message)
                  }
               },
               error: function(xhr) {
                  console.log(xhr.responseText); // Handle error
               }
            });
         });
         var otp_inputs = document.querySelectorAll(".otp__digit");
         var mykey = "0123456789".split("");

         otp_inputs.forEach((input) => {
            input.addEventListener("input", handleInput);
            input.addEventListener("paste", handlePaste);
         });

         function handleInput(event) {
            let current = event.target;
            let index = Array.from(current.parentNode.children).indexOf(current) + 1; // Index of current input

            if (event.inputType === 'deleteContentBackward' && current.value === '') {
               if (index > 1) {
                  let previous = current.previousElementSibling;
                  previous.focus();
               }
            }

            if (event.inputType === 'insertText') {
               if (index < 6 && mykey.indexOf(current.value) === -1) {
                  current.value = ''; // Clear input if not valid
               } else if (index < 6 && mykey.indexOf(current.value) !== -1) {
                  let next = current.nextElementSibling;
                  next.focus();
               }
            }

            updateOtpDisplay();
         }

         function handlePaste(event) {
            event.preventDefault();
            let pasteData = event.clipboardData.getData('text');
            let pasteChars = pasteData.split('');

            let idx = 0;
            otp_inputs.forEach((input) => {
               if (idx < pasteChars.length) {
                  input.value = pasteChars[idx];
                  idx++;
               } else {
                  input.value = '';
               }
            });

            updateOtpDisplay();
         }

         function updateOtpDisplay() {
            let _finalKey = "";
            otp_inputs.forEach(input => {
               _finalKey += input.value;
            });

            let otpDisplay = document.querySelector("#_otp");
            if (_finalKey.length === 6) {
               otpDisplay.classList.replace("_notok", "_ok");
               otpDisplay.innerText = _finalKey;
            } else {
               otpDisplay.classList.replace("_ok", "_notok");
               otpDisplay.innerText = _finalKey;
            }
         }

      });
   </script>
</body>

</html>
<!--===== Footer End ========-->