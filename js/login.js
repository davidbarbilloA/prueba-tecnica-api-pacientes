const BASE_PATH = window.location.pathname.split('/')[1] || 'Prueba_tecnica';
const LOGIN_URL = `${window.location.origin}/${BASE_PATH}/login`;

document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    fetch(LOGIN_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
    })
        .then(response => response.json())
        .then(data => {
            if (data.token) {
                localStorage.setItem("authToken", data.token);
                window.location.href = "index.html";
            } else {
                showAlert(data.message || "Error al iniciar sesión", "danger");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showAlert("Error de conexión", "danger");
        });
});

function showAlert(message, type) {
    const alertDiv = document.getElementById("loginAlert");
    alertDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
}
