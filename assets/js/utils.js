const baseurl = 'http://localhost/SistemaCRM/';

export const icons = async () => {
    const [add, edit, trash, menu, user, telefono, correo, document, error,
        success, warning, info, building, video, reunion] = await Promise.all([
            fetch(baseurl + "assets/svg/add.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/edit.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/trash.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/menu.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/user.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/call.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/sms.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/document-text-2.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/close-circle.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/success.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/warning.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/info-circle.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/building.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/video.svg").then(res => res.text()),
            fetch(baseurl + "assets/svg/profile-2user.svg").then(res => res.text())
        ]);

    return {
        add,
        edit,
        trash,
        menu,
        user,
        telefono,
        correo,
        document,
        error,
        success,
        warning,
        info,
        building,
        video,
        reunion
    };
};

export function mostrarToast({ title, message, location = "bottom-right", type = "info", extra = {} }) {
    const {
        dismissable = true,
        onRender = null,
        buttons = [],
        icon = true,
        duration = 4000
    } = extra;

    // Crear contenedor si no existe
    let container = document.querySelector(`.toast-container.${location}`);
    if (!container) {
        container = document.createElement("div");
        container.className = `toast-container animate__animated animate__fadeInUp ${location}`;
        document.body.appendChild(container);
    }

    // Crear toast
    const toast = document.createElement("div");
    toast.className = `toast ${type}`;

    // Iconos por defecto
    const myIcons = window.icons;
    const defaultIcons = {
        success: myIcons.success,
        danger: myIcons.error,
        warning: myIcons.warning,
        info: myIcons.info
    };

    if (icon) {
        const iconDiv = document.createElement("div");
        iconDiv.className = "toast-icon";
        iconDiv.innerHTML = typeof icon === "string" ? icon : (defaultIcons[type] || myIcons.info);
        toast.appendChild(iconDiv);
    }

    // Contenido
    const content = document.createElement("div");
    content.className = "toast-content";

    if (title) {
        const h4 = document.createElement("div");
        h4.className = "toast-title";
        h4.textContent = title;
        content.appendChild(h4);
    }

    if (message) {
        const p = document.createElement("div");
        p.className = "toast-message";
        p.textContent = message;
        content.appendChild(p);
    }

    // Botones
    if (buttons.length > 0) {
        const actions = document.createElement("div");
        actions.className = "toast-actions";
        buttons.forEach(btn => {
            const b = document.createElement("button");
            b.textContent = btn.text;
            b.onclick = (e) => {
                e.stopPropagation();
                btn.onClick?.();
            };
            actions.appendChild(b);
        });
        content.appendChild(actions);
    }

    toast.appendChild(content);

    // Botón de cierre
    if (dismissable) {
        const closeBtn = document.createElement("span");
        closeBtn.className = "toast-close";
        closeBtn.innerHTML = "&times;";
        closeBtn.onclick = () => toast.remove();
        toast.appendChild(closeBtn);
    }

    // Render custom
    if (typeof onRender === "function") {
        onRender(toast);
    }

    // Insertar en contenedor
    container.querySelectorAll(".toast").forEach(t => t.remove());
    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add("show");
    });

    // Cerrar con botón
    if (dismissable) {
        toast.querySelector(".toast-close").addEventListener("click", () => closeToast(toast));
    }

    // Auto remover
    setTimeout(() => closeToast(toast), duration);
}

function closeToast(toast) {
    toast.classList.add("animate__animated", "animate__fadeOutDown");
    toast.classList.remove("show");
}