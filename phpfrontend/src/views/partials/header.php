<header class="app-header">
    <div class="header-left">
        <button class="icon-button d-lg-none" type="button" aria-label="Open navigation" x-on:click="mobileNavOpen = true">
            <?= ui_icon('menu') ?>
        </button>
        <div>
            <p class="page-eyebrow"><?= e($pageEyebrow) ?></p>
            <h1><?= e($pageTitle) ?></h1>
        </div>
    </div>

    <div class="header-actions">
        <button class="icon-button d-xl-none" type="button" title="Open global search" x-on:click="openGlobalSearch()">
            <?= ui_icon('search') ?>
        </button>
        <button class="top-search d-none d-xl-flex" type="button" x-on:click="openGlobalSearch()" title="Open global search">
            <?= ui_icon('search') ?>
            <span>Global search</span>
            <kbd>/</kbd>
        </button>
        <div class="notification-wrap" x-on:click.outside="notificationOpen = false">
            <button class="icon-button" type="button" title="Notifications" x-on:click="toggleNotifications()">
                <?= ui_icon('bell') ?>
                <span class="notification-badge" x-show="unreadNotifications" x-text="unreadNotifications"></span>
            </button>
            <div class="notification-panel" x-show="notificationOpen" x-cloak>
                <div class="surface-header mb-2">
                    <h2>Notifications</h2>
                    <span class="badge-soft violet" x-text="`${notifications.length} items`"></span>
                </div>
                <div class="list-stack">
                    <template x-for="item in notifications" :key="item.title + item.detail">
                        <button class="mini-row text-start" type="button" x-on:click="openResult(item)">
                            <span>
                                <strong x-text="item.title"></strong>
                                <span x-text="item.detail"></span>
                            </span>
                            <span class="badge-soft" :class="item.level" x-text="item.level"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
        <button class="user-chip" type="button" x-on:click="goToProfile()" title="Open account">
            <span class="avatar avatar-sm"><?= e($user['avatar'] ?? 'HR') ?></span>
            <span class="d-none d-sm-inline"><?= e($user['name'] ?? 'Demo User') ?></span>
        </button>
    </div>

    <div class="command-overlay" x-show="searchOpen" x-cloak x-on:click.self="closeGlobalSearch()" x-on:keydown.escape.window="closeGlobalSearch()">
        <section class="command-panel">
            <div class="command-search">
                <?= ui_icon('search') ?>
                <input type="search" x-ref="globalSearchInput" x-model="globalSearch" x-on:keydown.enter.prevent="openFirstResult()" placeholder="Search pages, employees, attendance..." aria-label="Global search">
            </div>
            <div class="list-stack mt-3">
                <template x-for="result in globalResults" :key="result.type + result.label + result.detail">
                    <button class="mini-row text-start" type="button" x-on:click="openResult(result)">
                        <span>
                            <strong x-text="result.label"></strong>
                            <span x-text="result.detail"></span>
                        </span>
                        <span class="badge-soft teal" x-text="result.type"></span>
                    </button>
                </template>
                <template x-if="!globalResults.length">
                    <div class="feedback-panel">No matching pages, employees, or attendance records.</div>
                </template>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-outline-light" type="button" x-on:click="closeGlobalSearch()">Close</button>
            </div>
        </section>
    </div>
</header>
