<?php

require_once __DIR__ . '/config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/node_modules/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <title>Gestionale Padel</title>
</head>
<body>

    <!-- Hamburger mobile (visibile solo sotto 992px) -->
    <button class="hamburger-btn" id="hamburgerBtn" onclick="toggleSidebarMobile()" aria-label="Apri menu">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- Overlay scuro dietro la sidebar su mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebarMobile()"></div>

    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('collapsed');
    }

    function toggleSidebarMobile() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var hamburger = document.getElementById('hamburgerBtn');
    var isOpen = sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('visibile', isOpen);
    hamburger.classList.toggle('sidebar-aperta', isOpen);
    }

    function closeSidebarMobile() {
    document.getElementById('sidebar').classList.remove('mobile-open');
    document.getElementById('sidebarOverlay').classList.remove('visibile');
    document.getElementById('hamburgerBtn').classList.remove('sidebar-aperta');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.sidebar-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) closeSidebarMobile();
            });
        });
    });
    </script>
