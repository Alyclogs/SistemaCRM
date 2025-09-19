<style>
    .container {
        width: 100%;
        margin: 0 auto;
        background: white;
        border-radius: 8px;
        padding: 1rem;
    }

    .gantt-container {
        display: flex;
        border: 2px solid #333;
    }

    .project-table {
        min-width: 400px;
        background: white;
        border-right: 2px solid #333;
    }

    .gantt-chart {
        flex: 1;
        overflow-x: auto;
        background: white;
    }

    .table-header {
        display: flex;
        background: #f8f9fa;
        border-bottom: 2px solid #333;
        font-weight: bold;
    }

    .header-cell {
        padding: 12px 8px;
        border-right: 1px solid #ddd;
        font-size: 12px;
        text-align: center;
    }

    .name-col {
        width: 180px;
    }

    .state-col {
        width: 90px;
    }

    .start-date-col {
        width: 95px;
    }

    .end-date-col {
        width: 95px;
    }

    .priority-col {
        width: 70px;
    }

    .collaborators-col {
        width: 100px;
    }

    .gantt-header {
        display: flex;
        background: #f8f9fa;
        border-bottom: 2px solid #333;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .month-header {
        min-width: 300px;
        padding: 12px;
        text-align: center;
        font-weight: bold;
        border-right: 1px solid #ddd;
    }

    .days-header {
        display: flex;
        border-top: 1px solid #ddd;
    }

    .day-cell {
        width: 30px;
        padding: 8px 4px;
        text-align: center;
        font-size: 11px;
        border-right: 1px solid #eee;
    }

    .project-row {
        display: flex;
        border-bottom: 1px solid #333;
        background: #f8f9fa;
        font-weight: bold;
    }

    .task-row {
        display: flex;
        border-bottom: 1px solid #ddd;
    }

    .cell {
        padding: 10px 8px;
        border-right: 1px solid #ddd;
        font-size: 12px;
        display: flex;
        align-items: center;
    }

    .gantt-row {
        display: flex;
        height: 45px;
        border-bottom: 1px solid #ddd;
        align-items: center;
        position: relative;
    }

    .gantt-bar {
        height: 20px;
        background: linear-gradient(135deg, #4CAF50, #45a049);
        border-radius: 4px;
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 10px;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .gantt-bar:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }

    .priority-alta {
        color: #dc3545;
        font-weight: bold;
    }

    .priority-media {
        color: #ffc107;
        font-weight: bold;
    }

    .priority-baja {
        color: #28a745;
        font-weight: bold;
    }

    .estado-progreso {
        color: #007bff;
    }

    .estado-completado {
        color: #28a745;
    }

    .estado-pendiente {
        color: #6c757d;
    }

    .expand-btn {
        background: none;
        border: none;
        cursor: pointer;
        margin-right: 8px;
        font-weight: bold;
        transition: transform 0.2s;
    }

    .expand-btn.expanded {
        transform: rotate(90deg);
    }

    .collaborators {
        display: flex;
        gap: 2px;
    }

    .avatar {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 10px;
    }

    .controls {
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 2px solid #333;
    }

    .month-nav {
        display: flex;
        align-items: center;
        gap: 15px;
        justify-content: center;
    }

    .nav-btn {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .nav-btn:hover {
        background: #0056b3;
    }

    .current-month {
        font-size: 18px;
        font-weight: bold;
        min-width: 150px;
        text-align: center;
    }

    .today-line {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dc3545;
        z-index: 5;
        pointer-events: none;
    }
</style>

<div class="page-content">
    <div class="container">
        <div class="controls">
            <div class="month-nav">
                <button class="nav-btn" onclick="previousMonth()">◀ Anterior</button>
                <div class="current-month" id="currentMonth"></div>
                <button class="nav-btn" onclick="nextMonth()">Siguiente ▶</button>
            </div>
        </div>

        <div class="gantt-container">
            <div class="project-table">
                <div class="table-header">
                    <div class="header-cell name-col">Nombre</div>
                    <div class="header-cell state-col">Estado</div>
                    <div class="header-cell start-date-col">Fecha inicio</div>
                    <div class="header-cell end-date-col">Fecha Fin</div>
                    <div class="header-cell priority-col">Prioridad</div>
                    <div class="header-cell collaborators-col">Colaboradores</div>
                </div>
                <div id="projectTableBody"></div>
            </div>

            <div class="gantt-chart">
                <div class="gantt-header">
                    <div class="month-header" id="ganttMonthHeader">
                        <div id="monthTitle"></div>
                        <div class="days-header" id="daysHeader"></div>
                    </div>
                </div>
                <div id="ganttBody"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Datos de ejemplo
    const projectsData = [{
            id: 1,
            name: "Sistema web",
            state: "En progreso",
            startDate: "2025-08-17",
            endDate: "2025-08-30",
            priority: "Alta",
            collaborators: ["A", "B", "C", "D", "E"],
            tasks: []
        },
        {
            id: 2,
            name: "Aplicación móvil",
            state: "En progreso",
            startDate: "2025-08-01",
            endDate: "2025-09-15",
            priority: "Media",
            collaborators: ["F", "G", "H"],
            tasks: [{
                    id: 21,
                    name: "Diseño UI",
                    state: "En progreso",
                    startDate: "2025-08-17",
                    endDate: "2025-08-25",
                    priority: "Alta",
                    collaborators: ["F", "G"]
                },
                {
                    id: 22,
                    name: "Módulo de usuarios",
                    state: "En progreso",
                    startDate: "2025-08-20",
                    endDate: "2025-09-05",
                    priority: "Media",
                    collaborators: ["H", "I"]
                }
            ]
        },
        {
            id: 3,
            name: "Dashboard Analytics",
            state: "Pendiente",
            startDate: "2025-09-01",
            endDate: "2025-09-20",
            priority: "Baja",
            collaborators: ["J", "K"],
            tasks: [{
                    id: 31,
                    name: "Configuración inicial",
                    state: "Pendiente",
                    startDate: "2025-09-01",
                    endDate: "2025-09-05",
                    priority: "Media",
                    collaborators: ["J"]
                },
                {
                    id: 32,
                    name: "Integración API",
                    state: "Pendiente",
                    startDate: "2025-09-06",
                    endDate: "2025-09-15",
                    priority: "Alta",
                    collaborators: ["K", "L"]
                }
            ]
        }
    ];

    let currentDate = new Date();
    let expandedProjects = new Set();

    function initializeApp() {
        updateCurrentMonth();
        renderProject();
    }

    function updateCurrentMonth() {
        const months = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        const monthText = `${months[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
        document.getElementById('currentMonth').textContent = monthText;
        document.getElementById('monthTitle').textContent = monthText;

        renderDaysHeader();
    }

    function renderDaysHeader() {
        const daysInMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate();
        const daysHeader = document.getElementById('daysHeader');
        daysHeader.innerHTML = '';

        for (let day = 1; day <= daysInMonth; day++) {
            const dayCell = document.createElement('div');
            dayCell.className = 'day-cell';
            dayCell.textContent = day;
            daysHeader.appendChild(dayCell);
        }
    }

    function renderProject() {
        const tableBody = document.getElementById('projectTableBody');
        const ganttBody = document.getElementById('ganttBody');

        tableBody.innerHTML = '';
        ganttBody.innerHTML = '';

        projectsData.forEach(project => {
            renderProjectRow(project, tableBody, ganttBody);

            if (expandedProjects.has(project.id)) {
                project.tasks.forEach(task => {
                    renderTaskRow(task, tableBody, ganttBody);
                });
            }
        });

        renderTodayLine();
    }

    function renderProjectRow(project, tableBody, ganttBody) {
        // Fila de la tabla
        const projectRow = document.createElement('div');
        projectRow.className = 'project-row';

        const hasTasksBtn = project.tasks.length > 0 ?
            `<button class="expand-btn ${expandedProjects.has(project.id) ? 'expanded' : ''}" onclick="toggleProject(${project.id})">▶</button>` : '';

        projectRow.innerHTML = `
                <div class="cell name-col">${hasTasksBtn}${project.name}</div>
                <div class="cell state-col estado-${project.state.toLowerCase().replace(' ', '-')}">${project.state}</div>
                <div class="cell start-date-col">${formatDate(project.startDate)}</div>
                <div class="cell end-date-col">${formatDate(project.endDate)}</div>
                <div class="cell priority-col priority-${project.priority.toLowerCase()}">${project.priority}</div>
                <div class="cell collaborators-col">${renderCollaborators(project.collaborators)}</div>
            `;

        tableBody.appendChild(projectRow);

        // Fila del gantt (vacía para proyectos)
        const ganttRow = document.createElement('div');
        ganttRow.className = 'gantt-row';
        ganttBody.appendChild(ganttRow);
    }

    function renderTaskRow(task, tableBody, ganttBody) {
        // Fila de la tabla
        const taskRow = document.createElement('div');
        taskRow.className = 'task-row';

        taskRow.innerHTML = `
                <div class="cell name-col">${task.name}</div>
                <div class="cell state-col estado-${task.state.toLowerCase().replace(' ', '-')}">${task.state}</div>
                <div class="cell start-date-col">${formatDate(task.startDate)}</div>
                <div class="cell end-date-col">${formatDate(task.endDate)}</div>
                <div class="cell priority-col priority-${task.priority.toLowerCase()}">${task.priority}</div>
                <div class="cell collaborators-col">${renderCollaborators(task.collaborators)}</div>
            `;

        tableBody.appendChild(taskRow);

        // Fila del gantt con barra
        const ganttRow = document.createElement('div');
        ganttRow.className = 'gantt-row';

        const ganttBar = createGanttBar(task);
        if (ganttBar) {
            ganttRow.appendChild(ganttBar);
        }

        ganttBody.appendChild(ganttRow);
    }

    function createGanttBar(item) {
        const startDate = new Date(item.startDate);
        const endDate = new Date(item.endDate);
        const currentMonth = currentDate.getMonth();
        const currentYear = currentDate.getFullYear();

        // Verificar si la tarea está en el mes actual
        if ((startDate.getFullYear() === currentYear && startDate.getMonth() === currentMonth) ||
            (endDate.getFullYear() === currentYear && endDate.getMonth() === currentMonth) ||
            (startDate < new Date(currentYear, currentMonth, 1) && endDate > new Date(currentYear, currentMonth + 1, 0))) {

            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const monthStart = new Date(currentYear, currentMonth, 1);
            const monthEnd = new Date(currentYear, currentMonth + 1, 0);

            const barStart = startDate < monthStart ? monthStart : startDate;
            const barEnd = endDate > monthEnd ? monthEnd : endDate;

            const startDay = barStart.getDate();
            const endDay = barEnd.getDate();

            const bar = document.createElement('div');
            bar.className = 'gantt-bar';
            bar.style.left = `${(startDay - 1) * 30}px`;
            bar.style.width = `${(endDay - startDay + 1) * 30}px`;
            bar.textContent = item.name;
            bar.title = `${item.name}\n${formatDate(item.startDate)} - ${formatDate(item.endDate)}`;

            return bar;
        }

        return null;
    }

    function renderTodayLine() {
        const today = new Date();
        if (today.getMonth() === currentDate.getMonth() && today.getFullYear() === currentDate.getFullYear()) {
            const todayDay = today.getDate();
            const ganttBody = document.getElementById('ganttBody');

            const todayLine = document.createElement('div');
            todayLine.className = 'today-line';
            todayLine.style.left = `${(todayDay - 1) * 30 + 15}px`;

            ganttBody.appendChild(todayLine);
        }
    }

    function renderCollaborators(collaborators) {
        return collaborators.map(collab =>
            `<div class="avatar">${collab}</div>`
        ).join('');
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
    }

    function toggleProject(projectId) {
        if (expandedProjects.has(projectId)) {
            expandedProjects.delete(projectId);
        } else {
            expandedProjects.add(projectId);
        }
        renderProject();
    }

    function previousMonth() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        updateCurrentMonth();
        renderProject();
    }

    function nextMonth() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        updateCurrentMonth();
        renderProject();
    }

    // Inicializar la aplicación
    $(document).ready(function() {
        initializeApp();
    });
</script>