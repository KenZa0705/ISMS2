const togglePassword = document.querySelector("#togglePassword");
const password = document.querySelector("#password");

password.addEventListener("input", function(){
    if (password.value.length >= 6){
        togglePassword.classList.remove("d-none");
    } else {
        togglePassword.classList.add("d-none");
    }
});

togglePassword.addEventListener("click", function () {

    const type = password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);

    this.classList.toggle("fa-eye");
    this.classList.toggle("fa-eye-slash");
});