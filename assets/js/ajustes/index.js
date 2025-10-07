import api from "../utils/api.js";
import { ModalComponent } from "../utils/modal.js";
import { fetchCampanias, fetchEmisores, fetchPlantillas, guardarEmisor, guardarPlantilla } from "./campanias.js";
import { fetchCamposExtra, guardarCampo } from "./campos.js";
import { cargarCampaniaExistente, guardarCampania, initCampaniaConfig, initPlantillaSelection } from "./programaciones.js";
import { fetchRoles, guardarRol } from "./roles.js";

export const modalAjustes = new ModalComponent("ajustes", { size: "md" });

function fetchAjustes() {
    fetchRoles();
    fetchCamposExtra();
    fetchCampanias();
    fetchPlantillas("correosPlantillasList");
    fetchEmisores("correoEmisoresList", "correo");
}

export function abrirModal(type, title, id = null, options = {}) {
    const urls = {
        rol: window.baseurl + "views/components/ajustes/formRol.php",
        campo: window.baseurl + "views/components/ajustes/formCampos.php",
        campania: window.baseurl + "views/components/ajustes/modalCampania.php",
        plantilla: window.baseurl + "views/components/ajustes/formPlantilla.php",
        emisorCorreo: window.baseurl + "views/components/ajustes/formEmisor.php"
    }
    let url = urls[type];
    if (id) url += "?id=" + id;

    const { size = "md", onRender } = options;

    const actions = {
        campania: () => {
            fetchPlantillas("campaniaPlantillasList", {
                selectable: true,
                editable: false,
                onRender: () => {
                    if (id) cargarCampaniaExistente(id);
                    initCampaniaConfig();
                    initPlantillaSelection();
                }
            });
        }
    }

    fetch(url)
        .then(response => response.text())
        .then(html => {
            modalAjustes.components.buttons.guardar.attr("data-type", type);
            modalAjustes.setSize(size);
            modalAjustes.show(title, html);
            modalAjustes.setOption(options);
            if (typeof onRender === "function") onRender();
            if (actions[type]) actions[type]();
        });
}

document.addEventListener("click", function (e) {
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

    if (e.target.closest("#btnGuardarAjustes")) {
        const type = e.target.closest("#btnGuardarAjustes").dataset.type;
        const actions = {
            rol: () => guardarRol(),
            campo: () => guardarCampo(),
            campania: () => guardarCampania(),
            plantilla: () => guardarPlantilla(),
            emisorCorreo: () => guardarEmisor()
        }
        actions[type]();
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

export function buscarClientes(filtro, resultados) {
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

export function buscarEmpresas(filtro, resultados) {
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
    fetchAjustes();
});