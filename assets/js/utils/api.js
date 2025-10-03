import { normalizarHora } from "./date.js";
import { mostrarToast } from "./utils.js";

function getController(source) {
    switch (source) {
        case "usuarios":
            return 'controller/usuarios/UsuarioController.php?';
        case "clientes":
            return 'controller/clientes/ClienteController.php?';
        case "actividades":
            return 'controller/actividades/ActividadController.php?';
        case "proyectos":
            return 'controller/proyectos/ProyectoController.php?';
        case "tareas":
            return 'controller/tareas/TareaController.php?';
        case "ajustes":
            return 'controller/ajustes/AjustesController.php?';
        case "campanias":
            return 'controller/ajustes/CampaniaController.php?';
        case "emisores":
            return 'controller/ajustes/EmisorController.php?';
        case "plantillas":
            return "controller/ajustes/PlantillaController.php?"
        default:
            throw new Error(`Controller no definido para: ${source}`);
    }
}

async function apiFetch(url, options = {}, responseType = "json") {
    try {
        const res = await fetch(url, options);
        const text = await res.text();

        let data;
        try {
            data = responseType === "json" ? JSON.parse(text) : text;
        } catch {
            data = { success: false, message: text };
        }

        if (!res.ok || data.success === false) {
            throw new Error(data.message || `Error HTTP: ${res.status}`);
        }

        return data;
    } catch (err) {
        console.error("Error en fetch:", err.message, options?.body);
        mostrarToast({
            message: "Ocurrió un error en la solicitud. Inténtalo de nuevo",
            type: "danger"
        });
        throw err;
    }
}

export default {
    get: async function ({
        source = "usuarios",
        action = "listar",
        params = [],
        onSuccess,
        onError
    } = {}) {
        let url = window.baseurl + getController(source);
        const urlParams = new URLSearchParams({ action });

        params.forEach(param => {
            urlParams.append(param.name, param.value);
        });

        url += urlParams.toString();

        try {
            const data = await apiFetch(url);
            if (typeof onSuccess === "function") onSuccess(data);
        } catch (err) {
            if (typeof onError === "function") onError(err);
        }
    },

    getHtml: async function ({
        source = "",
        params = [],
        onSuccess,
        onError
    } = {}) {
        let url = window.baseurl + source;
        const urlParams = new URLSearchParams();

        params.forEach(param => {
            urlParams.append(param.name, param.value);
        });
        url += urlParams.toString();

        try {
            const data = await apiFetch(url, {}, 'text');
            if (typeof onSuccess === "function") onSuccess(data);
        } catch (err) {
            if (typeof onError === "function") onError(err);
        }
    },

    post: async function ({
        source = "usuarios",
        action = "create",
        data = {},
        onSuccess,
        onError
    } = {}) {
        let url = window.baseurl + getController(source);
        const urlParams = new URLSearchParams({ action });
        const isFormData = data instanceof FormData;
        url += urlParams.toString();

        try {
            const camposHora = ["hora_inicio", "hora_fin", "hora"];
            if (isFormData) {
                camposHora.forEach(campo => {
                    if (data.has(campo)) {
                        data.set(campo, normalizarHora(data.get(campo)));
                    }
                });
            } else {
                camposHora.forEach(campo => {
                    if (data[campo]) {
                        data[campo] = normalizarHora(data[campo]);
                    }
                });
            }

            const result = await apiFetch(url, {
                method: "POST",
                headers: isFormData ? {} : { "Content-Type": "application/json" },
                body: isFormData ? data : JSON.stringify(data)
            });

            mostrarToast({
                message: result.message,
                type: "success"
            });

            if (typeof onSuccess === "function") onSuccess(result);
        } catch (err) {
            if (typeof onError === "function") onError(err);
        }
    }
};