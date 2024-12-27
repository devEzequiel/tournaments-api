<template>
    <header class="header bg-purple text-white px-4 py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Logo -->
            <div class="logo">
                <h1 class="mb-0">🏆 DivanScore</h1>
            </div>

            <!-- Botão de menu para dispositivos móveis -->
            <button
                class="menu-toggle d-md-none"
                @click="toggleMenu"
            >
                <span class="menu-icon"></span>
            </button>

            <!-- Navegação -->
            <nav
                class="menu d-md-flex"
                :class="{ 'menu-open': isMenuOpen }"
            >
                <ul class="nav flex-column flex-md-row">
                    <li class="nav-item">
                        <InertiaLink href="/championships" class="nav-link">Championships</InertiaLink>
                    </li>
                    <li class="nav-item">
                        <InertiaLink href="/teams" class="nav-link">Teams</InertiaLink>
                    </li>
                    <li class="nav-item">
                        <InertiaLink href="/players" class="nav-link">Players</InertiaLink>
                    </li>
                    <li class="nav-item">
                        <InertiaLink href="/settings" class="nav-link">Settings</InertiaLink>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
</template>

<script>
import { Link as InertiaLink } from '@inertiajs/vue3';

export default {
    components: {
        InertiaLink,
    },
    data() {
        return {
            isMenuOpen: false,
        };
    },
    methods: {
        toggleMenu() {
            this.isMenuOpen = !this.isMenuOpen;
        },
    },
};
</script>

<style scoped>
/* Estilo do Header */
.header {
    background-color: #6a1b9a;
    color: #ffffff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
}

.logo h1 {
    font-size: 1.8rem;
    font-weight: bold;
    margin: 0;
}

/* Botão para abrir o menu (visível apenas no mobile) */
.menu-toggle {
    background: none;
    border: none;
    color: #ffffff;
    font-size: 1.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.menu-icon {
    display: block;
    width: 25px;
    height: 3px;
    background-color: #ffffff;
    position: relative;
    transition: all 0.3s ease;
}

.menu-icon::before,
.menu-icon::after {
    content: "";
    display: block;
    width: 25px;
    height: 3px;
    background-color: #ffffff;
    position: absolute;
    transition: all 0.3s ease;
}

.menu-icon::before {
    top: -8px;
}

.menu-icon::after {
    top: 8px;
}

/* Menu padrão */
.menu {
    display: none;
    flex-direction: column;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background-color: #6a1b9a;
    padding: 1rem;
    transition: all 0.3s ease;
    z-index: 1000;
}

.menu-open {
    display: flex;
}

.menu .nav {
    list-style: none;
    margin: 0;
    padding: 0;
}

.menu .nav-item {
    margin-bottom: 0.5rem;
}

.menu .nav-item:last-child {
    margin-bottom: 0;
}

/* Links do menu */
.nav-link {
    color: #ffffff;
    font-size: 1.1rem;
    font-weight: bold;
    text-decoration: none;
    padding: 0.5rem;
    border-radius: 5px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.nav-link:hover {
    background-color: #8e44ad;
    color: #fff;
}

/* Visível no desktop */
@media (min-width: 768px) {
    .menu {
        display: flex;
        flex-direction: row;
        position: static;
        background: none;
        padding: 0;
    }

    .menu .nav-item {
        margin-bottom: 0;
    }

    .menu .nav-link {
        margin-left: 1rem;
        padding: 0.5rem 1rem;
    }

    .menu-toggle {
        display: none;
    }
}
</style>
