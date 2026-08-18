const isLoggedIn = localStorage.getItem("glowCareLoggedIn");
const authLink = document.getElementById("auth-link");

if (authLink) {
    if (isLoggedIn === "true") {
        authLink.innerText = "Logout";
        authLink.href = "#";
        authLink.onclick = function() {
            localStorage.removeItem("glowCareLoggedIn");
            window.location.reload();
        };
    } else {
        authLink.innerText = "Login";
        authLink.href = "login.php";
    }
}