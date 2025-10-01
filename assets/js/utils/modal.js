export class ModalComponent {
    #$modal;
    type;
    modalId;
    components = {};
    options;
    onSave = null;

    constructor(type = "ajustes", options = {}, onSave = null) {
        this.type = type;
        this.modalId = `${this.type}Modal`;
        this.options = Object.assign(
            {
                size: "md",
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

    /**
     * Crea la estructura HTML del modal si no existe
     */
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
                        <div class="modal-header">
                            <h1 class="modal-title text-large" id="${this.modalId}Label">Nuevo ${this.type}</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="${this.modalId}Body"></div>
                        <div class="modal-footer" ${this.options.ocultarFooter ? 'style="display:none;"' : ""}>
                            <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-default" id="btnGuardar${this.capitalize(this.type)}">Guardar cambios</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $("body").append(html);
        return $(`#${this.modalId}`);
    }

    /**
     * Guarda referencias directas a los componentes del modal
     */
    initComponents() {
        this.components = {
            header: this.#$modal.find(".modal-header"),
            body: this.#$modal.find(".modal-body"),
            footer: this.#$modal.find(".modal-footer"),
            btnGuardar: this.#$modal.find(`#btnGuardar${this.capitalize(this.type)}`),
            title: this.#$modal.find(".modal-title")
        };
    }

    /**
     * Inicializa eventos como el botón guardar
     */
    initEvents() {
        if (this.components.btnGuardar.length && this.onSave) {
            this.components.btnGuardar.off("click").on("click", () => {
                this.onSave(this);
            });
        }
    }

    setTitle(title) {
        this.components.title.text(title);
        return this;
    }

    setSize(size) {
        ["md", "lg", "xl"].forEach(medida => this.#$modal
            .find(".modal-dialog")
            .removeClass("modal-" + medida));
        this.#$modal.find(".modal-dialog").addClass("modal-" + size);
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
        this.components[component]?.attr(attribute, value);
    }

    getComponent(component) {
        return this.components[component] || null;
    }

    show(title = null, body = null) {
        if (title) this.components.title.text(title);
        if (body) this.components.body.html(body);
        this.#$modal.modal("show");
    }

    hide() {
        this.#$modal.modal("hide");
    }

    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
}