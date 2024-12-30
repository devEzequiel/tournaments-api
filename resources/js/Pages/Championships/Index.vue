<template>
    <DefaultLayout>
        <!-- Toast -->
        <div v-if="toastMessage" class="alert alert-success" role="alert">
            {{ toastMessage }}
        </div>

        <!-- Botão + Modal para adicionar campeonato -->
        <AddModal
            :teams="teams"
            @toast="showToast"
            @reload="reloadPage"
        />

        <!-- Lista de Campeonatos -->
        <div class="row">
            <div
                v-if="championships.length === 0"
                class="text-center"
            >
                <p class="text-muted">Nenhum campeonato disponível no momento.</p>
            </div>
            <div
                class="col-md-4"
                v-for="championship in championships"
                :key="championship.id"
            >
                <Card
                    :title="championship.name"
                    :description="championship.description"
                    :rounds="championship.rounds"
                    :status="championship.finished_at ? 'Finalizado' : 'Em andamento'"
                    :playoffs="championship.playoffs"
                    :playoff_rounds="championship.playoff_rounds"
                    :started_at="championship.started_at"
                    :finished_at="championship.finished_at"
                    :link="`/championships/${championship.name.replace(/\s+/g, '-').toLowerCase()}`"
                />
            </div>
        </div>
    </DefaultLayout>
</template>

<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import AddModal from '@/Components/AddChampionshipModal.vue';
import Card from '@/Components/ChampionshipCard.vue';

export default {
    components: {
        DefaultLayout,
        AddModal,
        Card,
    },
    props: {
        championships: Array,
        teams: Array,
    },
    data() {
        return {
            toastMessage: '',
        };
    },
    methods: {
        async showToast(message) {
            this.toastMessage = message;

            // Remove o toast após 3 segundos
            setTimeout(() => {
                this.toastMessage = '';
            }, 3000);
        },
        async reloadPage() {
            this.$inertia.reload(); // Recarrega a página usando Inertia
        },
    },
};
</script>
