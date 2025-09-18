import { mostrarToast } from "./utils.js";

const baseurl = 'http://localhost/SistemaCRM/';
let filtroBuscado = '';
let selectedEstado = '';
let usuariosCache = [];

function fetchUsuarios(filtro = "", idestado = "") {
    let url = baseurl + (filtro === ''
        ? 'controller/Usuarios/UsuarioController.php?action=read'
        : 'controller/Usuarios/UsuarioController.php?action=search&filtro=' + encodeURIComponent(filtro));

    if (idestado !== '') {
        url += ('&idestado=' + encodeURIComponent(idestado));
    }

    const container = document.getElementById('tablaUsuariosBody');
    container.innerHTML = 'Cargando...'; // Barra de carga proximamente uwu

    fetch(url)
        .then(res => res.json())
        .then(async (usuarios) => {
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
        })
        .catch(e => {
            console.error(e);
        });
}

function guardarUsuario() {
    const formUsuario = document.getElementById("formUsuario");
    const formData = new FormData(formUsuario);

    const idusuario = formData.get("idusuario");
    const action = idusuario ? "update" : "create";

    fetch(baseurl + "controller/usuarios/UsuarioController.php?action=" + action, {
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
                fetchUsuarios();
                $("#usuarioModal").modal("hide");
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

function eliminarUsuario(idusuario) {
    const formData = new FormData();
    formData.append('idusuario', idusuario);

    fetch(baseurl + "controller/usuarios/UsuarioController.php?action=delete", {
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
                fetchUsuarios();
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
    formData.append("idusuario", document.getElementById('selectedId').value);
    formData.append("projects", JSON.stringify(seleccionados));

    fetch(baseurl + "controller/usuarios/UsuarioController.php?action=setProjects", {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                mostrarToast({
                    title: "Hola"
                });
                fetchUsuarios();
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
            .catch(e => console.error(e));
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
            .catch(e => console.error(e));
    }

    if (e.target.closest('#btnDeleteUsuario')) {
        e.stopPropagation();
        const idusuario = e.target.closest('#btnDeleteUsuario').dataset.id;
        if (confirm("¿Seguro que desea eliminar al usuario del sistema?")) {
            eliminarUsuario(idusuario);
        }
    }

    if (e.target.closest('#btnProyectosUsuario')) {
        e.stopPropagation();
        const idusuario = e.target.closest('#btnProyectosUsuario').dataset.id;
        fetch(baseurl + "views/components/selectModal.php?source=proyectos&type=multiple&id=" + idusuario)
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

    if (e.target.closest('#btnGuardarUsuario')) {
        console.log('Click en guardar usuario');
        guardarUsuario();
    }
});

document.getElementById('inputBuscarUsuarios').addEventListener('input', function () {
    filtroBuscado = this.value.trim().toLowerCase();
    fetchUsuarios(filtroBuscado, selectedEstado);
});

document.addEventListener('DOMContentLoaded', function () {
    fetchUsuarios();
});