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

                html += `<tr>
                    <td>
                        <div class="info-row">
                            <img class="user-icon sm clickable" data-type="cliente" data-id="${cliente.idcliente}" src="${cliente.foto}" alt="Foto de ${cliente.nombres} ${cliente.apellidos}"></img>
                            <span class="fw-bold user-link clickable" data-type="cliente" data-id="${cliente.idcliente}">${cliente.nombre}</span>
                        </div>
                    </td>
                    <td>${cliente.empresa || ''}</td>
                    <td>${cliente.num_doc}</td>
                    <td>${cliente.telefono}</td>
                    <td>${cliente.correo}</td>
                    <td>
                        <div class="chip chip-outline chip-info">${cliente.nombre_rol}</div>
                    </td>
                    <td>
                        <div class="chip chip-${estadoUsuario(cliente.estado)}">${cliente.estado}</div>
                    </td>
                    <td>
                        <div class="icons-row">
                            <button class="btn-icon bg-light" id="btnEditUsuario" data-id="${cliente.idcliente}">${window.icons.edit}</button>
                            <button class="btn-icon bg-light" id="btnDeleteUsuario" data-id="${cliente.idcliente}">${window.icons.trash}</button>
                        </div>
                    </td>
                </tr>`;
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