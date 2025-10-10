// clientes-table.js
import api from "../utils/api.js";
import { ModalComponent } from "../utils/modal.js";
import { abrirModal, eliminarRegistro, guardarRegistro } from "./utils.js";

let filtroBuscado = '';
let selectedTipo = '1';
let clientesCache = [];        // todos los clientes traídos del backend
let columnsConfig = [];        // configuración cargada desde diccionario_campos
let currentPage = 1;
let selectedUsuario = document.getElementById('idUsuario').value;
export let modalCampania = null;

const defaultColumns = [
    { campo: "foto", nombre: "Foto", visible: 1, orden: 0 },
    { campo: "nombres", nombre: "Nombres", visible: 1, orden: 1 },
    { campo: "apellidos", nombre: "Apellidos", visible: 1, orden: 2 },
    { campo: "empresa_nombre", nombre: "Empresa", visible: 1, orden: 3 },
    { campo: "num_doc", nombre: "N° Documento", visible: 1, orden: 4 },
    { campo: "telefono", nombre: "Teléfono", visible: 1, orden: 5 },
    { campo: "correo", nombre: "Correo electrónico", visible: 1, orden: 6 },
    { campo: "idestado", nombre: "Estado", visible: 1, orden: 7 }
];

async function loadTableSettings() {
    return new Promise(resolve => {
        api.get({
            source: "diccionario",
            action: "listar",
            params: [{ name: "tabla", value: selectedTipo == 1 ? "clientes" : "empresas" }],
            onSuccess: (cols) => {
                if (Array.isArray(cols) && cols.length) {
                    columnsConfig = cols.map(c => ({
                        campo: c.campo,
                        nombre: c.nombre || c.label || c.campo,
                        descripcion: c.descripcion || '',
                        tipo_dato: c.tipo_dato || 'texto',
                        visible: (c.visible == 1 || c.visible === true) ? 1 : 0,
                        orden: parseInt(c.orden ?? 0, 10),
                        contexto: c.contexto || 'general',
                        meta: c.meta ? (typeof c.meta === 'string' ? JSON.parse(c.meta) : c.meta) : null
                    })).sort((a, b) => a.orden - b.orden);
                } else {
                    columnsConfig = defaultColumns.map(c => ({
                        campo: c.campo,
                        nombre: c.nombre,
                        descripcion: '',
                        tipo_dato: 'texto',
                        visible: c.visible,
                        orden: c.orden,
                        contexto: 'general',
                        meta: null
                    }));
                }
                resolve();
            },
            onError: () => {
                // fallback a defaults si hay error
                columnsConfig = defaultColumns.map(c => ({
                    campo: c.campo,
                    nombre: c.nombre,
                    descripcion: '',
                    tipo_dato: 'texto',
                    visible: c.visible,
                    orden: c.orden,
                    contexto: 'general',
                    meta: null
                }));
                resolve();
            }
        });
    });
}

function fetchClientes(filtro = "", tipo = "1", idusuario) {
    filtroBuscado = filtro ?? filtroBuscado;
    selectedTipo = tipo ?? selectedTipo;
    selectedUsuario = idusuario ?? selectedUsuario;

    const params = [{ name: 'tipo', value: tipo }];
    if (filtroBuscado) params.push({ name: 'filtro', value: filtroBuscado });
    if (selectedUsuario) params.push({ name: 'idusuario', value: selectedUsuario });

    // Indicador
    const container = (tipo == '1') ? document.getElementById('tablaClientesBody') : document.getElementById('tablaOrganizacionesBody');
    const wrapperTable = (tipo == '1') ? document.getElementById('tablaClientes') : document.getElementById('tablaOrganizaciones');
    if (container) container.innerHTML = '<tr><td colspan="10">Cargando...</td></tr>';
    if (wrapperTable) wrapperTable.style.display = 'table';

    api.get({
        source: "clientes",
        action: filtroBuscado === '' ? "listar" : "buscar",
        params,
        onSuccess: async (items) => {
            clientesCache = Array.isArray(items) ? items : [];
            currentPage = 1;
            await renderTablePage(currentPage);
        },
        onError: (err) => {
            if (container) container.innerHTML = `<tr><td colspan="10">Error cargando datos</td></tr>`;
            console.error("Error fetchClientes:", err);
        }
    });
}

function buildVisibleColumns(columnsConfig, clientesPage) {
    // asegurarnos que meta sea objeto
    const normalize = c => {
        const copy = Object.assign({}, c);
        if (copy.meta && typeof copy.meta === "string") {
            try { copy.meta = JSON.parse(copy.meta); } catch (e) { copy.meta = null; }
        }
        return copy;
    };
    const cols = columnsConfig.map(normalize).filter(c => c.visible == 1).slice();

    // map de grupos
    const groups = {};
    const singles = [];

    cols.forEach(col => {
        const meta = col.meta || {};
        if (meta && meta.combineGroup) {
            const g = String(meta.combineGroup);
            if (!groups[g]) groups[g] = { members: [], photoInside: false, orden: Number.POSITIVE_INFINITY, nombre: meta.groupLabel || g, hideIfEmpty: !!meta.hideIfEmpty };
            groups[g].members.push({ campo: col.campo, orderInGroup: meta.orderInGroup ?? 0, col: col });
            if (meta.photoInside) groups[g].photoInside = true;
            groups[g].orden = Math.min(groups[g].orden, col.orden ?? Number.POSITIVE_INFINITY);
        } else {
            singles.push(col);
        }
    });

    // transformar grupos en columnas combinadas, respetando hideIfEmpty (evaluado sobre clientesPage)
    const groupCols = Object.keys(groups).map(key => {
        const g = groups[key];
        g.members.sort((a, b) => (a.orderInGroup || 0) - (b.orderInGroup || 0));
        const memberFields = g.members.map(m => m.campo);

        // evaluar hideIfEmpty: si true y ningún cliente en pagina tiene valores -> omitimos la columna
        if (g.hideIfEmpty) {
            const existe = (clientesPage || []).some(cliente => {
                for (const f of memberFields) {
                    const v = cliente[f];
                    if (v !== undefined && v !== null && String(v).trim() !== '') return true;
                }
                if (g.photoInside && cliente.foto && String(cliente.foto).trim() !== '') return true;
                return false;
            });
            if (!existe) {
                return null; // será filtrado luego
            }
        }

        return {
            campo: 'combined__' + key,
            nombre: g.nombre || key,
            orden: g.orden === Number.POSITIVE_INFINITY ? 0 : g.orden,
            meta: {
                members: memberFields,
                photoInside: !!g.photoInside,
                hideIfEmpty: !!g.hideIfEmpty
            }
        };
    }).filter(Boolean);

    // final: combinar singles y groupCols y ordenar por orden
    const final = singles.concat(groupCols).sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0));
    return final;
}

function getColsFromThead(tr) {
    const ths = Array.from(tr.children).slice(1, -1); // ignorar checkbox y acciones
    return ths.map((th, idx) => {
        const campo = th.dataset.campo;
        const labelEl = th.querySelector('.col-label');
        const nombre = labelEl ? labelEl.textContent.trim() : (th.textContent || campo).trim();
        let meta = {};
        try {
            const raw = th.dataset.meta;
            if (raw && raw !== "{}") {
                meta = typeof raw === "object" ? raw : JSON.parse(raw);
            }
        } catch (e) {
            meta = {};
        }
        return { campo, nombre, meta, orden: idx };
    });
}

function syncColumnsConfigFromThead(tr) {
    const domCols = getColsFromThead(tr);

    domCols.forEach((d, idx) => {
        const campo = d.campo;
        // caso columna base existente
        const cfg = columnsConfig.find(c => c.campo === campo);
        if (cfg) {
            cfg.orden = idx;
            // actualizar meta si hay (mantener objeto)
            if (d.meta && Object.keys(d.meta).length) cfg.meta = d.meta;
            return;
        }

        // caso columna combinada: propagar orden/meta a sus miembros en columnsConfig
        if (campo && String(campo).startsWith('combined__')) {
            const groupKey = campo.replace('combined__', '');
            // actualizar columnas que tengan meta.combineGroup === groupKey
            columnsConfig.forEach(c => {
                try {
                    const m = c.meta && typeof c.meta === 'string' ? JSON.parse(c.meta) : c.meta;
                    if (m && m.combineGroup === groupKey) {
                        c.orden = idx;
                        // opcional: si dom provee meta.members, sincronizar (solo si viene)
                        if (d.meta && d.meta.members) {
                            c.meta = Object.assign(m || {}, m, c.meta || {}, d.meta);
                        }
                    }
                } catch (e) {
                    // ignore parse errors
                }
            });
        }
    });

    // Asegurar consistencia: ordenar columnsConfig in-memory por orden
    columnsConfig.sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0));
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function renderRowCell(cliente, col) {
    // columna combinada
    if (String(col.campo).startsWith('combined__')) {
        const meta = col.meta || {};
        const members = Array.isArray(meta.members) ? meta.members : [];

        // obtener valores de members en orden
        const parts = members.map(key => {
            const v = cliente[key];
            return (v === null || v === undefined) ? '' : String(v).trim();
        }).filter(Boolean);

        const displayName = parts.join(' ').trim();
        const fotoUrl = (meta.photoInside && cliente.foto && cliente.foto.trim() !== '')
            ? cliente.foto
            : '';

        if (!displayName) return '-';

        if (fotoUrl) {
            return `
                <div class="d-flex align-items-center">
                    <img src="${escapeHtml(fotoUrl)}"
                         alt="${escapeHtml(displayName)}"
                         class="user-icon sm clickable me-2"
                         data-id="${cliente.idcliente}" data-type="cliente">
                    <span class="fw-semibold user-link clickable"
                    data-id="${cliente.idcliente}" data-type="cliente">${escapeHtml(displayName)}</span>
                </div>
            `;
        }

        // Si hay solo nombre → mostrar texto
        return `<span class="fw-semibold">${escapeHtml(displayName)}</span>`;
    }

    // columna normal
    const val = cliente[col.campo];
    if (val === undefined || val === null || String(val).trim() === '') return '-';
    return escapeHtml(String(val));
}

async function renderTablePage(page = 1) {
    // Asegúrate de cargar configuración de columnas
    if (!columnsConfig || columnsConfig.length === 0) {
        await loadTableSettings();
    }

    const perPageInput = document.getElementById('registrosPaginaInput');
    const perPage = Math.max(1, parseInt(perPageInput?.value || 10, 10));
    const start = (page - 1) * perPage;
    const end = start + perPage;
    const clientes = clientesCache.slice(start, end);

    // DOM refs
    const table = document.getElementById(selectedTipo == '1' ? 'tablaClientes' : 'tablaOrganizaciones');
    const tbody = document.getElementById(selectedTipo == '1' ? 'tablaClientesBody' : 'tablaOrganizacionesBody');

    if (!table || !tbody) return;

    const visibleCols = buildVisibleColumns(columnsConfig, clientes);

    // Cabecera: armar <thead>
    const thead = table.querySelector('thead');
    if (!thead) {
        console.warn("La tabla debe tener <thead> en el HTML");
    } else {
        const tr = document.createElement('tr');

        // checkbox seleccionar todo
        const thSelect = document.createElement('th');
        thSelect.innerHTML = `<input type="checkbox" id="selectAllClients" />`;
        tr.appendChild(thSelect);

        const initialVisible = buildVisibleColumns(columnsConfig, clientes);
        initialVisible.forEach(col => {
            const metaStr = col.meta ? JSON.stringify(col.meta) : '{}';
            const th = document.createElement('th');
            th.dataset.campo = col.campo;
            th.dataset.meta = metaStr;
            th.style.cursor = 'move';
            th.innerHTML = `<span class="col-label">${col.nombre || col.campo}</span>`;
            tr.appendChild(th);
        });

        // columna acciones
        const thAcc = document.createElement('th');
        thAcc.textContent = "Acciones";
        tr.appendChild(thAcc);

        thead.innerHTML = '';
        thead.appendChild(tr);

        // Inicializar SortableJS en encabezado para reordenar y preservar meta
        if (window.Sortable) {
            if (thead._sortable) { try { thead._sortable.destroy(); } catch (e) { } }
            thead._sortable = Sortable.create(tr, {
                animation: 150,
                handle: '.col-label',
                onEnd: function (evt) {
                    syncColumnsConfigFromThead(tr);
                    guardarColumnConfig();

                    // Re-renderizar la página actual para que cuerpo coincida con nuevo orden
                    setTimeout(() => {
                        renderTablePage(currentPage);
                    }, 0);
                }
            });
        }
    }

    // Filas
    let html = '';
    if (!clientes || clientes.length === 0) {
        html = `<tr><td colspan="${2 + visibleCols.length}">No se encontraron resultados</td></tr>`;
    } else {
        clientes.forEach(cliente => {
            const id = cliente.idcliente || cliente.idempresa || '';
            html += `<tr data-id="${id}">`;

            // checkbox
            html += `<td><input type="checkbox" class="client-checkbox" value="${id || ''}" data-type="${selectedTipo}"></td>`;

            // columnas según visibleCols
            visibleCols.forEach(col => {
                html += `<td>${renderRowCell(cliente, col)}</td>`;
            });

            // acciones
            html += `<td>
                        <div class="icons-row">
                            <button class="btn btn-icon bg-light btn-edit-client" data-id="${id}" data-type="${selectedTipo}">${window.icons.edit}</button>
                            <button class="btn btn-icon bg-light btn-delete-client" data-id="${id}" data-type="${selectedTipo}">${window.icons.trash}</button>
                        </div>
                    </td>`;

            html += `</tr>`;
        });
    }

    tbody.innerHTML = html;
    table.style.display = 'table';

    // Render paginación
    renderPagination(clientesCacheLength(), perPage, page);
}

function guardarColumnConfig() {
    const payload = columnsConfig.map(c => ({
        campo: c.campo,
        nombre: c.nombre,
        descripcion: c.descripcion || '',
        tipo_dato: c.tipo_dato || 'texto',
        visible: c.visible ? 1 : 0,
        orden: c.orden ?? 0,
        contexto: c.contexto || 'general',
        meta: c.meta || null
    }));
    const form = new FormData();
    form.append('tabla', selectedTipo == 1 ? 'clientes' : 'empresas');
    form.append('columnas', JSON.stringify(payload));

    api.post({
        source: "diccionario",
        action: "save",
        data: form,
        onSuccess: () => {
            // recarga configuración y refresca vista
            loadTableSettings().then(() => renderTablePage(currentPage));
        },
        onError: (err) => {
            console.error("Error guardando columnas:", err);
            loadTableSettings().then(() => renderTablePage(currentPage));
        }
    });
}

function clientesCacheLength() {
    return clientesCache.length;
}

function renderPagination(totalItems, perPage, page) {
    const pager = document.getElementById('clientesPager');
    if (!pager) return;

    const totalPages = Math.max(1, Math.ceil(totalItems / perPage));
    let html = '';

    html += `<button class="btn btn-icon border mx-1" data-page="${Math.max(1, page - 1)}" ${page === 1 ? 'disabled' : ''}>${window.icons.arrowLeft}</button>`;

    const maxButtons = 7;
    let start = Math.max(1, page - Math.floor(maxButtons / 2));
    let end = Math.min(totalPages, start + maxButtons - 1);
    if (end - start < maxButtons - 1) start = Math.max(1, end - maxButtons + 1);

    for (let p = start; p <= end; p++) {
        html += `<button class="btn icon ${p === page ? 'bg-default' : 'bg-light'}" data-page="${p}">${p}</button>`;
    }

    html += `<button class="btn btn-icon border mx-1" data-page="${Math.min(totalPages, page + 1)}" ${page === totalPages ? 'disabled' : ''}>${window.icons.arrowRight}</button>`;

    pager.innerHTML = html;
}

function openColumnSettings() {
    if (!document.getElementById('columnSettingsModal')) {
        const modalHtml = `
            <div class="modal fade" id="columnSettingsModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header"><h5 class="modal-title">Ajustar columnas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <div id="columnsList" class="list-group"></div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" id="btnSaveColumns" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                  </div>
                </div>
              </div>
            </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    const columnsList = document.getElementById('columnsList');
    columnsList.innerHTML = '';

    // items ordenados por 'orden'
    const ordered = columnsConfig.slice().sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0));
    ordered.forEach(col => {
        const item = document.createElement('div');
        item.className = 'list-group-item d-flex align-items-center';
        item.dataset.campo = col.campo;
        item.innerHTML = `
            <span class="me-2 handle" style="cursor:grab;">☰</span>
            <input type="checkbox" class="me-2 col-visible" ${col.visible ? 'checked' : ''}/>
            <span class="flex-grow-1">${col.nombre}</span>
        `;
        columnsList.appendChild(item);
    });

    if (window.Sortable) {
        if (columnsList._sortable) try { columnsList._sortable.destroy(); } catch (e) { }
        columnsList._sortable = Sortable.create(columnsList, {
            handle: '.handle',
            animation: 150
        });
    }

    const modalEl = new bootstrap.Modal(document.getElementById('columnSettingsModal'));
    modalEl.show();

    document.getElementById('btnSaveColumns').onclick = function () {
        const items = Array.from(columnsList.children);
        items.forEach((it, idx) => {
            const campo = it.dataset.campo;
            const cfg = columnsConfig.find(c => c.campo === campo);
            if (cfg) {
                cfg.orden = idx;
                cfg.visible = !!it.querySelector('.col-visible').checked ? 1 : 0;
            }
        });
        guardarColumnConfig();
        modalEl.hide();
    };
}

function getSelectedClients() {
    return Array.from(document.querySelectorAll('.client-checkbox:checked')).map(cb => ({ id: cb.value, tipo: cb.dataset.type }));
}

// Vincular evento a todos los checkboxes
function initClientCheckboxListeners() {
    document.querySelectorAll('.client-checkbox').forEach(cb => {
        cb.addEventListener('change', toggleFloatingButton);
    });
}

document.addEventListener('click', function (e) {
    // abrir modal de ajustes de columnas
    if (e.target.closest('#btnTableSettings')) {
        openColumnSettings();
    }

    // paginador
    if (e.target.closest('#clientesPager button')) {
        const btn = e.target.closest('button');
        const page = parseInt(btn.dataset.page, 10);
        currentPage = page;
        renderTablePage(currentPage);
    }

    // seleccionar todo
    if (e.target.closest('#selectAllClients')) {
        const checked = e.target.closest('#selectAllClients').checked;
        document.querySelectorAll('.client-checkbox').forEach(cb => cb.checked = checked);
    }

    // editar / eliminar
    if (e.target.closest('.btn-edit-client')) {
        const id = e.target.closest('.btn-edit-client').dataset.id;
        const tipo = e.target.closest('.btn-edit-client').dataset.type;
        abrirModal({ tipo, id });
    }
    if (e.target.closest('.btn-delete-client')) {
        const id = e.target.closest('.btn-delete-client').dataset.id;
        const tipo = e.target.closest('.btn-delete-client').dataset.type;
        eliminarRegistro(tipo, id, () => fetchClientes('', selectedTipo));
    }

    // handlers previos adaptados
    if (e.target.closest('#btnRefresh')) {
        fetchClientes();
        selectedTipo = '1';
        document.querySelectorAll('.selected-filtro').forEach(el => {
            const grupo = el.closest('.busqueda-grupo');
            if (grupo && grupo.dataset.type !== "Tipo") {
                el.textContent = grupo.dataset.type || '';
            }
        });
        document.querySelectorAll('.boton-filtro').forEach(el => {
            const grupo = el.closest('.busqueda-grupo');
            if (grupo && grupo.dataset.type !== "Tipo") {
                el.classList.remove('selected');
            }
        });
        document.querySelectorAll('.filtro-item').forEach(el => el.classList.remove('selected'));
        document.querySelectorAll('.busqueda-grupo').forEach(el => el.style.display = '');
        const newSelectedTipo = document.querySelector(`.filtro-item[data-id="${selectedTipo}"]`);
        if (newSelectedTipo) newSelectedTipo.classList.add('selected');
        const tipoText = document.querySelector('#tipoCliente');
        if (tipoText) tipoText.textContent = "Clientes";
        const tiposClientes = document.getElementById('tiposClientes');
        if (tiposClientes) tiposClientes.classList.add('selected');
    }

    if (e.target.closest('.list-item')) {
        const tab = e.target.closest('.list-item');
        if (!tab.dataset.tipo) return;

        selectedTipo = tab.dataset.tipo || '1';
        loadTableSettings();
        fetchClientes(filtroBuscado, selectedTipo, selectedUsuario);
    }

    if (e.target.closest('#btnNuevoRegistro')) {
        abrirModal({ tipo: selectedTipo, esNuevo: true });
    }

    if (e.target.closest('#btnProyectosCliente')) {
        e.stopPropagation();
        const idcliente = e.target.closest('#btnProyectosCliente').dataset.id;
        fetch(window.baseurl + "views/components/selectModal.php?source=proyectos&type=multiple&id=" + idcliente)
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

    if (e.target.closest('#btnGuardarCliente')) {
        guardarRegistro(selectedTipo, () => {
            fetchClientes('', selectedTipo, selectedUsuario);
        });
    }

    if (e.target.closest("#btnNuevaOrganizacion")) {
        e.stopPropagation();
        const value = e.target.closest("#btnNuevaOrganizacion").dataset.value;
        const formData = new FormData();
        formData.append("razon_social", value);

        api.post({
            source: "clientes",
            action: "createOrganizacion",
            data: formData,
            onSuccess: (response) => {
                const hiddenId = document.getElementById("idOrganizacionInput");
                if (hiddenId) hiddenId.value = response.id;
            }
        });
        const resultados = document.querySelector(`[data-parent="organizacionInput"]`);
        if (resultados) {
            resultados.innerHTML = "";
            resultados.style.display = "none";
        }
    }

    if (e.target.closest("#btnCrearCampania")) {
        const seleccionados = getSelectedClients();
        if (seleccionados.length === 0) return;
        modalCampania = new ModalComponent("clienteCampania", { size: "lg" });

        fetch(window.baseurl + "views/components/clientes/crearCampania.php")
            .then((res) => res.text())
            .then((html) => {
                modalCampania.show("Crear campaña", html);
                modalCampania.setOption("ocultarFooter", true);
            });
    }

    if (e.target.closest(".org-item")) {
        const target = e.target.closest(".org-item");

        const input = document.getElementById("organizacionInput");
        const hiddenId = document.getElementById("idOrganizacionInput");
        if (input) input.value = target.dataset.value;
        if (hiddenId) hiddenId.value = target.dataset.id;

        const resultados = document.querySelector(`[data-parent="organizacionInput"]`);
        if (resultados) {
            resultados.innerHTML = "";
            resultados.style.display = "none";
        }
    }

    if (e.target.closest(".usuario-item")) {
        const target = e.target.closest(".usuario-item");
        selectedUsuario = target.dataset.id;
        api.get({
            source: "usuarios",
            action: "ver",
            params: [{ name: "idusuario", value: selectedUsuario }],
            onSuccess: (usuario) => {
                document.getElementById("usuarioActualNombre").textContent = `${usuario.nombres} ${usuario.apellidos}`;
                document.getElementById("usuarioActualFoto").src = usuario.foto;
                target.closest(".resultados-busqueda").querySelectorAll(".usuario-item").forEach(el => el.classList.remove("selected"));
                target.classList.add("selected");
                fetchClientes(filtroBuscado, selectedTipo, selectedUsuario);
                target.closest(".resultados-busqueda").style.display = "none";
            }
        });
    }
});

document.addEventListener('change', function (e) {
    if (e.target.closest('.client-checkbox') || e.target.closest("#selectAllClients")) {
        const seleccionados = getSelectedClients();
        const btnCampania = document.getElementById("floatingButton");
        if (seleccionados.length > 0) {
            btnCampania.classList.add("show");
            btnCampania.classList.remove("hide");
        } else {
            btnCampania.classList.remove("show");
            btnCampania.classList.add("hide");
        }
    }
});

document.addEventListener('input', function (e) {
    if (e.target.closest('#organizacionInput')) {
        const target = e.target.closest('#organizacionInput');
        const value = target.value;
        const resultados = document.querySelector(`[data-parent="${target.id}"]`);
        let html = '';

        const buttonNewOrganizacion = `
        <div class="resultado-item bg-secondary text-white" id="btnNuevaOrganizacion" data-value="${value}">
            ${window.getIcon("add", "white")}<span>Agregar ${value} como nueva organización</span>
        </div>
        `;

        if (value.length > 2) {
            api.get({
                source: "clientes",
                action: "buscarOrganizaciones",
                params: [{ name: "filtro", value }],
                onSuccess: (organizaciones) => {
                    if (!resultados) return;
                    if (organizaciones.length === 0) {
                        html = buttonNewOrganizacion;
                        resultados.innerHTML = html;
                        resultados.style.display = "flex";
                        return;
                    }

                    organizaciones.forEach(org => {
                        html += `<div class="resultado-item org-item" data-value="${org.razon_social}" data-id="${org.idempresa}">
                            ${window.icons.building}${org.razon_social}
                        </div>`;
                    });

                    if (!organizaciones.some(org =>
                        org.razon_social.toLowerCase().trim() === value.toLowerCase().trim()
                        || org.ruc.toLowerCase().trim() === value.toLowerCase().trim()
                    )) {
                        html += buttonNewOrganizacion;
                    }

                    resultados.innerHTML = html;
                    resultados.style.display = "flex";
                }
            });
        } else if (resultados) {
            resultados.innerHTML = '';
            resultados.style.display = "none";
        }
    }

    // búsqueda general de clientes
    if (e.target.closest('#inputBuscarClientes')) {
        const input = e.target.closest('#inputBuscarClientes');
        filtroBuscado = input.value.trim();
        fetchClientes(filtroBuscado, selectedTipo);
    }

    // cambio de registros por página
    if (e.target.closest('#registrosPaginaInput')) {
        currentPage = 1;
        renderTablePage(currentPage);
    }
});

document.addEventListener('DOMContentLoaded', async function () {
    await loadTableSettings();
    initClientCheckboxListeners();
    fetchClientes();
});