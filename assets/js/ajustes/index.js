import api from "../utils/api.js";

function fetchRoles() {
    api.get({
        source: "usuarios",
        action: "listarRoles",
        onSuccess: (roles) => {
            const rolesContainer = document.getElementById("rolesList");
            rolesContainer.innerHTML = "";

            if (roles.length === 0) {
                rolesContainer.innerHTML = "<p class='text-muted'>No hay roles disponibles.</p>";
                return;
            }

            let html = "";

            roles.forEach(role => {
                html += `
                    <div class="role-card p-3 mb-3 border rounded">
                        <h5>${role.nombre}</h5>
                        <p class="text-muted">${role.descripcion || 'Sin descripción'}</p>
                        <div class="info-row">
                            <button class="btn btn-icon bg-light" data-id="${role.idrol}" id="btnEditarRol">
                                ${window.icons.edit}<span>Editar</span>
                            </button>
                            <button class="btn btn-icon bg-light" data-id="${role.idrol}" id="btnEliminarRol">
                                ${window.icons.trash}<span>Eliminar</span>
                            </button>
                        </div>
                    </div>
                `;
            });

            rolesContainer.innerHTML = html;
        }
    });
}

function fetchCamposExtra() {
    api.get({
        source: "ajustes",
        action: "listar_campos",
        onSuccess: (campos) => {
            const camposContainer = document.getElementById("camposList");
            camposContainer.innerHTML = "";
            if (campos.length === 0) {
                camposContainer.innerHTML = "<p class='text-muted'>No hay campos personalizados disponibles.</p>";
                return;
            }
            let html = "";
            campos.forEach(campo => {
                html += `
                    <div class="campo-card p-3 mb-3 border rounded d-flex justify-content-between align-items-center">
                        <div>
                            <h5>${campo.nombre} <span class="badge bg-secondary">${campo.tipo_dato}</span></h5>
                        <p class="text-muted">Referencia: ${campo.tipo_referencia} (ID: ${campo.idreferencia})</p>
                        </div>
                        <div class="info-row">
                            <button class="btn btn-icon bg-light" data-id="${campo.idcampo}" id="btnEditarCampo">
                                ${window.icons.edit}<span>Editar</span>
                            </button>
                            <button class="btn btn-icon bg-light" data-id="${campo.idcampo}" id="btnEliminarCampo">
                                ${window.icons.trash}<span>Eliminar</span>
                            </button>
                        </div>
                    </div>
                `;
            });
            camposContainer.innerHTML = html;
        }
    });
}

document.addEventListener("click", function (e) {
    if (e.target.closest("#btnNuevoRol")) {
        fetch(baseurl + "views/components/ajustes/formRol.php?id=" + (e.target.dataset.id || ""))
            .then(response => response.text())
            .then(html => {
                $("#ajustesModalLabel").text("Nuevo rol");
                $("#ajustesModalBody").html(html);
                $("#ajustesModal").modal("show");
            });
    }
    else if (e.target.closest("#btnNuevoCampo")) {
        fetch(baseurl + "views/components/ajustes/formCampos.php?id=" + (e.target.dataset.id || ""))
            .then(response => response.text())
            .then(html => {
                $("#ajustesModalLabel").text("Nuevo campo personalizado");
                $("#ajustesModalBody").html(html);
                $("#ajustesModal").modal("show");
            });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const rolesSection = document.getElementById("rolesSection");
    const camposSection = document.getElementById("camposSection");

    document.querySelectorAll(".info-container.clickable").forEach(container => {
        container.addEventListener("click", () => {
            const target = container.getAttribute("data-target");
            if (target === "roles") {
                rolesSection.style.display = "block";
                camposSection.style.display = "none";
                fetchRoles();
            }
            else if (target === "campos") {
                rolesSection.style.display = "none";
                camposSection.style.display = "block";
                fetchCamposExtra();
            }
        });
    });
    camposSection.style.display = "";
    rolesSection.style.display = "none";
});