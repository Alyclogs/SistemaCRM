import api from "../utils/api.js";

function fetchAjustes() {
    fetchRoles();
    fetchCamposExtra();
}

function fetchRoles() {
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

function fetchCamposExtra() {
    api.get({
        source: "ajustes",
        action: "listar_campos_extra",
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
                    <tr>
                        <td>${campo.nombre}</td>
                        <td>${campo.tipo_dato}</td>
                        <td>${campo.longitud || 'N/A'}</td>
                        <td>${campo.requerido ? 'Sí' : 'No'}</td>
                        <td>${campo.tabla}</td>
                        <td>${campo.valor_inicial ? (Array.isArray(campo.valor_inicial) ? campo.valor_inicial.join(", ") : campo.valor_inicial) : 'N/A'}</td>
                        <td>
                            <div class="info-row">
                                <button class="btn btn-icon bg-light" data-id="${campo.idcampo}" id="btnEditarCampo">
                                    ${window.icons.edit}
                                </button>
                                <button class="btn btn-icon bg-light" data-id="${campo.idcampo}" id="btnEliminarCampo">
                                    ${window.icons.trash}
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            camposContainer.innerHTML = html;
        }
    });
}

document.addEventListener("click", function (e) {
    if (e.target.closest("#btnNuevoRol")) {
        fetch(baseurl + "views/components/ajustes/formRol.php")
            .then(response => response.text())
            .then(html => {
                $("#ajustesModalLabel").text("Nuevo rol");
                $("#ajustesModalBody").html(html);
                $("#ajustesModal").modal("show");
            });
    }

    if (e.target.closest("#btnNuevoCampo")) {
        fetch(baseurl + "views/components/ajustes/formCampos.php")
            .then(response => response.text())
            .then(html => {
                $("#ajustesModalLabel").text("Nuevo campo personalizado");
                $("#ajustesModalBody").html(html);
                $("#ajustesModal").modal("show");
                $("#btnGuardarAjustes").attr("data-type", "campo");
            });
    }

    if (e.target.closest("#btnEditarRol")) {
        fetch(baseurl + "views/components/ajustes/formRol.php?id=" + e.target.closest("button").dataset.id)
            .then(response => response.text())
            .then(html => {
                $("#ajustesModalLabel").text("Editar rol");
                $("#ajustesModalBody").html(html);
                $("#ajustesModal").modal("show");
            });
    }

    if (e.target.closest("#btnEditarCampo")) {
        fetch(baseurl + "views/components/ajustes/formCampos.php?id=" + e.target.closest("button").dataset.id)
            .then(response => response.text())
            .then(html => {
                $("#ajustesModalLabel").text("Editar campo personalizado");
                $("#ajustesModalBody").html(html);
                $("#ajustesModal").modal("show");
                $("#btnGuardarAjustes").attr("data-type", "campo");
            });
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

    if (e.target.closest("#btnEliminarCampo")) {
        const id = e.target.closest("button").dataset.id;

        if (confirm("¿Está seguro de que desea eliminar este campo personalizado? Esta acción no se puede deshacer.")) {
            const formData = new FormData();
            formData.append("idcampo", id);

            api.post({
                source: "ajustes",
                action: "eliminar_campo",
                data: formData,
                onSuccess: () => {
                    fetchCamposExtra();
                }
            });
        }
    }

    if (e.target.closest("#btnGuardarRol")) {
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

    if (e.target.closest("#btnGuardarAjustes")) {
        const type = e.target.closest("#btnGuardarAjustes").dataset.type;

        if (type === "campo") {
            const form = document.getElementById("formCampo");
            const formData = new FormData(form);
            const campo = formData.get('nombre').replace(' ', '_').toLowerCase();
            formData.append("campo", campo);

            const action = formData.get("idcampo") ? "actualizar_campo" : "crear_campo";
            api.post({
                source: "ajustes",
                action: action,
                data: formData,
                onSuccess: () => {
                    $("#ajustesModal").modal("hide");
                    fetchCamposExtra();
                }
            });
        }
    }

    if (e.target.closest('.cliente-item')) {
        const target = e.target.closest('.cliente-item');
        const value = target.dataset.value;
        const id = target.dataset.id;
        if (!value || !id) return;

        const grupo = target.closest('.busqueda-grupo');
        const resultados = grupo.querySelector('.resultados-busqueda');
        const input = grupo.querySelector(`input[id="${resultados.dataset.parent}"]`);
        const hidden = grupo.querySelector(`input[name="idreferencia"]`);
        input.value = value;
        hidden.value = id;

        resultados.innerHTML = '';
        resultados.style.display = 'none';
    }

    if (e.target.closest(".org-item")) {
        const target = e.target.closest(".org-item");

        const input = document.getElementById("referenciaInput");
        const hiddenId = document.getElementById("idReferenciaInput");
        input.value = target.dataset.value;
        hiddenId.value = target.dataset.id;

        const resultados = document.querySelector(`[data-parent="${input.id}"]`);
        resultados.innerHTML = "";
        resultados.style.display = "none";
    }
});

document.addEventListener('input', function (e) {
    if (e.target.id === 'referenciaInput') {
        const input = e.target;
        const value = input.value.trim();
        const type = input.dataset.type || 'cliente';
        const resultados = input.closest('.busqueda-grupo').querySelector('.resultados-busqueda');

        if (value.length > 2) {
            if (type === "clientes") {
                buscarClientes(value, resultados);
            }
            if (type === "empresas") {
                buscarEmpresas(value, resultados);
            }
            if (type === "actividades") {
            }
        } else {
            resultados.innerHTML = '';
            resultados.style.display = 'none';
        }
    }
});

document.addEventListener("change", function (e) {
    if (e.target.id === 'tablaInput') {
        const value = e.target.value;
        const inputReferencia = document.getElementById('referenciaInput');

        if (value === "clientes") {
            inputReferencia.dataset.type = "clientes";
        }
        if (value === "empresas") {
            inputReferencia.dataset.type = "empresas";
        }
        if (value === "actividades") {
            inputReferencia.dataset.type = "actividades";
        }
    }
})

function buscarClientes(filtro, resultados) {
    api.get({
        source: "clientes",
        action: "buscar",
        params: [
            { name: "filtro", value: filtro },
            { name: "tipo", value: 1 }
        ],
        onSuccess: function (clientes) {
            let html = '';

            if (clientes.length > 0) {
                clientes.forEach(cliente => {
                    html += `
                            <div class="resultado-item cliente-item" 
                                data-id="${cliente.idcliente}" 
                                data-value="${cliente.nombres} ${cliente.apellidos}">
                                <div class="d-flex flex-column gap-2 w-100">
                                    <div class="d-flex align-items-center gap-2">
                                        <img class="user-icon sm" src="${window.baseurl + cliente.foto}" alt="Foto de ${cliente.nombres} ${cliente.apellidos}">
                                        <div class="d-flex flex-column" style="font-size: 13px">
                                            <span>${cliente.nombres} ${cliente.apellidos}</span>
                                            <div class="info-row gap-4">
                                                <div class="info-row">
                                                    ${window.icons.telefono} <span>${cliente.telefono}</span>
                                                </div>
                                                <div class="info-row">
                                                    ${window.icons.building} <span>${cliente.empresa_nombre}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                });
            } else {
                html = '<div class="resultado-item cliente-item">No se encontraron resultados</div>';
            }

            resultados.innerHTML = html;
            resultados.style.display = 'flex';
        }
    });
}

function buscarEmpresas(filtro, resultados) {
    api.get({
        source: "clientes",
        action: "buscarOrganizaciones",
        params: [
            { name: "filtro", value: filtro }
        ],
        onSuccess: (organizaciones) => {
            let html = '';

            if (organizaciones.length > 0) {
                organizaciones.forEach(org => {
                    html += `<div class="resultado-item org-item" data-value="${org.razon_social}" data-id="${org.idempresa}">
                                    ${window.icons.building}${org.razon_social}
                                </div>`;
                });
            } else {
                html = '<div class="resultado-item cliente-item">No se encontraron resultados</div>';
            }

            resultados.innerHTML = html;
            resultados.style.display = "flex";
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".ajuste-item.clickable").forEach(container => {
        container.addEventListener("click", () => {
            document.querySelectorAll(".ajuste-item.clickable").forEach(c =>
                c.classList.remove("selected")
            );

            const section = document.getElementById(container.dataset.target);
            if (section) {
                document.querySelectorAll(".ajuste-section").forEach(section => section.style.display = "none");
                container.classList.add("selected");
                section.style.display = "block";
            }
        });
    });

    // Estado inicial
    fetchAjustes();
    document.querySelectorAll(".ajuste-section").forEach(section => section.style.display = "none");
    const itemSelected = document.querySelector('.ajuste-item.selected');
    document.getElementById(itemSelected.dataset.target).style.display = "block";
});