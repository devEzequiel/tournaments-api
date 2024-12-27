<template>
    <div class="layout d-flex flex-column">
        <!-- Header -->
        <Header />

        <!-- Conteúdo principal -->
        <main class="flex-grow-1 container my-5">
            <slot />
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>

<script>
import Header from './Header.vue';
import Footer from './Footer.vue';

export default {
    components: {
        Header,
        Footer,
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
</style>
