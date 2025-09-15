<?php
$pagina = isset($_GET['action']) ? $_GET['action'] : 'read';
$pagina = basename($pagina);

// Definir ruta de vista
$ruta = "views/clientes/" . $pagina . ".php";

if (!file_exists($ruta)) {
    $ruta = "views/clientes/read.php";
    $pagina = "read";
}
?>

<?php include($ruta); ?>