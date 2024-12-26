<template>
    <DefaultLayout>
        <!-- Título da Página -->
        <div class="text-center my-5">
            <h1 class="display-4" style="color: #6a1b9a;">🏆 Championships</h1>
            <p class="text-muted">Acompanhe todos os campeonatos facilmente.</p>
        </div>

        <!-- Lista de Campeonatos -->
        <div class="row">
            <div class="col-md-4" v-for="championship in championships" :key="championship.id">
                <Card :title="championship.name" :description="`Status: ${championship.status}`" />
            </div>
        </div>
    </DefaultLayout>
</template>

<script>
import DefaultLayout from '../Layouts/DefaultLayout.vue';
import Card from '../../Components/Card.vue';

export default {
    components: {
        DefaultLayout,
        Card,
    },
    props: {
        championships: Array, // Campeonatos passados do Laravel
    },
};
</script>
