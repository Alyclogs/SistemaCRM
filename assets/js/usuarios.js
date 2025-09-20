import api from "./api.js";
import { mostrarToast } from "./utils.js";

const baseurl = 'http://localhost/SistemaCRM/';
let filtroBuscado = '';
let selectedEstado = '';
let usuariosCache = [];

function fetchUsuarios(filtro = "", idestado = "") {
    let params = [];

    if (filtro !== '') {
        params.push({ name: 'filtro', value: filtro });
    }

    if (idestado !== '') {
        params.push({ name: 'idestado', value: idestado });
    }

    const container = document.getElementById('tablaUsuariosBody');
    container.innerHTML = 'Cargando...'; // Barra de carga proximamente uwu

    api.get({
        source: "usuarios",
        action: filtro === '' ? "read" : "search",
        params,
        onSuccess: function (usuarios) {
            let html = '';
            if (usuarios.length === 0) {
                container.innerHTML = `No se encontraron usuarios`;
                return;
            }
            usuariosCache = usuarios;

            const estadoUsuario = (estado) => estado === 'ACTIVO' ? 'success' : 'danger';

            usuarios.forEach(async (usuario) => {
                html += `<tr>
                    <td>
                        <div class="info-row">
                            <img class="user-icon sm clickable" data-type="usuario" data-id="${usuario.idusuario}" src="${usuario.foto}" alt="Foto de ${usuario.nombres} ${usuario.apellidos}"></img>
                            <span class="fw-bold user-link clickable" data-type="usuario" data-id="${usuario.idusuario}">${usuario.nombres} ${usuario.apellidos}</span>
                            ${usuario.idusuario == document.getElementById('idUsuario').value ? ' <div class="chip chip-success">ACTUAL</div>' : ''}
                        </div>
                    </td>
                    <td>${usuario.num_doc}</td>
                    <td>${usuario.telefono}</td>
                    <td>${usuario.correo}</td>
                    <td>
                        <div class="chip chip-outline chip-info">${usuario.nombre_rol}</div>
                    </td>
                    <td>
                        <div class="chip chip-${estadoUsuario(usuario.estado)}">${usuario.estado}</div>
                    </td>
                    <td>
                        <div class="icons-row">
                            <button class="btn-icon bg-light" id="btnEditUsuario" data-id="${usuario.idusuario}">${window.icons.edit}</button>
                            <button class="btn-icon bg-light" id="btnDeleteUsuario" data-id="${usuario.idusuario}">${window.icons.trash}</button>
                        </div>
                    </td>
                </tr>`;
            });

            container.innerHTML = html;
        }
    });
}

function guardarUsuario() {
    const formUsuario = document.getElementById("formUsuario");
    const formData = new FormData(formUsuario);

    const idusuario = formData.get("idusuario");
    const action = idusuario ? "update" : "create";

    api.post({
        source: "usuarios",
        action,
        data: formData,
        onSuccess: function () {
            fetchUsuarios();
            $("#usuarioModal").modal("hide");
        }
    });
}

function eliminarUsuario(idusuario) {
    const formData = new FormData();
    formData.append('idusuario', idusuario);

    api.post({
        source: "usuarios",
        action: "delete",
        data: formData
    });
}

document.addEventListener('click', function (e) {
    if (e.target.closest('.tab-item')) {
        const tab = e.target.closest('.tab-item');
        selectedEstado = tab.dataset.estado || '';
        const tabsContainer = tab.closest('.tabs-container');
        tabsContainer.querySelectorAll('.tab-item').forEach(el => el.classList.remove('selected'));

        tab.classList.add('selected');
        fetchUsuarios(filtroBuscado, selectedEstado);
    }

    if (e.target.closest('#btnNuevoUsuario')) {
        fetch(baseurl + "views/components/usuarios/formUsuario.php")
            .then(res => res.text())
            .then(html => {
                $("#usuarioModalLabel").text("Agregar nuevo usuario");
                $("#usuarioModalBody").html(html);
                $("#usuarioModal").modal("show");
            })
            .catch(e => {
                mostrarToast({
                    message: "Ocurrió un error al mostrar el formulario",
                    type: "danger"
                });
                console.error(e);
            });
    }

    if (e.target.closest('#btnEditUsuario')) {
        e.stopPropagation();
        const idusuario = e.target.closest('#btnEditUsuario').dataset.id;
        fetch(baseurl + "views/components/usuarios/formUsuario.php?id=" + idusuario)
            .then(res => res.text())
            .then(html => {
                $("#usuarioModalLabel").text("Editar usuario");
                $("#usuarioModalBody").html(html);
                $("#usuarioModal").modal("show");
            })
            .catch(e => {
                mostrarToast({
                    message: "Ocurrió un error al mostrar el formulario",
                    type: "danger"
                });
                console.error(e);
            });
    }

    if (e.target.closest('#btnDeleteUsuario')) {
        e.stopPropagation();
        const idusuario = e.target.closest('#btnDeleteUsuario').dataset.id;
        if (confirm("¿Seguro que desea eliminar al usuario del sistema?")) {
            eliminarUsuario(idusuario);
        }
    }

    if (e.target.closest('#btnGuardarUsuario')) {
        console.log('Click en guardar usuario');
        guardarUsuario();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    fetchUsuarios();
});