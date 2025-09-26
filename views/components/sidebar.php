<?php
$base_url = 'http://localhost/SistemaCRM/';
$current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

function isActive($url)
{
    global $current_url, $base_url;
    return (strpos($current_url, $url) !== false) ? 'active' : '';
}

$menuItems = [
    ["label" => "Inicio", "categoria" => "home", "url" => "index.php?p=home", "icon" => "assets/svg/house.svg"],
    ["label" => "Agenda", "categoria" => "agenda", "url" => "index.php?p=agenda/index", "icon" => "assets/svg/calendar.svg"],
    ["label" => "Clientes", "categoria" => "clientes", "url" => "index.php?p=clientes/index", "icon" => "assets/svg/people.svg"],
    ["label" => "Usuarios", "categoria" => "usuarios", "url" => "index.php?p=usuarios/index", "icon" => "assets/svg/profile-2user.svg"],
    ["label" => "Proyectos", "categoria" => "proyectos", "url" => "index.php?p=timeline", "icon" => "assets/svg/document-text-2.svg"],
    ["label" => "Tareas", "categoria" => "tareas", "url" => "index.php?p=tareas", "icon" => "assets/svg/task-square.svg"],
];

$footerItems = [
    ["label" => "Ajustes", "categoria" => "ajustes", "url" => "index.php?p=ajustes/index", "icon" => "assets/svg/setting-2.svg"]
];
?>

<div class="sidebar">
    <div class="sidebar-header">
        <a class="sidebar-item logo" href="index.php">
            <?php include('assets/svg/logo2.svg') ?>
        </a>
    </div>

    <div class="sidebar-content">
        <div class="sidebar-items">
            <?php foreach ($menuItems as $item): ?>
                <div class="sidebar-item <?= isActive($item['categoria']) ?>" data-url="<?= $item['url'] ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="<?= $item['label'] ?>">
                    <div class="sidebar-item-left"></div>
                    <div class="sidebar-item-content">
                        <div class="sidebar-item-content-main">
                            <div class="sidebar-item-content-divider"></div>
                            <div class="sidebar-link">
                                <?php include($item['icon']) ?>
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
                <div class="sidebar-item <?= isActive($item['categoria']) ?>" data-url="<?= $item['url'] ?>" data-bs-toggle="tooltip" data-bs-placement="right" title="<?= $item['label'] ?>">
                    <div class="sidebar-item-left"></div>
                    <div class="sidebar-item-content">
                        <div class="sidebar-item-content-main">
                            <div class="sidebar-item-content-divider"></div>
                            <div class="sidebar-link">
                                <?php include($item['icon']) ?>
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
</div>