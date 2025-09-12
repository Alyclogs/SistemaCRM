import { icons } from "./utils.js";

const baseurl = 'http://localhost/SistemaCRM/';

function fetchClientes(filtro = "") {
    const url = baseurl + (filtro === ''
        ? 'controller/Clientes/ClienteController.php?action=read'
        : 'controller/Clientes/ClienteController.php?action=search&filtro=' + encodeURIComponent(filtro));

    const container = document.getElementById('clientsContainer');
    container.innerHTML = 'Cargando...'; // Barra de carga proximamente uwu

    fetch(url)
        .then(res => res.json())
        .then(async (clientes) => {
            let html = '';
            const myIcons = await icons();

            if (clientes.length === 0) {
                container.innerHTML = `No hay clientes registrados`;
                return;
            }

            clientes.forEach(async (cliente) => {
                const clienteEstado = (estado) => estado === 'CLIENTE' ? 'border border-success text-success' : estado === 'INACTIVO' ? 'border border-danger text-danger' : 'border border-warning text-warning';
                const proyectoEstado = (estado) => {
                    switch (estado) {
                        case 'PLANIFICADO':
                            return 'border border-info tex-info';
                        case 'EN PROGRESO':
                            return 'border border-warning text-warning';
                        case 'EN PAUSA':
                            return 'border border-danger text-danger';
                        case 'CANCELADO':
                            return 'border border-danger text-danger';
                        case 'TERMINADO':
                            return 'border border-success text-success';
                    }
                }

                html += `<div class="col col-xl-4 col-lg-6 col-md-6 col-sm-12">
                    <div class="container-border">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="info-row">
                                <img src="${cliente.foto}" class="user-icon sm"></img>
                                <h5 class="text-large flex-grow-1">${cliente.nombre}</h5>
                            </div>
                            <div class="icons-row">
                                <button class="btn-icon bcg-light">${myIcons.edit}</button>
                                <button class="btn-icon bcg-light">${myIcons.menu}</button>
                            </div>
                        </div>
                        <div class="row mb-4" style="max-width: 50%;">
                            <div class="col info-row">${myIcons.dni}${cliente.dni || cliente.ruc}</div>
                            <div class="col info-row">${myIcons.telefono}${cliente.telefono}</div>
                            <div class="col info-row">${myIcons.correo}${cliente.correo}</div>
                        </div>
                        <div>
                            <div class="d-flex gap-3">
                                <div class="d-flex flex-column gap-1">
                                    <p class="text-medium">Proyecto</p>
                                    ${cliente.proyectos.length ? `<div class="chip chip-outline ${proyectoEstado(cliente.proyectos[0].estado)}">${cliente.proyectos[0].nombre}</div>` : 'N/A'}
                                </div>
                                <div class="d-flex flex-column gap-1">
                                    <p class="text-medium">Estado</p>
                                    ${cliente.estado ? `<div class="chip chip-outline ${clienteEstado(cliente.estado)}">${cliente.estado}</div>` : 'N/A'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            });

            container.innerHTML = html;
        })
        .catch(e => {
            console.error(e);
        });
}

document.addEventListener('DOMContentLoaded', function () {
    fetchClientes();
});