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
        this.options = Object.assign(
            {
                size: "md",
                height: false,
                ocultarHeader: false,
                ocultarFooter: false,
                static: true,
                keyboard: false,
            },
            options
        );
        this.onSave = onSave;
        this.#$modal = this.createModal();
        this.initComponents();
        this.initEvents();
    }

    get modal() {
        return this.#$modal;
    }

    createModal() {
        let $modal = $(`#${this.modalId}`);

        if ($modal.length) return $modal; // ya existe

        const html = `
            <div class="modal fade" id="${this.modalId}" 
                data-bs-backdrop="${this.options.static ? "static" : "true"}" 
                data-bs-keyboard="${this.options.keyboard ? "true" : "false"}" 
                tabindex="-1" aria-labelledby="${this.modalId}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-${this.options.size}">
                    <div class="modal-content">
                        <div class="modal-header" ${this.options.ocultarHeader ? 'style="display:none;"' : ""}>
                            <h1 class="modal-title text-large" id="${this.modalId}Label">Nuevo ${this.type}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="${this.modalId}Body" ${this.options.height ? `style="height: ${this.options.height}"` : ''}></div>
                        <div class="modal-footer" ${this.options.ocultarFooter ? 'style="display:none;"' : ""}>
                            <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal" id="btnCerrar${this.capitalize(this.type)}">Cerrar</button>
                            <button type="button" class="btn btn-default" id="btnGuardar${this.capitalize(this.type)}">Guardar cambios</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $("body").append(html);
        return $(`#${this.modalId}`);
    }

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

    initEvents() {
        if (this.components.buttons.guardar.length && this.onSave) {
            this.components.buttons.guardar.off("click").on("click", () => {
                this.onSave(this);
            });
        }
    }

    setTitle(title) {
        this.components.title.text(title);
        return this;
    }

    setSize(size) {
        ["md", "lg", "xl"].forEach(medida =>
            this.components.dialog.removeClass("modal-" + medida)
        );
        this.components.dialog.addClass("modal-" + size);
        this.options.size = size;
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

    setAttribute(component, attribute, value) {
        component.attr(attribute, value);
        return this;
    }

    getComponent(component) {
        return this.components[component]?.[0] || this.#$modal.find(component)[0];
    }

    getComponents(selector) {
        return this.#$modal[0].querySelectorAll(selector);
    }

    show(title = null, body = null) {
        if (title) this.components.title.text(title);
        if (body) this.components.body.html(body);
        this.#$modal.modal("show");
    }

    hide() {
        this.#$modal.modal("hide");
    }

    setOption(key, value = null) {
        if (typeof key === "object") {
            Object.entries(key).forEach(([k, v]) => this.setOption(k, v));
            return this;
        }

        this.options[key] = value;
        switch (key) {
            case "size":
                this.setSize(value);
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

    addButton(id, label, classes = "btn btn-default", onClick = null) {
        if (!this.components.footer.length) return null;

        const $btn = $(`<button type="button" id="${id}" class="${classes}">${label}</button>`);
        this.components.footer.append($btn);

        if (onClick) {
            $btn.on("click", () => onClick(this));
        }

        this.components.buttons[id] = $btn;
        return $btn;
    }

    removeButton(id) {
        if (this.components.buttons[id]) {
            this.components.buttons[id].remove();
            delete this.components.buttons[id];
        }
    }

    destroy() {
        this.#$modal.modal("hide");
        this.#$modal.remove();
        this.#$modal = null;
        this.components = { buttons: {} };
    }

    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}