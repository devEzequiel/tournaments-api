<template>
    <div class="layout d-flex flex-column">
        <!-- Header -->
        <Header />

        <!-- Conteúdo principal -->
        <main class="flex-grow-1 container main-content">
            <slot />
        </main>

        <!-- Footer -->
        <Footer />

        <!-- Toast Notifications -->
        <ToastNotification ref="toast" />
    </div>
</template>

<script>
import Header from './Header.vue';
import Footer from './Footer.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

export default {
    components: {
        Header,
        Footer,
        ToastNotification,
    },
};
</script>

<style scoped>
/* Configuração do layout principal para deixar o footer fixo na parte inferior */
.layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh; /* Faz o layout ocupar toda a altura da página */
}

main {
    flex-grow: 1; /* Garante que o conteúdo principal ocupe o espaço restante */
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.main-content {
    padding: 3rem 1.5rem;
}

@media (max-width: 768px) {
    .main-content {
        padding: 2rem 1rem;
    }
}
</style>
