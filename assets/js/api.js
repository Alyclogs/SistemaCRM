import { mostrarToast } from "./utils.js";

const baseurl = 'http://localhost/SistemaCRM/';

function getController(source) {
    switch (source) {
        case "usuarios":
            return 'controller/usuarios/UsuarioController.php?';
        case "clientes":
            return 'controller/clientes/ClienteController.php?';
        case "proyectos":
            return 'controller/proyectos/ProyectoController.php?';
        case "tareas":
            return 'controller/tareas/TareaController.php?';
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
        console.error("Error en fetch:", err.message);
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
        action = "read",
        params = [],
        onSuccess,
        onError
    } = {}) {
        let url = baseurl + getController(source);
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
        let url = baseurl + source;
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
        let url = baseurl + getController(source);
        const urlParams = new URLSearchParams({ action });
        const isFormData = data instanceof FormData;
        url += urlParams.toString();

        try {
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