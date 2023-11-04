const showHidePw = document.querySelectorAll(".showHidePw"),
  pw = document.querySelectorAll(".password"),
  signUp = document.querySelector(".signup-link"),
  login = document.querySelector(".login-link"),
  loginsection = document.querySelector(".login"),
  signupsection = document.querySelector(".signup"),
  submit_login_btn = document.querySelector(".login input[type=submit]"),
  errors = document.querySelectorAll('span.error');

showHidePw.forEach((eyeIcon) => {
  eyeIcon.addEventListener("click", () => {
    pw.forEach((p) => {
      if (p.type == "password") {
        p.type = "text";
        showHidePw.forEach((icon) => {
          icon.classList.replace("fa-eye-slash", "fa-eye");
        });
      } else {
        p.type = "password";
        showHidePw.forEach((icon) => {
          icon.classList.replace("fa-eye", "fa-eye-slash");
        });
      }
    });
  });
});