<template>
    <div>
        <h3 class="tab-title">Player Statistics</h3>
        <!-- Reutilizando a tabela reutilizável -->
        <PlayerTable :players="team.players" />
    </div>
</template>

<script>
import PlayerTable from "@/Components/PlayerTable.vue";

export default {
    props: {
        team: Object, // Dados recebidos do time
    },
    components: {
        PlayerTable, // Importa o componente reutilizável
    },
};
</script>
