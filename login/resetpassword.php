<!doctype html>
<html lang="en">

<head>
    <title>Title</title>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.3.2 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
        crossorigin="anonymous" />

    <link rel="stylesheet" href="login.css">

    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <!-- recaptcha -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- jquery library -->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js" integrity="sha512-KFHXdr2oObHKI9w4Hv1XPKc898mE4kgYx58oqsc/JqqdLMDI4YjOLzom+EMlW8HFUd0QfjfAvxSL6sEq/a42fQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="form_validation.js"></script>
</head>

<body>
    <main>
        <div class="main_bg">
            <section class="header_container bg-white">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-3 d-flex justify-content-center align-items-center">
                            <img src="pics/bsu_logo.png" alt="" class="img-fluid me-3">
                        </div>
                        <div class="col-6 d-flex justify-content-center align-items-center">
                            <h1 class="text-center">ANNOUNCEMENTS</h1>
                        </div>
                        <div class="col-3 d-flex justify-content-center align-items-center">
                            <img src="pics/bsu_logo.png" alt="" class="img-fluid ms-3">
                        </div>
                    </div>
                </div>
            </section>
            <section class="login_container py-5 px-4 d-flex justify-content-center align-items-center">
                <div class="container">
                    <div class="row d-flex justify-content-center align-items-center">
                        <div class="form-container col-12 col-md-6 bg-body-tertiary p-4">
                            <h2 class="text-center">Reset Password</h2>
                            <div class="form-body p-2">
                                <form id="new_password_form" method="POST" action="update_password.php">
                                    <div class="form-group mb-3">
                                        <label for="email">Enter your email:</label>
                                        <input type="email" name="email" value="<?php echo $_GET['email']; ?>" readonly class="form-control p-3">
                                    </div>
                                    <div class="form-group mb-3 position-relative">
                                        <label for="password">Enter new password:</label>
                                        <input type="password" id="password" name="password" class="form-control p-3">
                                        <i class="fas fa-eye position-absolute d-none" id="togglePassword" style="top: 50%; right: 15px; transform: translateY(-27px); cursor: pointer;"></i>
                                        <p class="mt-3">*Password is case sensitive</p>
                                    </div>    
                                    <input type="hidden" name="type" value="<?php echo $_GET['type']; ?>">
                                    <div class="button_container d-flex justify-content-center">
                                        <input type="submit" value="Update Password" class="btn btn-warning px-4 mb-2">
                                    </div>
                                </form>
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
                            <div class="img-container d-flex justify-content-center align-items-center">
                                <img class="img-fluid red_spartan d-flex align-items-center justify-content-center" src="pics/red spartan.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <script src="password.js"></script>
        <script src="form_validation.js"></script>
    </main>
    <!-- Bootstrap JavaScript Libraries -->
    <script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>
</body>

</html>