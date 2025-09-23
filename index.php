<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina = isset($_GET['p']) ? $_GET['p'] : 'home';

if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol'])) {
    if ($pagina !== "login") {
        header("Location: index.php?p=login");
        exit;
    }
}

// Definir ruta de vista
$ruta = "views/" . $pagina . ".php";

if (!file_exists($ruta)) {
    $ruta = "views/home.php";
    $pagina = "home";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="./assets/css/general.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./assets/js/app.min.js"></script>
    <title>Sistema CRM</title>
</head>
<style>
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    textarea:-webkit-autofill,
    textarea:-webkit-autofill:hover,
    textarea:-webkit-autofill:focus,
    select:-webkit-autofill,
    select:-webkit-autofill:hover,
    select:-webkit-autofill:focus {
        background-color: transparent !important;
        color: var(--default-text-color) !important;
        font-family: 'Poppins', sans-serif !important;
    }
</style>

<body>
    <input type="hidden" id="idUsuario" value="<?= $_SESSION['idusuario'] ?? '' ?>">
    <input type="hidden" id="nombreUsuario" value="<?= $_SESSION['nombre'] ?? '' ?>">
    <?php if ($pagina !== "login"): ?>
        <div class="main-container">
            <?php include("views/components/sidebar.php"); ?>
            <div class="main-content">
                <?php include("views/components/header.php"); ?>
                <main class="page-container">
                    <?php include($ruta); ?>
                </main>
                <?php include("views/components/footer.php"); ?>
            </div>
        </div>
    <?php else: ?>
        <?php include($ruta); ?>
    <?php endif; ?>
</body>
<script>
    document.addEventListener('DOMContentLoaded', async function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        document.querySelectorAll('.menu-button').forEach(button => {
            button.addEventListener('click', function() {
                button.querySelector('.menu-submenu').style.display = 'flex';
            });
        });
    });
</script>

</html>