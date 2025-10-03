import api from "../utils/api.js";
import { ModalComponent } from "../utils/modal.js";

const clienteModal = new ModalComponent("cliente", { size: "lg" });

export function abrirModal({ tipo, id = null, esNuevo = false, focus = null }) {
    const urls = {
        1: "views/components/clientes/formCliente.php",
        2: "views/components/clientes/formOrganizacion.php"
    };

    const titulos = {
        1: esNuevo ? "Agregar nuevo cliente" : "Editar cliente",
        2: esNuevo ? "Agregar nueva organización" : "Editar organización"
    };

    let url = window.baseurl + urls[tipo];
    if (id) url += "?id=" + id;

    fetch(url)
        .then(res => res.text())
        .then(html => {
            clienteModal.show(titulos[tipo], html);
            if (focus) clienteModal.getComponent(focus).focus();
        })
        .catch(e => {
            mostrarToast({
                message: "Ocurrió un error al mostrar el formulario",
                type: "danger"
            });
            console.error(e);
        });
}

export function guardarRegistro(tipo, onSuccess) {
    const forms = {
        1: "formCliente",
        2: "formOrganizacion"
    };

    const acciones = {
        1: { create: "crear", update: "actualizar" },
        2: { create: "crearOrganizacion", update: "actualizarOrganizacion" }
    };

    const form = document.getElementById(forms[tipo]);
    const formData = new FormData(form);

    const id = formData.get("id");
    const action = id ? acciones[tipo].update : acciones[tipo].create;

    api.post({
        source: "clientes",
        action,
        data: formData,
        onSuccess: () => {
            $("#clienteModal").modal("hide");
            document.dispatchEvent(new Event("entidadActualizada"));
            onSuccess();
        }
    });
}

export function eliminarRegistro(tipo, id, onSuccess) {
    const acciones = {
        1: { action: "eliminar", mensaje: "¿Seguro que desea eliminar al cliente del sistema?" },
        2: { action: "eliminarOrganizacion", mensaje: "¿Seguro que desea eliminar a la organización del sistema?" }
    };

    if (!confirm(acciones[tipo].mensaje)) return;

    const formData = new FormData();
    formData.append("id", id);

    api.post({
        source: "clientes",
        action: acciones[tipo].action,
        data: formData,
        onSuccess
    });
}