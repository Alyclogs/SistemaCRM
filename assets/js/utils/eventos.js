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
                programacionesCampania.sort((a, b) => a.idenvio - b.idenvio);

                for (let i = 0; i < programacionesCampania.length; i++) {
                    const prog = programacionesCampania[i];
                    let fechaEnvio = new Date(`${prog.fecha_envio}T${prog.hora_envio}`);

                    // Si ya debería haberse enviado
                    if (fechaEnvio <= ahora) {
                        console.log(`🚀 Enviando plantilla ${prog.idplantilla} (campaña #${idcampania})`);

                        const formEnvio = new FormData();
                        formEnvio.append("idenvio", prog.idenvio);
                        formEnvio.append("nuevoEstado", "enviada");

                        await api.post({
                            source: "campanias",
                            action: "enviarProgramacion",
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