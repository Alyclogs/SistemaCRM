<link rel="stylesheet" href="./assets/css/calendar.css">
<link rel="stylesheet" href="./assets/css/actividad.css">
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js'></script>
<?php
$pagina = isset($_GET['action']) ? $_GET['action'] : 'read';
$pagina = basename($pagina);

// Definir ruta de vista
$ruta = "views/agenda/" . $pagina . ".php";

if (!file_exists($ruta)) {
    $ruta = "views/agenda/read.php";
    $pagina = "read";
}
?>

<?php include($ruta); ?>