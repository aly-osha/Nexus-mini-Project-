document.f1.addEventListener("submit", function (e) {
    e.preventDefault();
    const name = document.getElementsByName("name")[0].value.trim();
    const role = document.getElementsByName("role");
    const email = document.getElementsByName("email")[0].value.trim();
    const dob = document.getElementsByName("dob")[0].value;
    const address = document.getElementsByName("address")[0].value.trim();
    const uname = document.getElementsByName("uname")[0].value.trim();
    const password = document.getElementsByName("pword")[0].value;
    const repassword = document.getElementsByName("repword")[0].value;
    if (name === "" || email === "" || dob === "" || address === "" || uname === "" || password === "" || repassword === "") {
        alert("All fields are required.");
        return;
    }
        let roleSelected = false;
    for (let r of role) {
        if (r.checked) {
            roleSelected = true;
            break;
        }
    }
     if (!roleSelected) {
        alert("Please select a role: Teacher or Student.");
        return;
    }
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return;
    }
        if (password !== repassword) {
        alert("Passwords do not match.");
        return;
    }


      e.target.submit();
});