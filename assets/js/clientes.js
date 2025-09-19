import api from "./api.js";
import { mostrarToast } from "./utils.js";

const baseurl = 'http://localhost/SistemaCRM/';
let filtroBuscado = '';
let selectedEstado = '';
let clientesCache = [];

function fetchClientes(filtro = "", idestado = "") {
    let params = [];

    if (filtro !== '') {
        params.push({ name: 'filtro', value: filtro });
    }

    if (idestado !== '') {
        params.push({ name: 'idestado', value: idestado });
    }

    const container = document.getElementById('tablaClientesBody');
    container.innerHTML = 'Cargando...'; // Barra de carga proximamente uwu
    let html = '';

    api.get({
        source: "clientes",
        action: filtro === '' ? "read" : "search",
        params,
        onSuccess: function (clientes) {
            if (clientes.length === 0) {
                container.innerHTML = "No se encontraron resultados";
                return;
            }
            clientesCache = clientes;

            html += '<div class="row">';

            clientes.forEach(async (cliente) => {
                const clienteEstado = (estado) => estado === 'CLIENTE' ? 'success' : estado === 'INACTIVO' ? 'danger' : 'warning';
                const proyectoEstado = (estado) => {
                    switch (estado) {
                        case 'PLANIFICADO':
                            return 'info';
                        case 'EN PROGRESO':
                            return 'warning';
                        case 'EN PAUSA':
                            return 'danger';
                        case 'CANCELADO':
                            return 'danger';
                        case 'TERMINADO':
                            return 'success';
                    }
                }

                html += `<tr>
                    <td>
                        <div class="info-row">
                            <img class="user-icon sm clickable" data-type="cliente" data-id="${cliente.idcliente}" src="${cliente.foto}" alt="Foto de ${cliente.nombres} ${cliente.apellidos}"></img>
                            <span class="fw-bold user-link clickable" data-type="cliente" data-id="${cliente.idcliente}">${cliente.nombres} ${cliente.apellidos}</span>
                        </div>
                    </td>
                    <td>${cliente.empresa || ''}</td>
                    <td>${cliente.num_doc}</td>
                    <td>${cliente.telefono}</td>
                    <td>${cliente.correo}</td>
                    <td>
                        <div class="chip chip-${clienteEstado(cliente.estado)}">${cliente.estado}</div>
                    </td>
                    <td>
                        <div class="icons-row">
                            <button class="btn-icon bg-light" id="btnEditCliente" data-id="${cliente.idcliente}">${window.icons.edit}</button>
                            <button class="btn-icon bg-light" id="btnDeleteCliente" data-id="${cliente.idcliente}">${window.icons.trash}</button>
                        </div>
                    </td>
                </tr>`;
            });

            html += '</div>';

            container.innerHTML = html;
        }
    });
}

function guardarCliente() {
    const formCliente = document.getElementById("formCliente");
    const formData = new FormData(formCliente);

    const idcliente = formData.get("idcliente");
    const action = idcliente ? "update" : "create";

    api.post({
        source: "clientes",
        action,
        data: formData,
        onSuccess: function () {
            fetchClientes();
            $("#clienteModal").modal("hide");
        }
    });
}

function eliminarCliente(idcliente) {
    const formData = new FormData();
    formData.append('idcliente', idcliente);

    api.post({
        source: "cliente",
        action: "delete",
        data: formData
    });
}

function asignarProyectos() {
    const seleccionados = [...document.querySelectorAll("#selectorItems .selector-item.selected")]
        .map(el => el.dataset.id);

    const formData = new FormData();
    formData.append("idcliente", document.getElementById('selectedId').value);
    formData.append("projects", JSON.stringify(seleccionados));

    api.post({
        source: "clientes",
        action: "setProjects",
        data: formData,
        onSuccess: function () {
            fetchClientes();
            $("#selectorModal").modal("hide");
        }
    });
}

document.addEventListener('click', function (e) {
    if (e.target.closest('.tab-item')) {
        const tab = e.target.closest('.tab-item');
        selectedEstado = tab.dataset.estado || '';
        const tabsContainer = tab.closest('.tabs-container');
        tabsContainer.querySelectorAll('.tab-item').forEach(el => el.classList.remove('selected'));

        tab.classList.add('selected');
        fetchClientes(filtroBuscado, selectedEstado);
    }

    if (e.target.closest('#btnNuevoCliente')) {
        fetch(baseurl + "views/components/clientes/formCliente.php")
            .then(res => res.text())
            .then(html => {
                $("#clienteModalLabel").text("Agregar nuevo cliente");
                $("#clienteModalBody").html(html);
                $("#clienteModal").modal("show");
            })
            .catch(e => console.error(e));
    }

    if (e.target.closest('#btnEditCliente')) {
        e.stopPropagation();
        const idcliente = e.target.closest('#btnEditCliente').dataset.id;
        fetch(baseurl + "views/components/clientes/formCliente.php?id=" + idcliente)
            .then(res => res.text())
            .then(html => {
                $("#clienteModalLabel").text("Editar cliente");
                $("#clienteModalBody").html(html);
                $("#clienteModal").modal("show");
            })
            .catch(e => console.error(e));
    }

    if (e.target.closest('#btnDeleteCliente')) {
        e.stopPropagation();
        const idcliente = e.target.closest('#btnDeleteCliente').dataset.id;
        if (confirm("¿Seguro que desea eliminar al cliente del sistema?")) {
            eliminarCliente(idcliente);
        }
    }

    if (e.target.closest('#btnProyectosCliente')) {
        e.stopPropagation();
        const idcliente = e.target.closest('#btnProyectosCliente').dataset.id;
        fetch(baseurl + "views/components/selectModal.php?source=proyectos&type=multiple&id=" + idcliente)
            .then(res => res.text())
            .then(html => {
                $("#selectorModal").remove();
                $("body").append(html);
                $("#selectorModalLabel").text("Seleccione proyectos")
                $("#selectorModal").modal("show");

                $('#btnSeleccionar').on('click', function () {
                    asignarProyectos();
                });
            })
            .catch(e => console.error(e));
    }

    if (e.target.closest('#btnGuardarCliente')) {
        guardarCliente();
    }

    if (e.target.closest("#btnNuevaOrganizacion")) {
        e.stopPropagation();
        const value = e.target.closest("#btnNuevaOrganizacion").dataset.value;
        const formData = new FormData();
        formData.append("razon_social", value);

        api.post({
            source: "clientes",
            action: "createOrganizacion",
            data: formData
        });
        const resultados = document.querySelector(`[data-parent="organizacionInput"]`);
        resultados.innerHTML = "";
        resultados.style.display = "none";
    }

    if (e.target.closest(".resultado-item")) {
        const target = e.target.closest(".resultado-item");
        if (target.id === "btnNuevaOrganizacion") return;

        const input = document.getElementById("organizacionInput");
        input.value = target.dataset.value;

        const resultados = document.querySelector(`[data-parent="organizacionInput"]`);
        resultados.innerHTML = "";
        resultados.style.display = "none";
    }
});

document.addEventListener('input', function (e) {
    if (e.target.closest('#organizacionInput')) {
        const target = e.target.closest('#organizacionInput');
        const value = target.value;
        const resultados = document.querySelector(`[data-parent="${target.id}"]`);
        let html = '';

        const buttonNewOrganizacion = `
        <div class="resultado-item bg-secondary text-white" id="btnNuevaOrganizacion" data-value="${value}">
            ${window.getIcon("add", "white")}<span>Agregar ${value} como nueva organización</span>
        </div>
        `;

        api.get({
            source: "clientes",
            action: "searchOrganizaciones",
            params: [
                { name: "filtro", value }
            ],
            onSuccess: (organizaciones) => {
                if (organizaciones.length === 0) {
                    html = buttonNewOrganizacion;
                    resultados.innerHTML = html;
                    resultados.style.display = "flex";
                    return;
                }

                organizaciones.forEach(org => {
                    html += `<div class="resultado-item" data-value="${org.razon_social}">
                            ${window.icons.building}${org.razon_social}
                        </div>`;
                });

                if (!organizaciones.some(org =>
                    org.razon_social.toLowerCase().includes(value.toLowerCase().trim())
                )) {
                    html += buttonNewOrganizacion;
                }

                resultados.innerHTML = html;
                resultados.style.display = "flex";
            }
        });
    }
});

document.getElementById('inputBuscarClientes').addEventListener('input', function () {
    filtroBuscado = this.value.trim().toLowerCase();
    fetchClientes(filtroBuscado, selectedEstado);
});

document.addEventListener('DOMContentLoaded', function () {
    fetchClientes();
});