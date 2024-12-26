<template>
    <DefaultLayout>
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Times</h1>
            <button class="btn btn-primary" @click="showAddModal = true">Adicionar Time</button>
        </div>

        <!-- Modal de Adicionar Time -->
        <AddTeamModal
            v-if="showAddModal"
            @close="showAddModal = false"
            @reload="reloadPage"
        />

        <!-- Feedback (Toast) -->
        <div v-if="toastMessage" class="alert alert-success" role="alert">
            {{ toastMessage }}
        </div>

        <!-- Lista de Times -->
        <div class="row g-4">
            <div
                class="col-12 col-md-6 col-lg-4"
                v-for="team in teams"
                :key="team.id"
            >
                <TeamCard
                    :name="team.name"
                    :firstColor="team.first_color"
                    :secondColor="team.second_color"
                    :teamId="team.id"
                    @team-updated="handleTeamUpdated"
                />
            </div>
        </div>
    </DefaultLayout>
</template>

<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import TeamCard from '@/Components/TeamCard.vue';
import AddTeamModal from '@/Components/AddTeamModal.vue';

export default {
    components: {
        DefaultLayout,
        TeamCard,
        AddTeamModal,
    },
    props: {
        teams: Array, // Lista de times enviada do servidor
    },
    data() {
        return {
            showAddModal: false, // Controle da exibição da modal "Adicionar Time"
            toastMessage: '', // Mensagem de feedback/Toast
        };
    },
    methods: {
        // Exibe uma mensagem de sucesso no Toast
        showToast(message) {
            this.toastMessage = message;

            // Remove o Toast após 3 segundos
            setTimeout(() => {
                this.toastMessage = '';
            }, 3000);
        },

        // Recarrega a listagem de times (usando Inertia.js)
        reloadPage() {
            this.$inertia.reload();
        },

        // Atualiza o time localmente após edição
        handleTeamUpdated(updatedTeam) {
            const index = this.teams.findIndex((team) => team.id === updatedTeam.id);
            if (index !== -1) {
                this.teams.splice(index, 1, updatedTeam); // Substitui o time atualizado localmente
                this.showToast('Time atualizado com sucesso!');
            }
        },
    },
};
</script>

<style scoped>
/* Botão de Adicionar Time */
.btn-primary {
    font-size: 1rem;
    padding: 10px 20px;
}

h1 {
    font-size: 1.5rem;
    font-weight: bold;
}
</style>
