export class ModalComponent {
    #$modal;
    type;
    modalId;
    components = { buttons: {} };
    options;
    onSave = null;

    constructor(type = "ajustes", options = {}, onSave = null) {
        this.type = type;
        this.modalId = `${this.type}Modal`;
        this.options = {
            size: "md",
            height: false,
            ocultarHeader: false,
            ocultarFooter: false,
            static: true,
            keyboard: false,
            ...options
        };
        this.onSave = onSave;
        this.#$modal = this.createModal();
        this.initComponents();
        this.initEvents();
    }

    /** Getter para acceder al modal jQuery */
    get modal() {
        return this.#$modal;
    }

    /** Crea el modal si no existe */
    createModal() {
        let $modal = $(`#${this.modalId}`);

        // Si ya existe, lo limpiamos por seguridad y lo reutilizamos
        if ($modal.length) {
            $modal.off(); // Evita listeners duplicados
            $modal.remove(); // Eliminamos para crear una versión limpia
        }

        const html = `
            <div class="modal fade" id="${this.modalId}"
                data-bs-backdrop="${this.options.static ? "static" : "true"}"
                data-bs-keyboard="${this.options.keyboard ? "true" : "false"}"
                tabindex="-1" aria-labelledby="${this.modalId}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-${this.options.size}">
                    <div class="modal-content">
                        <div class="modal-header" ${this.options.ocultarHeader ? 'style="display:none;"' : ""}>
                            <h1 class="modal-title fs-5" id="${this.modalId}Label">Nuevo ${this.capitalize(this.type)}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="${this.modalId}Body" 
                            ${this.options.height ? `style="height: ${this.options.height}"` : ""}></div>
                        <div class="modal-footer" ${this.options.ocultarFooter ? 'style="display:none;"' : ""}>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" 
                                id="btnCerrar${this.capitalize(this.type)}">Cerrar</button>
                            <button type="button" class="btn btn-default" 
                                id="btnGuardar${this.capitalize(this.type)}">Guardar cambios</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $("body").append(html);
        this.#$modal = $(`#${this.modalId}`);
        return this.#$modal;
    }

    /** Inicializa referencias internas */
    initComponents() {
        this.components = {
            header: this.#$modal.find(".modal-header"),
            body: this.#$modal.find(".modal-body"),
            footer: this.#$modal.find(".modal-footer"),
            title: this.#$modal.find(".modal-title"),
            dialog: this.#$modal.find(".modal-dialog"),
            buttons: {
                cerrar: this.#$modal.find(`#btnCerrar${this.capitalize(this.type)}`),
                guardar: this.#$modal.find(`#btnGuardar${this.capitalize(this.type)}`)
            }
        };
    }

    /** Inicializa eventos básicos del modal */
    initEvents() {
        const { guardar } = this.components.buttons;

        if (guardar?.length && this.onSave) {
            guardar.off("click").on("click", () => {
                this.onSave(this);
            });
        }

        // Limpia el cuerpo cuando se cierra para evitar fugas de memoria
        this.#$modal.on("hidden.bs.modal", () => {
            this.components.body.html("");
        });
    }

    /** ======= Métodos funcionales ======= */

    show(title = null, body = null) {
        if (title) this.setTitle(title);
        if (body) this.setBody(body);
        Object.entries(this.components).forEach(([key, comp]) => {
            if (key !== "buttons") comp.show();
        });
        this.#$modal.modal("show");
    }

    hide() {
        this.#$modal.modal("hide");
    }

    destroy() {
        this.#$modal.modal("hide");
        this.#$modal.off().remove();
        this.#$modal = null;
        this.components = { buttons: {} };
    }

    /** ======= Métodos setters ======= */

    setTitle(title) {
        this.components.title.text(title);
        return this;
    }

    setSize(size) {
        this.components.dialog.removeClass("modal-md modal-lg modal-xl").addClass(`modal-${size}`);
        this.options.size = size;
        return this;
    }

    setHeight(height) {
        this.components.body.css("height", height);
        this.options.height = height;
        return this;
    }

    setHeader(header) {
        this.components.header.html(header);
        return this;
    }

    setBody(body) {
        this.components.body.html(body);
        return this;
    }

    setFooter(footer) {
        this.components.footer.html(footer);
        return this;
    }

    setOption(key, value) {
        if (typeof key === "object") {
            Object.entries(key).forEach(([k, v]) => this.setOption(k, v));
            return this;
        }

        this.options[key] = value;
        switch (key) {
            case "size":
                this.setSize(value);
                break;
            case "height":
                this.setHeight(value);
                break;
            case "ocultarHeader":
                value ? this.components.header.hide() : this.components.header.show();
                break;
            case "ocultarFooter":
                value ? this.components.footer.hide() : this.components.footer.show();
                break;
            case "static":
                this.#$modal.attr("data-bs-backdrop", value ? "static" : "true");
                break;
            case "keyboard":
                this.#$modal.attr("data-bs-keyboard", value ? "true" : "false");
                break;
        }
        return this;
    }

    /** ======= Métodos de botones ======= */

    addButton(id, label, classes = "btn btn-primary", onClick = null) {
        if (!this.components.footer.length) return null;

        const $btn = $(`<button type="button" id="${id}" class="${classes}">${label}</button>`);
        this.components.footer.append($btn);

        if (onClick) $btn.on("click", () => onClick(this));

        this.components.buttons[id] = $btn;
        return $btn;
    }

    removeButton(id) {
        if (this.components.buttons[id]) {
            this.components.buttons[id].remove();
            delete this.components.buttons[id];
        }
    }

    /** ======= Métodos auxiliares ======= */

    getComponent(component) {
        return this.components[component]?.[0] || this.#$modal.find(component)[0];
    }

    getComponents(selector) {
        return this.#$modal[0].querySelectorAll(selector);
    }

    capitalize(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : "";
    }
}