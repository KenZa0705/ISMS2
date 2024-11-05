<!doctype html>
<html lang="en">

<head>
    <title>Title</title>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <?php include '../cdn/head.html'; ?>

    <script src="form_validation.js"></script>
</head>

<body>
    <main>
        <div class="main_bg">
            <section class="header_container bg-white">
                <div class="container">
                    <div class="row">
                        <div class="col-3 d-flex justify-content-start">
                            <img src="pics/brand.png" alt="" class="img-fluid">
                        </div>
                        <div class="col-6 d-flex justify-content-center align-items-center">
                            <h1 class="text-center">ANNOUNCEMENTS</h1>
                        </div>
                        <div class="col-3 d-flex justify-content-end">
                            <img src="pics/bsu_logo.png" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
            </section>

            <section class="login_container py-5 px-4 d-flex justify-content-center align-items-center">
                <div class="container">
                    <div class="row d-flex justify-content-center align-items-center">
                        <div class="form-container col-12 col-md-5 bg-body-tertiary p-4">
                            <h2 class="text-center">Login</h2>
                            <div class="form-body p-2">
                                <form id="login_form" action="login_script.php" method="POST">
                                    <div class="form-group mb-3">
                                        <?php echo $_GET['message'] ?? ''; ?>
                                        <input id="email" name="email" type="email" class="form-control p-3" placeholder="Email">
                                    </div>
                                    <div class="form-group mb-3 position-relative">
                                        <input id="password" name="password" type="password" class="form-control p-3" placeholder="Password">
                                        <i class="fas fa-eye position-absolute d-none" id="togglePassword" style="top: 50%; right: 15px; transform: translateY(-27px); cursor: pointer;"></i>
                                        <p class="mt-3">*Password is case sensitive</p>
                                    </div>
                                    <div class="form-group recaptcha_container d-flex justify-content-center mb-3">
                                        <div class="g-recaptcha" data-sitekey="6LfgN1kqAAAAAFS00KZj9_LgtXht8ISAUQgzU_YH"></div>
                                    </div>
                                    <div class="button_container d-flex justify-content-center">
                                        <button id="signin" type="submit" class="btn btn-warning px-4 mb-2">Sign in</button>
                                    </div>

                                </form>
                                <!-- Trigger Button -->
                                <a href="#" id="resetPasswordBtn" data-bs-toggle="modal" data-bs-target="#otp-modal">Forgot Password? Click here</a>

                                <!-- Modal Structure -->
                                <div id="resetPasswordModal" class="modal">
                                    <div class="modal-content">
                                        <span class="close">&times;</span>
                                        <h2>Reset Password</h2>
                                        <form method="POST" action="send_otp.php">
                                            <label for="email">Enter your email:</label>
                                            <input type="email" name="email" required>
                                            <div class="button_container d-flex justify-content-center">
                                                <button type="submit" class="btn btn-warning px-4 mb-2">Send OTP</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="footer bg-black">
                <div class="container">
                    <div class="row pt-3 d-flex">
                        <div class="col-8">
                            <p class="BSU">BATANGAS STATE UNIVERSITY</p>
                            <p class="description">A premier national university that develops leaders in the global knowledge economy</p>
                            <p class="copyright">Copyright &copy; 2023</p>
                        </div>
                        <div class="col-4">
                            <div class="img-container d-flex justify-content-end align-items-center">
                                <img class="img-fluid" src="pics/redspartan-logo.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- otp modal -->
            <div class="modal otp-modal fade" id="otp-modal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content p-0">
                        <div class="modal-header d-flex justify-content-between align-items-center">
                            <h2 class="modal-title" id="otpModalLabel">Reset Password</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="send_otp.php">
                                <input type="email" name="email" placeholder="Enter your Email" required class="form-control mb-3">
                                <div class="button_container d-flex justify-content-center">
                                    <button type="submit" class="btn btn-warning px-4 mb-2">Send OTP</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            <!-- recaptcha modal -->
            <div class="modal recaptcha-modal fade" id="recaptchaModal" tabindex="-1" aria-labelledby="recaptchaModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content text-center">
                        <div class="modal-body">
                            <div class="mb-3">
                                <span class="text-danger fs-1">&#10060;</span>
                            </div>
                            <h5 class="modal-title mb-2" id="recaptchaModalLabel">Error</h5>
                            <p>Please Complete the Recaptcha First.</p>
                            <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="password.js"></script>
        <script src="form_validation.js"></script>
    </main>
    <?php include '../cdn/body.html'; ?>
</body>

</html>