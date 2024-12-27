<template>
    <DefaultLayout>
        <!-- Toast -->
        <div v-if="toastMessage" class="alert alert-success" role="alert">
            {{ toastMessage }}
        </div>

        <!-- Botão + Modal -->
        <AddTeamModal
            @toast="showToast"
            @reload="reloadPage"
        />

        <!-- Lista de Times -->
        <div class="row g-4">
            <div
                class="col-md-3"
                v-for="team in teams"
                :key="team.id"
            >
                <TeamCard
                    :teamId = "team.id"
                    :name="team.name"
                    :firstColor="team.first_color"
                    :secondColor="team.second_color"
                    :link="`/teams/${team.name.replace(/\s+/g, '-').toLowerCase()}`"
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
        AddTeamModal
    },
    props: {
        teams: Array
    },
    data() {
        return {
            toastMessage: '',
        };
    },
    methods: {
        showToast(message) {
            this.toastMessage = message;

            // Remove o toast após 3 segundos
            setTimeout(() => {
                this.toastMessage = '';
            }, 3000);
        },
        reloadPage() {
            this.$inertia.reload(); // Recarrega os dados usando Inertia
        },
    },
};
</script>
