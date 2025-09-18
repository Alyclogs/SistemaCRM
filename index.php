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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="./assets/css/general.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./assets/js/app.min.js"></script>
    <title>Sistema CRM</title>
</head>

<body>
    <input type="hidden" id="idUsuario" value="<?= $_SESSION['idusuario'] ?? '' ?>">
    <?php if ($pagina !== "login"): ?>
        <div class="main-container">
            <?php include("views/components/sidebar.php"); ?>
            <div class="container-fluid flex-grow-1 p-0">
                <main class="page-container">
                    <div class="page-content">
                        <?php include($ruta); ?>
                    </div>
                </main>
                <?php include("views/components/footer.php"); ?>
            </div>
        </div>
    <?php else: ?>
        <?php include($ruta); ?>
    <?php endif; ?>
</body>

</html>