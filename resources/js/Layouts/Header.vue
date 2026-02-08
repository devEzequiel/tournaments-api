<template>
    <header class="header">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="logo">
                <h1 class="mb-0">DivanScore</h1>
            </div>

            <div class="header-actions">
                <button
                    class="theme-toggle"
                    type="button"
                    @click="toggleTheme"
                    :aria-label="isDark ? 'Ativar modo claro' : 'Ativar modo escuro'"
                >
                    {{ isDark ? 'Modo claro' : 'Modo escuro' }}
                </button>

                <button
                    class="menu-toggle d-md-none"
                    :class="{ 'is-open': isMenuOpen }"
                    @click="toggleMenu"
                    type="button"
                    aria-label="Abrir menu"
                >
                    <span class="menu-icon"></span>
                </button>
            </div>

            <nav class="menu d-md-flex" :class="{ 'menu-open': isMenuOpen }">
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
                    <li class="nav-item" v-if="$page.props.auth.user">
                        <InertiaLink 
                            href="/logout" 
                            method="post" 
                            as="button" 
                            class="nav-link logout-btn"
                        >
                            <i class="fas fa-sign-out-alt"></i> Sair
                        </InertiaLink>
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
            isDark: false,
        };
    },
    mounted() {
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = savedTheme || (prefersDark ? 'dark' : 'light');
        this.setTheme(theme);
    },
    methods: {
        toggleMenu() {
            this.isMenuOpen = !this.isMenuOpen;
        },
        toggleTheme() {
            this.setTheme(this.isDark ? 'light' : 'dark');
        },
        setTheme(theme) {
            this.isDark = theme === 'dark';
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        },
    },
};
</script>

<style scoped>
.header {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);
    position: sticky;
    top: 0;
    z-index: 50;
    padding: 0.75rem 0;
}

:global([data-theme="dark"]) .header {
    background: linear-gradient(135deg, #1e1b4b, #4c1d95);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.logo h1 {
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}

.menu-toggle {
    background: rgba(255, 255, 255, 0.12);
    border: none;
    color: #ffffff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    transition: background 0.2s ease;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.theme-toggle {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    cursor: pointer;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    font-size: 0.9rem;
    font-weight: 600;
    transition: background 0.2s ease, border-color 0.2s ease;
    white-space: nowrap;
}

.theme-toggle:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.4);
}

.menu-toggle:hover {
    background: rgba(255, 255, 255, 0.2);
}

.menu-icon {
    display: block;
    width: 24px;
    height: 3px;
    background-color: #ffffff;
    position: relative;
    transition: all 0.3s ease;
}

.menu-icon::before,
.menu-icon::after {
    content: "";
    display: block;
    width: 24px;
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

.menu-toggle.is-open .menu-icon {
    background-color: transparent;
}

.menu-toggle.is-open .menu-icon::before {
    transform: translateY(8px) rotate(45deg);
}

.menu-toggle.is-open .menu-icon::after {
    transform: translateY(-8px) rotate(-45deg);
}

.menu {
    display: none;
    flex-direction: column;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: rgba(15, 23, 42, 0.96);
    padding: 1rem 1.25rem;
    transition: all 0.3s ease;
    z-index: 1000;
    border-bottom-left-radius: 16px;
    border-bottom-right-radius: 16px;
    box-shadow: 0 16px 30px rgba(15, 23, 42, 0.3);
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

.nav-link {
    color: #ffffff;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    padding: 0.6rem 0.9rem;
    border-radius: 10px;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.nav-link:hover {
    background-color: rgba(255, 255, 255, 0.12);
    color: #fff;
}

@media (min-width: 768px) {
    .menu {
        display: flex;
        flex-direction: row;
        position: static;
        background: transparent;
        padding: 0;
        box-shadow: none;
    }

    .menu .nav-link {
        margin-left: 1rem;
        padding: 0.55rem 1rem;
    }

    .menu-toggle {
        display: none;
    }
}

@media (max-width: 768px) {
    .theme-toggle {
        font-size: 0.8rem;
        padding: 0.4rem 0.7rem;
    }
}

/* Botão de logout */
.logout-btn {
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid rgba(239, 68, 68, 0.4);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.logout-btn:hover {
    background: rgba(239, 68, 68, 0.4);
    border-color: rgba(239, 68, 68, 0.6);
}
</style>
