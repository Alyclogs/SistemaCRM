import api from "../utils/api.js";
import { modalCampania } from "./index.js";

(() => {
    const API_SOURCE = "campanias";
    const API_ACTION = "listar";

    const root = document.getElementById("patternWizard");
    if (!root || !modalCampania) return;

    const campaignsListEl = root.querySelector("#campaignsList");
    const summaryEl = root.querySelector("#selectedCampaignSummary");
    const previewEl = root.querySelector("#previewDates");
    const dateInput = root.querySelector("#patternStartDate");
    const btnGuardar = modalCampania.components.buttons.guardar[0];

    let campaigns = [];           // array completo desde backend
    let selectedCampaign = null;  // objeto de campaña elegido
    let includedProgramaciones = new Set(); // ids de programaciones incluidas

    // helper formateo
    const pad = n => String(n).padStart(2, "0");
    function formatDateLocal(d) {
        if (!d) return "";
        const y = d.getFullYear(), m = pad(d.getMonth() + 1), day = pad(d.getDate());
        return `${y}-${m}-${day}`; // input date value
    }
    function formatDateDisplay(d) {
        if (!d) return "";
        const opts = { year: "numeric", month: "short", day: "numeric" };
        return d.toLocaleDateString(undefined, opts);
    }
    function formatTime(t) {
        if (!t) return "";
        // t expected "HH:MM:SS" or "HH:MM"
        const [h, m] = t.split(":");
        return `${pad(h)}:${pad(m)}`;
    }

    // Cambiar seccion (list-view)
    root.querySelectorAll(".list-item").forEach(li => {
        li.addEventListener("click", () => {
            const target = li.dataset.target;

            // Si vamos a fecha, recalc preview
            if (target === "fechaBody") {
                renderSelectedSummary();
                renderPreview();
                modalCampania.setOption("ocultarFooter", false);
            }
        });
    });

    // cargar campañas (intenta usar variable global campaignsData si existe)
    async function loadCampaigns() {
        // si existe global campaignsData (pre-cargada), úsala
        if (window.campaignsData && Array.isArray(window.campaignsData)) {
            campaigns = window.campaignsData;
            renderCampaigns();
            return;
        }

        // petición al backend
        api.get({
            source: API_SOURCE,
            action: API_ACTION,
            onSuccess: (data) => {
                campaigns = Array.isArray(data) ? data : [];
                renderCampaigns();
            },
            onError: (err) => {
                campaignsListEl.innerHTML = `<div class="text-danger p-3">Error cargando patrones</div>`;
                console.error(err);
            }
        });
    }

    function renderCampaigns() {
        if (!campaigns.length) {
            campaignsListEl.innerHTML = `<div class="text-muted p-3">No hay patrones disponibles.</div>`;
            return;
        }

        const html = campaigns.map(c => {
            if (c.estado === "activa") return '';

            return `
        <div class="campaign-card" data-id="${c.idcampania}">
          <div class="campaign-header">
            <div style="display:flex;gap:10px;align-items:center;">
              <input type="radio" name="selectedPattern" class="pattern-radio" value="${c.idcampania}" id="pattern_${c.idcampania}">
              <div>
                <div class="campaign-title">${escapeHtml(c.nombre || 'Sin nombre')}</div>
                <div class="small-muted">${escapeHtml(c.descripcion || '')}</div>
              </div>
            </div>
            <div class="small-muted">Estado: ${escapeHtml(c.estado || '')}</div>
          </div>

          <div class="programacion-list" id="progList_${c.idcampania}">
            ${(c.programaciones || []).map(p => `
              <div class="programacion-item">
                <div class="programacion-left">
                  <input type="checkbox" class="prog-checkbox" data-idenvio="${p.idenvio}" data-idplantilla="${p.idplantilla}" checked />
                  <div>
                    <div style="font-weight:600">${escapeHtml(p.plantilla_nombre || 'Plantilla')}</div>
                    <div class="programacion-meta">${p.dias_despues !== null ? `Días después: ${p.dias_despues}` : ''}
                      ${p.dia_semana !== null ? ` - Día semana: ${p.dia_semana}` : ''} 
                      ${p.hora_envio ? ` - ${formatTime(p.hora_envio)}` : ''}</div>
                  </div>
                </div>
              </div>
            `).join('')}
          </div>
        </div>
      `;
        }).join('');

        campaignsListEl.innerHTML = html;

        // listeners: seleccionar patrón
        campaignsListEl.querySelectorAll(".pattern-radio").forEach(r => {
            r.addEventListener("change", (e) => {
                const id = Number(e.target.value);
                selectedCampaign = campaigns.find(cc => +cc.idcampania === id) || null;
                // marcar todas las checkboxes de esa tarjeta como checked por defecto
                includedProgramaciones = new Set();
                const container = campaignsListEl.querySelector(`[data-id="${id}"]`);
                if (container) {
                    container.querySelectorAll(".prog-checkbox").forEach(cb => {
                        cb.checked = true;
                        includedProgramaciones.add(cb.dataset.idenvio ? String(cb.dataset.idenvio) : String(cb.dataset.idplantilla));
                    });
                }
                // sincronizar vista resumen y fecha preview
                renderSelectedSummary();
                renderPreview();
            });
        });

        // listeners: programacion checkboxes
        campaignsListEl.querySelectorAll(".prog-checkbox").forEach(cb => {
            cb.addEventListener("change", (e) => {
                const idenvio = String(cb.dataset.idenvio);
                if (cb.checked) includedProgramaciones.add(idenvio);
                else includedProgramaciones.delete(idenvio);
                // actualizar preview si estamos en step2
                if (root.querySelector('.list-item[data-target="fechaBody"]').classList.contains('selected')) {
                    renderPreview();
                }
            });
        });

        // auto-select first campaign
        const firstRadio = campaignsListEl.querySelector(".pattern-radio");
        if (firstRadio) {
            firstRadio.checked = true;
            firstRadio.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function renderSelectedSummary() {
        if (!selectedCampaign) {
            summaryEl.innerHTML = `<div class="text-muted">No hay patrón seleccionado</div>`;
            return;
        }
        const progs = (selectedCampaign.programaciones || []).length;
        summaryEl.innerHTML = `
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
          <div style="font-weight:700">${escapeHtml(selectedCampaign.nombre)}</div>
          <div class="small-muted">${escapeHtml(selectedCampaign.descripcion || '')}</div>
          <div class="small-muted">Programaciones: ${progs}</div>
        </div>
        <div class="small-muted">Modalidad: ${escapeHtml(selectedCampaign.modalidad_envio || '')}</div>
      </div>
    `;
    }

    // Calcula las fechas preview en base a fechaInicio y patron seleccionado
    function computePreview(startDate) {
        if (!selectedCampaign || !startDate) return [];

        const modalidad = selectedCampaign.modalidad_envio || 'dias_especificos';
        const start = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate()); // midnight local
        const preview = [];

        // recorre programaciones en orden (mantener orden de array)
        (selectedCampaign.programaciones || []).forEach((p, idx) => {
            // ignorar si programacion no incluida
            if (!includedProgramaciones.has(String(p.idenvio))) return;

            let fecha = null;

            if (modalidad === 'dias_especificos') {
                // asumimos dias_despues relativo a fecha inicio (no acumulativo)
                const dias = Number(p.dias_despues ?? 0);
                fecha = new Date(start);
                fecha.setDate(fecha.getDate() + dias);
            } else if (modalidad === 'dias_semana') {
                // asume campaign <= 1 semana: dia_semana: 0..6 (0 = Lunes)
                // asumimos startDate es inicio de la semana (si no, simplemente start + dia_semana)
                const diaSemana = p.dia_semana ?? 0;
                fecha = new Date(start);
                fecha.setDate(fecha.getDate() + Number(diaSemana));
            } else {
                // fallback: usar start
                fecha = new Date(start);
            }

            // agregar hora si existe
            let hora = p.hora_envio || p.hora || null;
            if (hora && hora.indexOf && hora.indexOf(':') === -1 && hora.length === 4) {
                // formato HHMM -> HH:MM
                hora = hora.slice(0, 2) + ':' + hora.slice(2);
            }

            preview.push({
                idenvio: p.idenvio,
                idplantilla: p.idplantilla,
                plantilla_nombre: p.plantilla_nombre,
                fecha_envio: fecha,
                hora_envio: hora
            });
        });

        return preview;
    }

    function renderPreview() {
        const val = dateInput.value;
        if (!selectedCampaign) {
            previewEl.innerHTML = `<div class="text-muted p-2">Primero selecciona un patrón en el paso 1</div>`;
            return;
        }
        if (!val) {
            previewEl.innerHTML = `<div class="text-muted p-2">Selecciona una fecha de inicio para ver la previsualización</div>`;
            return;
        }

        const sd = new Date(val + "T00:00:00"); // ensure local
        const rows = computePreview(sd);

        if (!rows.length) {
            previewEl.innerHTML = `<div class="text-muted p-2">No hay programaciones seleccionadas</div>`;
            return;
        }

        const html = rows.map(r => `
            <div class="preview-row">
                <div>
                <div style="font-weight:600">${escapeHtml(r.plantilla_nombre || 'Plantilla')}</div>
                <div class="small-muted">${formatDateDisplay(r.fecha_envio)} ${r.hora_envio ? ' - ' + formatTime(r.hora_envio) : ''}</div>
                </div>
            </div>
            `).join('');

        previewEl.innerHTML = html;
    }

    // Exponer getter para que el botón "Guardar" del modal lo use
    window.getSelectedPattern = function () {
        if (!selectedCampaign) return null;
        const startVal = dateInput.value || null;
        const preview = startVal ? computePreview(new Date(startVal + "T00:00:00")) : [];
        return {
            idcampania: selectedCampaign.idcampania,
            nombre: selectedCampaign.nombre,
            programaciones: preview.map(p => ({
                idenvio: p.idenvio,
                idplantilla: p.idplantilla,
                fecha_envio: formatDateLocal(p.fecha_envio) + (p.hora_envio ? ' ' + formatTime(p.hora_envio) + ':00' : ' 00:00:00'),
                hora_envio: p.hora_envio
            })),
            fecha_inicio: startVal
        };
    };

    function saveCampaign() {
        const selection = window.getSelectedPattern();
        if (!selection) {
            mostrarToast({ title: "Selecciona un patrón y una fecha de inicio", type: "error" });
        } else {
            const fd = new FormData();
            fd.append('idcampania', selection.idcampania);
            fd.append('nombre', selection.nombre);
            fd.append('fecha_inicio', selection.fecha_inicio);
            fd.append('programaciones', JSON.stringify(selection.programaciones));

            api.post({
                source: "campanias",
                action: "iniciarCampania",
                data: fd,
                onSuccess: () => {
                    modalCampania.destroy();
                }
            });
        }
    }

    // helpers
    function escapeHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // listeners
    dateInput.addEventListener('change', renderPreview);
    btnGuardar.addEventListener('click', saveCampaign);

    loadCampaigns();
})();

