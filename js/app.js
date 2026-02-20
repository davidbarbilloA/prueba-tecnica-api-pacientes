// Dynamically determine the API URL based on the current location
const BASE_PATH = window.location.pathname.split('/')[1] || 'Prueba_tecnica';
const API_URL = `${window.location.origin}/${BASE_PATH}/pacientes`;
const CATALOG_URL = `${window.location.origin}/${BASE_PATH}/catalogs`;

let isEditing = false;

// Check Auth
if (!localStorage.getItem("authToken")) {
    window.location.href = "login.html";
}

document.addEventListener("DOMContentLoaded", function () {
    addLogoutButton();
    loadCatalogs();
    fetchPatients();

    const patientForm = document.getElementById("patientForm");
    if (patientForm) {
        patientForm.addEventListener("submit", function (e) {
            e.preventDefault();
            if (!validatePatientForm()) return;

            if (isEditing) {
                updatePatient();
            } else {
                registerPatient();
            }
        });
    }

    document.getElementById("cancelBtn").addEventListener("click", resetForm);

    // Dependent Dropdown: Department -> Municipality
    const depSelect = document.getElementById("departamento_id");
    if (depSelect) {
        depSelect.addEventListener("change", function () {
            const depId = this.value;
            loadMunicipios(depId);
        });
    }
});

function addLogoutButton() {
    const nav = document.querySelector(".navbar .container-fluid") || document.querySelector("body");
    const btn = document.createElement("button");
    btn.className = "btn btn-outline-danger position-absolute end-0 me-3 top-0 mt-3";
    btn.textContent = "Cerrar Sesión";
    btn.onclick = () => {
        localStorage.removeItem("authToken");
        window.location.href = "login.html";
    };
    if (document.querySelector(".navbar")) {
        document.querySelector(".navbar").appendChild(btn);
    } else {
        document.body.appendChild(btn);
    }
}

function getAuthHeaders() {
    return {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${localStorage.getItem("authToken")}`
    };
}

function handleResponse(response) {
    if (response.status === 401) {
        alert("Sesión expirada");
        localStorage.removeItem("authToken");
        window.location.href = "login.html";
        return Promise.reject("Unauthorized");
    }
    return response.ok ? response.json() : Promise.reject(response);
}

function loadCatalogs() {
    loadSelect("tipos_documento", "tipo_documento_id");
    loadSelect("generos", "genero_id");
    loadSelect("departamentos", "departamento_id");
}

function loadSelect(catalogName, selectId, selectedValue = null) {
    fetch(`${CATALOG_URL}/${catalogName}`, { headers: getAuthHeaders() })
        .then(handleResponse)
        .then(data => {
            const select = document.getElementById(selectId);
            select.innerHTML = '<option value="">Seleccione...</option>';
            data.forEach(item => {
                const option = document.createElement("option");
                option.value = item.id;
                option.textContent = item.nombre;
                select.appendChild(option);
            });
            if (selectedValue) select.value = selectedValue;
        })
        .catch(error => console.error(`Error loading ${catalogName}:`, error));
}

function loadMunicipios(depId, selectedValue = null) {
    const muniSelect = document.getElementById("municipio_id");
    if (!depId) {
        muniSelect.innerHTML = '<option value="">Seleccione Departamento primero</option>';
        muniSelect.disabled = true;
        return;
    }

    fetch(`${CATALOG_URL}/municipios/${depId}`, { headers: getAuthHeaders() })
        .then(handleResponse)
        .then(data => {
            muniSelect.innerHTML = '<option value="">Seleccione...</option>';
            data.forEach(item => {
                const option = document.createElement("option");
                option.value = item.id;
                option.textContent = item.nombre;
                muniSelect.appendChild(option);
            });
            muniSelect.disabled = false;
            if (selectedValue) muniSelect.value = selectedValue;
        })
        .catch(error => console.error("Error loading municipios:", error));
}

function fetchPatients() {
    fetch(API_URL, { headers: getAuthHeaders() })
        .then(handleResponse)
        .then(data => {
            const table = document.getElementById("patientsTable");
            table.innerHTML = "";

            if (data.length === 0) {
                table.innerHTML = `<tr><td colspan="5" class="text-center">No hay pacientes registrados.</td></tr>`;
                return;
            }

            data.forEach(patient => {
                const fullName = `${patient.nombre1} ${patient.apellido1}`;
                const email = patient.correo || '-';
                const doc = patient.numero_documento || '-';

                table.innerHTML += `
                    <tr>
                        <td>${patient.id}</td>
                        <td>${fullName}</td>
                        <td>${email}</td>
                        <td>${doc}</td>
                        <td>
                            <button class="btn btn-sm btn-warning me-2" onclick='editPatient(${JSON.stringify(patient)})'>Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="deletePatient(${patient.id})">Eliminar</button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(error => {
            console.error("Fetch Error:", error);
            if (error !== "Unauthorized") showAlert("Error al cargar los pacientes.", "danger");
        });
}

function validatePatientForm() {
    const requiredFields = [
        "tipo_documento_id", "numero_documento", "nombre1",
        "apellido1", "genero_id", "departamento_id", "municipio_id"
    ];

    for (const field of requiredFields) {
        const value = document.getElementById(field).value.trim();
        if (!value) {
            showAlert(`El campo ${field.replace('_', ' ').toUpperCase()} es obligatorio.`, "warning");
            return false;
        }
    }

    const email = document.getElementById("correo").value.trim();
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showAlert("El correo electrónico no es válido.", "warning");
        return false;
    }

    return true;
}

function registerPatient() {
    const data = getFormData();
    sendRequest(API_URL, "POST", data, "registrado");
}

function updatePatient() {
    const id = document.getElementById("patientId").value;
    const data = getFormData();
    sendRequest(`${API_URL}/${id}`, "PUT", data, "actualizado");
}

function deletePatient(id) {
    if (!confirm("¿Estás seguro de que quieres eliminar este paciente?")) return;
    sendRequest(`${API_URL}/${id}`, "DELETE", {}, "eliminado");
}

function sendRequest(url, method, data, actionPastTense) {
    fetch(url, {
        method: method,
        headers: getAuthHeaders(),
        body: JSON.stringify(data)
    })
        .then(handleResponse)
        .then(result => {
            if (result.error) {
                showAlert(result.error, "danger");
            } else {
                showAlert(`Paciente ${actionPastTense} exitosamente.`, "success");
                resetForm();
                fetchPatients();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            if (error !== "Unauthorized") showAlert("Error de conexión.", "danger");
        });
}

function editPatient(patient) {
    isEditing = true;
    document.getElementById("patientId").value = patient.id;
    document.getElementById("tipo_documento_id").value = patient.tipo_documento_id;
    document.getElementById("numero_documento").value = patient.numero_documento;
    document.getElementById("nombre1").value = patient.nombre1;
    document.getElementById("nombre2").value = patient.nombre2;
    document.getElementById("apellido1").value = patient.apellido1;
    document.getElementById("apellido2").value = patient.apellido2;
    document.getElementById("genero_id").value = patient.genero_id;
    document.getElementById("correo").value = patient.correo;
    document.getElementById("departamento_id").value = patient.departamento_id;

    // Trigger change to load municipios, then select the correct one
    loadMunicipios(patient.departamento_id, patient.municipio_id);

    // UI Updates
    document.getElementById("submitBtn").textContent = "Actualizar Paciente";
    document.getElementById("submitBtn").className = "btn btn-warning";
    document.getElementById("cancelBtn").classList.remove("d-none");
    window.scrollTo(0, 0);
}

function resetForm() {
    isEditing = false;
    document.getElementById("patientForm").reset();
    document.getElementById("patientId").value = "";
    document.getElementById("municipio_id").innerHTML = '<option value="">Seleccione Departamento primero</option>';
    document.getElementById("municipio_id").disabled = true;

    document.getElementById("submitBtn").textContent = "Registrar Paciente";
    document.getElementById("submitBtn").className = "btn btn-success";
    document.getElementById("cancelBtn").classList.add("d-none");
}

function getFormData() {
    return {
        tipo_documento_id: document.getElementById("tipo_documento_id").value,
        numero_documento: document.getElementById("numero_documento").value,
        nombre1: document.getElementById("nombre1").value,
        nombre2: document.getElementById("nombre2").value,
        apellido1: document.getElementById("apellido1").value,
        apellido2: document.getElementById("apellido2").value,
        genero_id: document.getElementById("genero_id").value,
        departamento_id: document.getElementById("departamento_id").value,
        municipio_id: document.getElementById("municipio_id").value,
        correo: document.getElementById("correo").value
    };
}

function showAlert(message, type) {
    const alertContainer = document.getElementById("alertContainer");
    alertContainer.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    setTimeout(() => {
        const alert = document.querySelector(".alert");
        if (alert) {
            alert.classList.remove("show");
            setTimeout(() => alert.remove(), 150);
        }
    }, 5000);
}