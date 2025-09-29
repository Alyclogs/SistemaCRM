import api from "../utils/api.js";
import { abrirModal, eliminarRegistro, guardarRegistro } from "./utils.js";

let filtroBuscado = '';
let selectedEstado = '';
let selectedTipo = '1';
let clientesCache = [];

function fetchClientes(filtro = "", idestado = "", tipo = "1") {
    let params = [];
    params.push({ name: 'tipo', value: tipo });

    if (filtro !== '') {
        params.push({ name: 'filtro', value: filtro });
    }

    if (idestado !== '') {
        params.push({ name: 'idestado', value: idestado });
    }

    document.querySelectorAll('table').forEach(t => t.style.display = 'none');
    const table = tipo == '1' ? document.getElementById('tablaClientes')
        : document.getElementById('tablaOrganizaciones');
    const container = tipo == '1' ? document.getElementById('tablaClientesBody')
        : document.getElementById('tablaOrganizacionesBody');

    container.innerHTML = 'Cargando...'; // Barra de carga proximamente uwu
    let html = '';

    api.get({
        source: "clientes",
        action: filtro === '' ? "listar" : "buscar",
        params,
        onSuccess: function (items) {
            if (items.length === 0) {
                container.innerHTML = "No se encontraron resultados";
                table.style.display = 'table';
                return;
            }

            if (tipo == '1') {
                let clientes = items;
                clientesCache = clientes;

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
                                <div class="info-row clickable">
                                    <img class="user-icon sm clickable" data-type="cliente" data-id="${cliente.idcliente}" src="${cliente.foto}" alt="Foto de ${cliente.nombres} ${cliente.apellidos}"></img>
                                    <span class="user-link clickable" data-type="cliente" data-id="${cliente.idcliente}">${cliente.nombres} ${cliente.apellidos}</span>
                                </div>
                            </td>
                            <td>${cliente.empresa_nombre || '-'}</td>
                            <td>${cliente.num_doc || '-'}</td>
                            <td>${cliente.telefono || '-'}</td>
                            <td>${cliente.correo || '-'}</td>
                            <td>
                                ${cliente.estado ? `<div class="chip chip-${clienteEstado(cliente.estado)}">${cliente.estado}</div>` : '-'}
                            </td>
                            <td>
                                <div class="icons-row">
                                    <button class="btn btn-icon bg-light" id="btnEditCliente" data-id="${cliente.idcliente}">${window.icons.edit}</button>
                                    <button class="btn btn-icon bg-light" id="btnDeleteRegistro" data-id="${cliente.idcliente}">${window.icons.trash}</button>
                                </div>
                            </td>
                </tr>`;
                });
            }

            if (tipo == '2') {
                let organizaciones = items;

                organizaciones.forEach(org => {
                    html += `<tr>
                            <td>
                                <div class="info-row">
                                    <img class="user-icon sm clickable" data-type="empresa" data-id="${org.idempresa}" src="${org.foto}" alt="Foto de ${org.razon_social}"></img>
                                    <span class="user-link clickable" data-type="empresa" data-id="${org.idempresa}">${org.razon_social}</span>
                                </div>
                            </td>
                            <td>${org.ruc || '-'}</td>
                            <td>${org.direccion || '-'}</td>
                            <td>
                                <div class="icons-row">
                                    <button class="btn btn-icon bg-light" id="btnEditOrganizacion" data-id="${org.idempresa}">${window.icons.edit}</button>
                                    <button class="btn btn-icon bg-light" id="btnDeleteRegistro" data-id="${org.idempresa}">${window.icons.trash}</button>
                                </div>
                            </td>
                </tr>`;
                });
            }

            container.innerHTML = html;
            table.style.display = 'table';
        }
    });
}

function updateSelectedTipo(tipo) {
    selectedTipo = tipo;
    if (selectedTipo == '2') {
        document.querySelector('.busqueda-grupo[data-type="Estado"]').style.display = 'none';
    } else {
        document.querySelector('.busqueda-grupo[data-type="Estado"]').style.display = '';
    }
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
    if (e.target.closest('#btnRefresh')) {
        fetchClientes();
        selectedEstado = '';
        selectedTipo = '1';
        document.querySelectorAll('.selected-filtro').forEach(el => {
            const grupo = el.closest('.busqueda-grupo');
            if (grupo.dataset.type !== "Tipo") {
                el.textContent = el.closest('.busqueda-grupo').dataset.type;
            }
        });
        document.querySelectorAll('.boton-filtro').forEach(el => {
            const grupo = el.closest('.busqueda-grupo');
            if (grupo.dataset.type !== "Tipo") {
                el.classList.remove('selected');
            }
        });
        document.querySelectorAll('.filtro-item').forEach(el => el.classList.remove('selected'));
        document.querySelectorAll('.busqueda-grupo').forEach(el => el.style.display = '');
        const newSelectedTipo = document.querySelector(`.filtro-item[data-id="${selectedTipo}"]`);
        newSelectedTipo.classList.add('selected');
        document.querySelector('#tipoCliente').textContent = "Clientes";
        document.getElementById('tiposClientes').classList.add('selected');
    }

    if (e.target.closest('.filtro-item')) {
        const tab = e.target.closest('.filtro-item');
        const grupoBusqueda = tab.closest('.busqueda-grupo');
        const btnFiltro = grupoBusqueda.querySelector('.boton-filtro');
        const selectedFiltro = btnFiltro.querySelector('.selected-filtro');

        grupoBusqueda.querySelectorAll('.filtro-item').forEach(el => el.classList.remove('selected'));
        tab.classList.add('selected');

        if (grupoBusqueda.dataset.type === "Estado") {
            selectedEstado = tab.dataset.id || '';
            updateSelectedTipo('1');
        }
        if (grupoBusqueda.dataset.type === "Tipo") {
            updateSelectedTipo(tab.dataset.id || '1');
        }
        selectedFiltro.textContent = tab.dataset?.value || grupoBusqueda.dataset.tipo;
        btnFiltro.classList.add('selected');

        const resultados = grupoBusqueda.querySelector('.resultados-busqueda');
        resultados.style.display = "none";

        fetchClientes(filtroBuscado, selectedEstado, selectedTipo);
    }

    if (e.target.closest('#btnNuevoRegistro')) {
        abrirModal({ tipo: selectedTipo, esNuevo: true });
    }

    if (e.target.closest('#btnEditCliente')) {
        e.stopPropagation();
        const id = e.target.closest('#btnEditCliente').dataset.id;
        abrirModal({ tipo: 1, id });
    }

    if (e.target.closest('#btnEditOrganizacion')) {
        e.stopPropagation();
        const id = e.target.closest('#btnEditOrganizacion').dataset.id;
        abrirModal({ tipo: 2, id });
    }

    if (e.target.closest('#btnDeleteRegistro')) {
        e.stopPropagation();
        const id = e.target.closest('#btnDeleteRegistro').dataset.id;
        eliminarRegistro(selectedTipo, id, () => {
            fetchClientes('', '', selectedTipo);
        });
    }

    if (e.target.closest('#btnProyectosCliente')) {
        e.stopPropagation();
        const idcliente = e.target.closest('#btnProyectosCliente').dataset.id;
        fetch(window.baseurl + "views/components/selectModal.php?source=proyectos&type=multiple&id=" + idcliente)
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
        guardarRegistro(selectedTipo, () => {
            fetchClientes('', '', selectedTipo);
        });
    }

    if (e.target.closest("#btnNuevaOrganizacion")) {
        e.stopPropagation();
        const value = e.target.closest("#btnNuevaOrganizacion").dataset.value;
        const formData = new FormData();
        formData.append("razon_social", value);
        let idOrganizacion = null;

        api.post({
            source: "clientes",
            action: "createOrganizacion",
            data: formData,
            onSuccess: (response) => {
                idOrganizacion = response.id;
                const hiddenId = document.getElementById("idOrganizacionInput");
                hiddenId.value = idOrganizacion;
            }
        });
        const resultados = document.querySelector(`[data-parent="organizacionInput"]`);
        resultados.innerHTML = "";
        resultados.style.display = "none";
    }

    if (e.target.closest(".org-item")) {
        const target = e.target.closest(".org-item");

        const input = document.getElementById("organizacionInput");
        const hiddenId = document.getElementById("idOrganizacionInput");
        input.value = target.dataset.value;
        hiddenId.value = target.dataset.id;

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

        if (value.length > 2) {
            api.get({
                source: "clientes",
                action: "buscarOrganizaciones",
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
                        html += `<div class="resultado-item org-item" data-value="${org.razon_social}" data-id="${org.idempresa}">
                            ${window.icons.building}${org.razon_social}
                        </div>`;
                    });

                    if (!organizaciones.some(org =>
                        org.razon_social.toLowerCase().trim() === value.toLowerCase().trim()
                        || org.ruc.toLowerCase().trim() === value.toLowerCase().trim()
                    )) {
                        html += buttonNewOrganizacion;
                    }

                    resultados.innerHTML = html;
                    resultados.style.display = "flex";
                }
            });
        } else {
            resultados.innerHTML = '';
            resultados.style.display = "none";
        }
    }
    if (e.target.closest('inputBuscarClientes')) {
        const input = e.target.closest('inputBuscarClientes');
        filtroBuscado = input.value.trim().toLowerCase();
        fetchClientes(filtroBuscado, selectedEstado);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    fetchClientes();
});