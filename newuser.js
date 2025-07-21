document.addEventListener("DOMContentLoaded", function () {
    const unameInput = document.getElementsByName("uname")[0];

    // Create and insert message span
    const msgSpan = document.createElement("span");
    unameInput.parentNode.appendChild(msgSpan);

    let timeout = null;

    unameInput.addEventListener("input", function () {
        const uname = unameInput.value.trim();

        // Clear previous timeout
        clearTimeout(timeout);

        // Debounce check (so it doesn't check every keystroke immediately)
        timeout = setTimeout(() => {
            if (uname.length === 0) {
                msgSpan.textContent = "";
                return;
            }

            fetch("user_name.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "uname=" + encodeURIComponent(uname)
            })
                .then(response => response.text())
                .then(data => {
                    if (data.includes("already taken")) {
                        msgSpan.textContent = "Username already taken";
                        msgSpan.style.color = "red";
                    } else {
                        msgSpan.textContent = "Username is available";
                        msgSpan.style.color = "green";
                    }
                })
                .catch(err => {
                    console.error("Error:", err);
                    msgSpan.textContent = "⚠️ Error checking username";
                    msgSpan.style.color = "orange";
                });
        }, 500); // wait 500ms after typing stops
    });
});
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
            k = r.value;
            break;
        }
    }
    if (!roleSelected) {
        alert("Please select a role: Teacher or Student.");
        return;
    }
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    if (k === "student") {
        if (age < 16) {
            alert("You must be at least 16 years old to register.");
            return;
        }
    }
    else if (k === "teacher") {
        if (age < 25) {
            alert("You must be at least 25 years old to register.");
            return;
        }
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