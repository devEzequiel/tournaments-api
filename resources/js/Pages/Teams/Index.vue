<template>
    <DefaultLayout>
        <!-- Botão + Modal -->
        <AddTeamModal @reload="reloadPage" />

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
    methods: {
        reloadPage() {
            this.$inertia.reload(); // Recarrega os dados usando Inertia
        },
    },
};
</script>
