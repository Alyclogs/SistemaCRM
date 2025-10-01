import api from "../utils/api.js";
import { abrirModal } from "./index.js";

export function fetchRoles() {
    api.get({
        source: "usuarios",
        action: "obtenerRoles",
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
                    <div class="role-card p-3 border rounded d-flex justify-content-between align-items-center">
                        <div>
                            <h6>${role.rol}</h6>
                            <p class="text-muted">${role.descripcion || 'Sin descripción'}</p>
                        </div>
                        <div class="info-row">
                            <button class="btn btn-icon bg-light" data-id="${role.idrol}" id="btnEditarRol">
                                ${window.icons.edit}
                            </button>
                            <button class="btn btn-icon bg-light" data-id="${role.idrol}" id="btnEliminarRol">
                                ${window.icons.trash}
                            </button>
                        </div>
                    </div>
                `;
            });
            rolesContainer.innerHTML = html;
        }
    });
}

export function guardarRol() {
    const form = document.getElementById("formRol");
    const formData = new FormData(form);
    api.post({
        source: "usuarios",
        action: "guardarRol",
        data: formData,
        onSuccess: () => {
            $("#rolModal").modal("hide");
            fetchRoles();
        }
    });
}

document.addEventListener("click", function (e) {
    if (e.target.closest("#btnNuevoRol")) {
        abrirModal("rol", "Nuevo rol", "md");
    }

    if (e.target.closest("#btnEditarRol")) {
        abrirModal("rol", "Editar rol", "md", e.target.closest("button").dataset.id);
    }

    if (e.target.closest("#btnEliminarRol")) {
        const id = e.target.closest("button").dataset.id;
        if (confirm("¿Está seguro de que desea eliminar este rol? Esta acción no se puede deshacer.")) {
            const formData = new FormData();
            formData.append("idrol", id);

            api.post({
                source: "usuarios",
                action: "eliminarRol",
                data: formData,
                onSuccess: () => {
                    fetchRoles();
                }
            });
        }
    }
});