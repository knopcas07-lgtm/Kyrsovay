<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="header w-100">
    <div class="header-container d-flex justify-content-between align-items-center px-3">

        <!-- Логотип -->
        <div class="logo">
            <a href="index.php">Салон красоты</a>
        </div>

        <!-- Навигация -->
        <nav class="nav" id="nav-menu">
            <ul>
                <li><a href="masters.php" class="btn-outline">Мастера</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="admin_panel.php">Админка</a></li>
                    <?php else: ?>
                        <li><a href="profile.php">Профиль</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="btn-outline">Выйти</a></li>
                <?php else: ?>
                    <li><a href="login.php">Войти</a></li>
                    <li><a href="register.php" class="btn">Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Бургер -->
        <div class="burger" id="burger">
            <span></span>
            <span></span>
            <span></span>
        </div>

    </div>
</header>

<style>
/* Базовые стили header */
html, body {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

.header {
    background-color: #fff;
    padding: 10px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    position: sticky;
    top: 0;
    z-index: 999;
    width: 100%;
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.logo a {
    font-weight: 700;
    color: #007bff;
    text-decoration: none;
    font-size: 1.5rem;
}

.nav ul {
    display: flex;
    gap: 10px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav ul li a {
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 6px;
    transition: 0.3s;
    color: #333;
}

.nav ul li a.btn {
    background-color: #007bff;
    color: #fff;
}

.nav ul li a.btn-outline {
    border: 1px solid #007bff;
    color: #007bff;
}

.nav ul li a:hover {
    opacity: 0.8;
}

/* Бургер для мобильных */
.burger {
    display: none;
    flex-direction: column;
    cursor: pointer;
    gap: 4px;
}

.burger span {
    display: block;
    height: 3px;
    width: 25px;
    background-color: #333;
    border-radius: 2px;
}

/* Мобильная адаптация */
@media (max-width: 768px) {
    .nav {
        position: absolute;
        top: 60px;
        right: 0;
        background: #fff;
        width: 200px;
        transform: translateX(100%);
        transition: 0.3s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        border-radius: 0 0 8px 8px;
    }
    .nav ul {
        flex-direction: column;
        padding: 10px;
    }
    .nav.show {
        transform: translateX(0);
    }
    .burger {
        display: flex;
    }
}
</style>

<script>
// JS для бургер-меню
const burger = document.getElementById('burger');
const navMenu = document.getElementById('nav-menu');

burger.addEventListener('click', () => {
    navMenu.classList.toggle('show');
});
</script>