import { icons, mostrarToast } from "./utils.js";

const baseurl = 'http://localhost/SistemaCRM/';
let filtroBuscado = '';
let selectedEstado = '';
let clientesCache = [];

function fetchClientes(filtro = "", idestado = "") {
    let url = baseurl + (filtro === ''
        ? 'controller/Clientes/ClienteController.php?action=read'
        : 'controller/Clientes/ClienteController.php?action=search&filtro=' + encodeURIComponent(filtro));

    if (idestado !== '') {
        url += ('&idestado=' + encodeURIComponent(idestado));
    }

    const container = document.getElementById('clientsContainer');
    container.innerHTML = 'Cargando...'; // Barra de carga proximamente uwu

    fetch(url)
        .then(res => res.json())
        .then(async (clientes) => {
            let html = '';
            const myIcons = await icons();

            if (clientes.length === 0) {
                container.innerHTML = `No se encontraron clientes`;
                return;
            }
            clientesCache = clientes;

            html += '<div class="row">';

            clientes.forEach(async (cliente) => {
                const clienteEstado = (estado) => estado === 'CLIENTE' ? 'chip-success' : estado === 'INACTIVO' ? 'chip-danger' : 'chip-warning';
                const proyectoEstado = (estado) => {
                    switch (estado) {
                        case 'PLANIFICADO':
                            return 'chip-info';
                        case 'EN PROGRESO':
                            return 'chip-warning';
                        case 'EN PAUSA':
                            return 'chip-danger';
                        case 'CANCELADO':
                            return 'chip-danger';
                        case 'TERMINADO':
                            return 'chip-success';
                    }
                }

                html += `<div class="col col-xl-4 col-lg-6 col-md-6 col-sm-12">
                    <div class="container-border">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="info-row">
                                <img src="${baseurl + cliente.foto}" class="user-icon sm" data-type="cliente" data-id="${cliente.idcliente}"></img>
                                <h5 class="text-large flex-grow-1">${cliente.nombre}</h5>
                                <div class="chip ${clienteEstado(cliente.estado)}">${cliente.estado}</div>
                            </div>
                            <div class="icons-row">
                                <button class="btn-icon bg-light" id="btnEditCliente" data-id="${cliente.idcliente}" title="Editar cliente">${myIcons.edit}</button>
                                <button class="btn-icon bg-light" id="btnProyectosCliente" data-id="${cliente.idcliente}" title="Asignar proyectos">${myIcons.dni}</button>
                            </div>
                        </div>
                        <div class="row mb-4" style="max-width: 65%;">
                            <div class="col info-row">${cliente.tipo_doc}: ${cliente.num_doc}</div>
                            <div class="col info-row">${myIcons.telefono}${cliente.telefono}</div>
                            <div class="col info-row">${myIcons.correo}${cliente.correo}</div>
                        </div>
                        <div>
                            <div class="d-flex flex-column gap-1">
                                <p class="text-medium">Proyectos</p>
                                <div class="d-flex gap-2">
                                    ${cliente.proyectos.length
                        ? cliente.proyectos.map(proyecto => `<div class="chip chip-outline clickable ${proyectoEstado(proyecto.estado)}" data-tipo="proyecto" data-id="${proyecto.idproyecto}">${proyecto.nombre}</div>`).join('')
                        : 'N/A'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            });

            html += '</div>';

            container.innerHTML = html;
        })
        .catch(e => {
            console.error(e);
        });
}

function guardarCliente() {
    const formCliente = document.getElementById("formCliente");
    const formData = new FormData(formCliente);

    const idcliente = formData.get("idcliente");
    const action = idcliente ? "update" : "create";

    fetch(baseurl + "controller/clientes/ClienteController.php?action=" + action, {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarToast({
                    message: data.message,
                    type: "success"
                });
                fetchClientes();
                $("#clienteModal").modal("hide");
            } else {
                mostrarToast({
                    message: data.message,
                    type: "danger"
                });
            }
        })
        .catch(err => {
            console.error("Error en la solicitud:", err);
        });
}

function asignarProyectos() {
    const seleccionados = [...document.querySelectorAll("#selectorItems .selector-item.selected")]
        .map(el => el.dataset.id);

    const formData = new FormData();
    formData.append("idcliente", document.getElementById('selectedId').value);
    formData.append("projects", JSON.stringify(seleccionados));

    fetch(baseurl + "controller/clientes/ClienteController.php?action=setProjects", {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarToast({
                    title: "Hola"
                });
                fetchClientes();
                $("#selectorModal").modal("hide");
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => {
            console.error("Error en la solicitud:", err);
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
        console.log('Click en guardar cliente');
        guardarCliente();
    }
});

document.getElementById('inputBuscarClientes').addEventListener('input', function () {
    filtroBuscado = this.value.trim().toLowerCase();
    fetchClientes(filtroBuscado, selectedEstado);
});

document.addEventListener('DOMContentLoaded', function () {
    fetchClientes();
});