<?php
$pagina = isset($_GET['action']) ? $_GET['action'] : 'read';
$pagina = basename($pagina);

// Definir ruta de vista
$ruta = "views/usuarios/" . $pagina . ".php";

if (!file_exists($ruta)) {
    $ruta = "views/usuarios/read.php";
    $pagina = "read";
}
?>

<?php include($ruta); ?>