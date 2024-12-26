<template>
    <header class="header bg-white border-bottom py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Logo -->
            <div class="logo">
                <h1 class="mb-0" style="color: #6a1b9a;">🏆 DivanScore</h1>
            </div>

            <!-- Navegação -->
            <nav class="menu">
                <ul class="nav">
                    <li class="nav-item">
                        <InertiaLink href="/dashboard" class="nav-link text-dark">Dashboard</InertiaLink>
                    </li>
                    <li class="nav-item">
                        <InertiaLink href="/championships" class="nav-link text-dark">Championships</InertiaLink>
                    </li>
                    <li class="nav-item">
                        <InertiaLink href="/teams" class="nav-link text-dark">Teams</InertiaLink>
                    </li>
                    <li class="nav-item">
                        <InertiaLink href="/players" class="nav-link text-dark">Players</InertiaLink>
                    </li>
                    <li class="nav-item">
                        <InertiaLink href="/settings" class="nav-link text-dark">Settings</InertiaLink>
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
};
</script>

<style scoped>
.header {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.logo h1 {
    font-size: 1.5rem;
    font-weight: bold;
}
.nav-link {
    padding: 0.5rem 1rem;
    transition: color 0.3s;
}
.nav-link:hover {
    color: #6a1b9a;
}
</style>
