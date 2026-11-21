<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Торговля — управление торговым домом</title>
    <link rel="stylesheet" href="/trade.css">
    <script src="/trade.js" defer></script>
</head>
<body>
<aside class="sidebar">
    <a class="brand" href="#overview"><span class="brand-icon">Т</span>торговля<span class="brand-dot">.</span></a>
    <p class="workspace">ТОРГОВЫЙ ДОМ <span>Учебный проект</span></p>
    <nav aria-label="Основная навигация">
        <a href="#overview" data-page="overview"><span>◫</span>Обзор</a>
        <p class="nav-label">УЧЁТ</p>
        <a href="#departments" data-page="departments"><span>▦</span>Отделы</a>
        <a href="#products" data-page="products"><span>◇</span>Товары</a>
        <a href="#sales" data-page="sales"><span>▤</span>Продажи</a>
        <p class="nav-label">АНАЛИТИКА</p>
        <a href="#reports" data-page="reports"><span>▥</span>Отчёты</a>
    </nav>
    <div class="sidebar-bottom"><span class="connection-dot"></span><span id="connection">Проверяем соединение…</span><small>Laravel · MySQL</small></div>
</aside>
<main>
    <header class="topbar"><span>Рабочее пространство <b>/</b> <span id="breadcrumb">Обзор</span></span><span class="local-label">Локальная версия <span class="avatar">ТД</span></span></header>
    <div class="content">
        <div class="heading"><div><p class="eyebrow">УПРАВЛЕНИЕ ТОРГОВЫМ ДОМОМ</p><h1 id="title">Обзор торговли</h1><p id="subtitle" class="muted">Продажи, отделы и выполнение плана в одном месте.</p></div><button id="create-button" class="primary" hidden>Добавить</button></div>
        <div id="notice" role="status" hidden></div>
        <section id="overview" class="page">
            <div class="overview-toolbar"><span class="section-label">Показатели за день</span><label class="date-label">Дата <input type="date" id="overview-date" required></label></div>
            <div class="metrics">
                <article><span>Выручка за день</span><strong id="metric-revenue">—</strong><small>По ценам на момент продажи</small></article>
                <article><span>Выполнение плана</span><strong id="metric-plan">—</strong><small id="metric-plan-note">Общий план всех отделов</small></article>
                <article><span>Отделы</span><strong id="metric-departments">—</strong><small>В торговом доме</small></article>
                <article><span>Товары</span><strong id="metric-products">—</strong><small>В каталоге</small></article>
            </div>
            <div class="overview-grid"><article class="panel"><div class="panel-heading"><div><h2>План и факт</h2><p class="muted">Выручка по отделам за выбранный день</p></div><span class="badge">В рублях</span></div><div id="department-bars"></div></article>
            <article class="panel quickstart"><span class="mini-icon">▦</span><h2>Всё начинается с товара</h2><p>Добавьте товары в каталог, создайте отдел с первой продажей и ведите ежедневный учёт.</p><a href="#products" class="secondary">Открыть каталог</a><div class="hint"><b>История цен сохраняется</b><br>Изменение цены в каталоге не меняет выручку прошлых продаж.</div></article></div>
            <article class="panel"><div class="panel-heading"><div><h2>Последние продажи</h2><p class="muted">Пять последних добавленных записей</p></div><a href="#sales" class="text-link">Все продажи</a></div><div id="recent-sales" class="table-wrap"></div></article>
        </section>
        <section id="records" class="page" hidden>
            <article class="panel"><div class="list-toolbar"><label class="search"><span>⌕</span><input id="search" type="search" placeholder="Поиск по записям" aria-label="Поиск по записям"></label><span id="record-count" class="muted"></span></div><div id="record-table" class="table-wrap"></div></article>
            <p id="record-hint" class="footnote"></p>
        </section>
        <section id="reports" class="page" hidden>
            <article class="panel"><div class="panel-heading"><div><h2>Выполнение дневного плана</h2><p class="muted">Выручка, отклонение и процент выполнения для каждого отдела</p></div><label class="date-label">Дата <input id="report-date" type="date" required></label></div><div id="daily-report" class="table-wrap"></div></article>
            <article class="panel"><div class="panel-heading"><div><h2>Спрос на товары</h2><p class="muted">По числу продаж. Товары без продаж тоже включены.</p></div><form id="demand-form" class="date-range"><label>С <input id="report-from" type="date" required></label><label>По <input id="report-to" type="date" required></label><button class="secondary">Показать</button></form></div><div id="demand-report" class="table-wrap"></div></article>
        </section>
        <footer>Торговля <span>Учёт продаж торгового дома</span></footer>
    </div>
</main>
<dialog id="editor"><form id="editor-form"><div class="dialog-heading"><div><p class="eyebrow">УЧЁТ ДАННЫХ</p><h2 id="editor-title">Новая запись</h2></div><button type="button" id="close-editor" class="icon-button" aria-label="Закрыть">×</button></div><div id="form-fields" class="form-grid"></div><div id="form-errors" role="alert" hidden></div><div class="dialog-actions"><button type="button" id="cancel-editor" class="secondary">Отмена</button><button id="save-button" class="primary">Сохранить</button></div></form></dialog>
<dialog id="delete-dialog"><form method="dialog"><h2>Удалить запись?</h2><p class="muted">Удаление возможно только при соблюдении правил связей. Последняя продажа отдела защищена.</p><div class="dialog-actions"><button value="cancel" class="secondary">Отмена</button><button value="delete" class="danger">Удалить</button></div></form></dialog>
</body></html>
