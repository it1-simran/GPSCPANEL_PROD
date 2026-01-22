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
                    <h2><strong>Login</strong></h2>

                </div>

                <div class="form-body bg-white padding-20">
                    @isset($url)
                    <form method="POST" action='{{ url("login/$url") }}' aria-label="{{ __('Login') }}">
                        @else
                        <form method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}">
                            @endisset
                            @csrf
                            <div class="form-group">
                                <label for="email-address">{{ __('E-Mail Address') }} <sup class='text-danger'>*</sup></label>
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                                @endif
                            </div>

                            <div class="form-group bgx-margin-top-1">
                                <label for="first-name">{{ __('Password') }} <sup class='text-danger'>*</sup></label>
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-md-6 text-center">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : 'checked' }}> {{ __('Remember Me') }} <sup class='text-danger'>*</sup>
                                        </label>
                                    </div>
                                    @if ($errors->has('remember'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('remember') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="col-md-6 text-center">
                                    <button type="submit" class="btn btn-green btn-raised btn-flat"> {{ __('Login') }}</button>
                                </div>
                            </div>
                            <hr />
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <h4>Forgot Password?</h4>
                                    <p><a href="/forgot-password">Contact to Administrator</a></p>
                                </div>
                            </div>


                        </form>


                </div><!--/form-body-->

            </div><!--/col-md-12-->

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