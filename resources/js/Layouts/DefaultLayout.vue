<template>
    <div class="layout">
        <!-- Header -->
        <Header />

        <!-- Conteúdo principal -->
        <main class="container my-5">
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
