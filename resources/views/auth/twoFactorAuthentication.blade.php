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
                    <h2><strong>Two Factor Authentication</strong></h2>
                    <p>We’ve sent a 6-digit OTP to your email. Please enter it below to continue.</p>
                </div>

                <div class="form-body bg-white padding-20">
                    <form method="POST" action="{{ route('2fa.submit') }}">
                        @csrf
                        <input type="hidden" name="email" value="{{ session('email') }}">

                        <div class="mb-3">
                            <label for="two_factor_code" class="form-label">Enter OTP</label>
                            <input
                                type="text"
                                name="two_factor_code"
                                id="two_factor_code"
                                class="form-control @error('two_factor_code') is-invalid @enderror"
                                required>
                            {{-- Error Marker --}}
                            @error('two_factor_code')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Verify</button>
                    </form>
                </div><!--/form-body-->

            </div>


        </div><!--/row-->
        <!--======== END LOGIN ========-->
        <!--======== END LOGIN ========-->

    </section>
    <!--======== Main Content End ========-->


    <!--===== Footer Start ========-->

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
</body>

</html>

<!--===== Footer End ========-->