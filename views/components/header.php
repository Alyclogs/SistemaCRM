<div class="container-fluid w-100 p-3 row align-items-center">
    <div class="col d-flex justify-content-start">
        <div class="text-large" id="pageTitle"></div>
    </div>
    <div class="col d-flex justify-content-center">
        <div class="grupo-input buscador-global">
            <?php include('./assets/svg/search.svg') ?>
            <input type="text" class="form-control" id="InputBuscarGlobal" placeholder="Búsqueda en CAPEDU">
        </div>
    </div>
    <div class="col d-flex justify-content-end">
        <div class="d-flex gap-4 align-items-center">
            <div class="menu-button">
                <?php include('./assets/svg/notification-bing.svg') ?>
            </div>
            <div class="menu-button">
                <img src="<?= $_SESSION['foto'] ?>" alt="Foto de <?= $_SESSION['nombre'] ?>" class="user-icon sm">
                <div class="menu-submenu" style="top: 3rem; right: 0px;">
                    <a class="submenu-item" href="index.php?p=perfil"><?php include('assets/svg/profile-circle.svg') ?>Mi perfil</a>
                    <a class="submenu-item" href="index.php?p=buzon"><?php include('assets/svg/sms.svg') ?>Mi buzón</a>
                    <a class="submenu-item text-danger" href="index.php?p=login&logout=true"><?php include('assets/svg/logout.svg') ?>Cerrar sesión</a>
                </div>
            </div>
        </div>
    </div>
</div>