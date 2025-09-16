<?php
$base_url = 'http://localhost/SistemaCRM/';
$current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

function isActive($url)
{
    global $current_url, $base_url;
    return (strpos($current_url, $url) !== false) ? 'active' : '';
}

$menuItems = [
    ["label" => "Inicio", "url" => "index.php?p=home", "icon" => "assets/svg/house.svg"],
    ["label" => "Agenda", "url" => "index.php?p=agenda", "icon" => "assets/svg/calendar.svg"],
    ["label" => "Clientes", "url" => "index.php?p=clientes/index", "icon" => "assets/svg/people.svg"],
    ["label" => "Usuarios", "url" => "index.php?p=usuarios", "icon" => "assets/svg/profile-2user.svg"],
    ["label" => "Proyectos", "url" => "index.php?p=timeline", "icon" => "assets/svg/document-text-2.svg"],
    ["label" => "Tareas", "url" => "index.php?p=tareas", "icon" => "assets/svg/task-square.svg"]
];

$footerItems = [
    ["label" => "Ajustes", "url" => "index.php?p=ajustes", "icon" => "assets/svg/setting-2.svg"]
];
?>

<div class="sidebar">
    <div class="sidebar-header">
        <a class="sidebar-item logo" href="index.php">
            <?php include('assets/svg/logo.svg') ?>
        </a>
    </div>

    <div class="sidebar-content">
        <div class="sidebar-items">
            <?php foreach ($menuItems as $item): ?>
                <div class="sidebar-item <?= isActive($item['url']) ?>" data-url="<?= $item['url'] ?>">
                    <div class="sidebar-item-left"></div>
                    <div class="sidebar-item-content">
                        <div class="sidebar-item-content-main">
                            <div class="sidebar-item-content-divider"></div>
                            <div class="sidebar-link">
                                <?php include($item['icon']) ?>
                                <a class="sidebar-link-text" href="<?= $item['url'] ?>">
                                    <?= $item['label'] ?>
                                </a>
                                <div class="sidebar-link-right"></div>
                            </div>
                            <div class="sidebar-item-content-divider"></div>
                        </div>
                        <div class="sidebar-content-corner">
                            <div class="sidebar-content-corner-top"></div>
                            <div class="sidebar-link-right"></div>
                            <div class="sidebar-content-corner-bottom"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-items">
            <?php foreach ($footerItems as $item): ?>
                <div class="sidebar-item <?= isActive($item['url']) ?>">
                    <div class="sidebar-item-left"></div>
                    <div class="sidebar-item-content">
                        <div class="sidebar-item-content-main">
                            <div class="sidebar-item-content-divider"></div>
                            <div class="sidebar-link">
                                <?php include($item['icon']) ?>
                                <a class="sidebar-link-text" href="<?= $item['url'] ?>">
                                    <?= $item['label'] ?>
                                </a>
                                <div class="sidebar-link-right"></div>
                            </div>
                            <div class="sidebar-item-content-divider"></div>
                        </div>
                        <div class="sidebar-content-corner">
                            <div class="sidebar-content-corner-top"></div>
                            <div class="sidebar-link-right"></div>
                            <div class="sidebar-content-corner-bottom"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="menu-button" style="padding-inline: 1rem;">
                <div class="sidebar-link">
                    <div class="user-icon sm" style="width: 26px; height: 26px">
                        <img src="<?= $_SESSION['foto'] ?>" alt="Foto de perfil">
                    </div>
                    <?php $nombreUsuario = explode(' ', $_SESSION['nombre'])[0] ?>
                    <span><?= $nombreUsuario ?></span>
                </div>
                <div class="menu-submenu" style="left: 8rem;">
                    <a class="submenu-item" href="index.php?p=perfil"><?php include('assets/svg/profile-circle.svg') ?>Mi perfil</a>
                    <a class="submenu-item" href="index.php?p=buzon"><?php include('assets/svg/sms.svg') ?>Mi buzón</a>
                    <a class="submenu-item text-danger" href="index.php?p=login&logout=true"><?php include('assets/svg/logout.svg') ?>Cerrar sesión</a>
                </div>
            </div>
        </div>
    </div>
</div>