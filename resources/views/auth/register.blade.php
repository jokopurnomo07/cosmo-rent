<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
    <meta name="description" content="Vuexy admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Vuexy admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="PIXINVENT">
    <title>Register Page - Cosmo Rent</title>
    <link rel="apple-touch-icon" href="{{ asset('template_login') }}/app-assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('template_login') }}/app-assets/images/ico/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">
    <!-- BEGIN: Vendor CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- END: Vendor CSS-->
    <!-- BEGIN: Theme CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/app-assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/app-assets/css/bootstrap-extended.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/app-assets/css/colors.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/app-assets/css/components.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/app-assets/css/themes/dark-layout.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/app-assets/css/themes/bordered-layout.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/app-assets/css/themes/semi-dark-layout.min.css">
    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/app-assets/css/core/menu/menu-types/vertical-menu.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/app-assets/css/plugins/forms/form-validation.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/app-assets/css/pages/authentication.css">
    <!-- END: Page CSS-->
    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('template_login') }}/assets/css/style.css">
    <!-- END: Custom CSS-->
</head>
<body class="vertical-layout vertical-menu-modern blank-page navbar-floating footer-static  " data-open="click" data-menu="vertical-menu-modern" data-col="blank-page">
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row"></div>
            <div class="content-body">
                <div class="auth-wrapper auth-basic px-2">
                    <div class="auth-inner my-2">
                        <div class="card mb-0">
                            <div class="card-body">
                                <a href="index.html" class="brand-logo">
                                    <svg viewbox="0 0 139 95" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" height="28">
                                        <!-- SVG content -->
                                    </svg>
                                    <h2 class="brand-text text-primary ms-1">Cosmo Rent</h2>
                                </a>
                                <h4 class="card-title mb-1">Adventure starts here 🚀</h4>
                                <p class="card-text mb-2">Make your app management easy and fun!</p>
                                <form class="auth-register-form mt-2" action="{{ route('register') }}" method="POST">
                                    @csrf
                                    <div class="mb-1">
                                        <label for="register-username" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="register-username" name="name" placeholder="johndoe" aria-describedby="register-username" tabindex="1" autofocus />
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="mb-1">
                                        <label for="register-email" class="form-label">Email</label>
                                        <input type="text" class="form-control @error('email') is-invalid @enderror" id="register-email" name="email" placeholder="john@example.com" aria-describedby="register-email" tabindex="2" />
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="mb-1">
                                        <label for="register-password" class="form-label">Password</label>
                                        <div class="input-group input-group-merge form-password-toggle">
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="register-password" name="password" placeholder="********" aria-describedby="register-password" tabindex="3" />
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="mb-1">
                                        <label for="register-password-confirm" class="form-label">Confirm Password</label>
                                        <div class="input-group input-group-merge form-password-toggle">
                                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="register-password-confirm" name="password_confirmation" placeholder="********" aria-describedby="register-password-confirm" tabindex="4" />
                                            @error('password_confirmation')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="mb-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="register-privacy-policy" tabindex="5" />
                                            <label class="form-check-label" for="register-privacy-policy"> I agree to the <a href="javascript:void(0);">privacy policy & terms</a></label>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary w-100" tabindex="6">Sign up</button>
                                </form>
                                <p class="text-center mt-2">
                                    <span>Already have an account?</span>
                                    <a href="auth-login-basic.html">
                                        <span>Sign in instead</span>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Content-->
    <!-- BEGIN: Vendor JS-->
    <script src="{{ asset('template_login') }}/app-assets/vendors/js/vendors.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>
    <!-- BEGIN: Page JS-->
    <script src="{{ asset('template_login') }}/app-assets/js/core/app-menu.min.js"></script>
    <script src="{{ asset('template_login') }}/app-assets/js/core/app.min.js"></script>
    <script src="{{ asset('template_login') }}/app-assets/js/scripts/pages/auth-register.min.js"></script>
    <!-- END: Page JS-->
</body>
</html>
