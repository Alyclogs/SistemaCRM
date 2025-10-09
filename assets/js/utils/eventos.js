import api from "./api.js";

async function verificarEnvios() {
    console.log("⏳ Verificando programaciones activas...");

    await api.get({
        source: "campanias",
        action: "obtenerProgramacionesPendientes",
        onSuccess: async (programaciones) => {
            if (!programaciones?.length) {
                console.log("✅ No hay programaciones pendientes.");
                return;
            }

            const ahora = new Date();

            // Agrupar programaciones por campaña
            const campanias = {};
            for (const prog of programaciones) {
                if (!campanias[prog.idcampania]) campanias[prog.idcampania] = [];
                campanias[prog.idcampania].push(prog);
            }

            // Procesar campañas activas
            for (const [idcampania, programacionesCampania] of Object.entries(campanias)) {
                const campania = programacionesCampania[0];
                const fechaInicioCamp = new Date(campania.fecha_inicio);
                const modalidad = campania.modalidad_envio || "dias_especificos";

                // día de semana base (0=lunes ... 6=domingo)
                const diaSemanaInicio = (fechaInicioCamp.getDay() + 6) % 7;

                programacionesCampania.sort((a, b) => a.idenvio - b.idenvio);

                for (let i = 0; i < programacionesCampania.length; i++) {
                    const prog = programacionesCampania[i];

                    let fechaEnvio = new Date(fechaInicioCamp);

                    if (i > 0) {
                        if (modalidad === "dias_especificos") {
                            const dias = parseInt(prog.dias_despues || 0, 10);
                            fechaEnvio.setDate(fechaInicioCamp.getDate() + dias);
                        } else if (modalidad === "dias_semana") {
                            const diaSemanaProg = parseInt(prog.dia_semana ?? diaSemanaInicio, 10);
                            const diferenciaDias = (diaSemanaProg - diaSemanaInicio + 7) % 7;
                            fechaEnvio.setDate(fechaInicioCamp.getDate() + diferenciaDias);
                        }
                    }

                    // Agregar hora
                    const [hora, minuto, segundo] = (prog.hora_envio || "08:00:00").split(":");
                    fechaEnvio.setHours(+hora, +minuto, +segundo || 0);

                    // Si ya debería haberse enviado
                    if (fechaEnvio <= ahora) {
                        console.log(`🚀 Enviando plantilla ${prog.idplantilla} (campaña #${idcampania})`);

                        const formEnvio = new FormData();
                        formEnvio.append("idenvio", prog.idenvio);
                        formEnvio.append("nuevoEstado", "enviada");

                        await api.post({
                            source: "campanias",
                            action: "actualizarEstadoEnvio",
                            data: formEnvio,
                            onSuccess: () => console.log(`✅ Envío #${prog.idenvio} marcado como 'enviada'`),
                            onError: (err) => console.error(`❌ Error al actualizar envío #${prog.idenvio}`, err)
                        });
                    }
                }

                // Si todas las programaciones están enviadas → finalizar campaña
                await api.get({
                    source: "campanias",
                    action: "obtenerProgramacionesPendientes",
                    onSuccess: async (pendientes) => {
                        const quedanPendientes = pendientes.some(p => p.idcampania == idcampania);
                        if (!quedanPendientes) {
                            console.log(`🏁 Campaña #${idcampania} completada. Finalizando...`);

                            const formFin = new FormData();
                            formFin.append("idcampania", idcampania);

                            await api.post({
                                source: "campanias",
                                action: "finalizarCampania",
                                data: formFin,
                                onSuccess: () => console.log(`✅ Campaña #${idcampania} finalizada.`)
                            });
                        }
                    }
                });
            }
        },
        onError: (err) => {
            console.error("❌ Error al obtener programaciones:", err);
        }
    });
}

if (typeof process !== "undefined" && process.argv[1]?.includes("eventos.js")) {
    verificarEnvios();
}

export { verificarEnvios };