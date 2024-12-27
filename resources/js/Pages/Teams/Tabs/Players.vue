<template>
    <div class="players-tab">
        <h3 class="tab-title">Player Statistics</h3>
        <table class="players-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Matches Played</th>
                <th>Goals</th>
                <th>Assists</th>
                <th>Avg Rate</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="player in team.players" :key="player.player_id">
                <td>{{ player.player_name }}</td>
                <td class="text-center">{{ player.matches_played }}</td>
                <td class="text-center">{{ player.total_goals }}</td>
                <td class="text-center">{{ player.total_assists }}</td>
                <td class="text-center">{{ player.avg_rate.toFixed(2) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
export default {
    props: {
        team: Object, // Recebe os dados do time vindo de List.vue
    },
};
</script>

<style scoped>
.players-tab {
    text-align: center;
}

.tab-title {
    font-size: 1.8rem;
    color: #6a1b9a;
    margin-bottom: 1rem;
}

.players-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0 auto;
}

.players-table th,
.players-table td {
    padding: 0.8rem;
    text-align: left;
    border: 1px solid #ddd;
}

.players-table th {
    background-color: #6a1b9a;
    color: white;
    font-weight: bold;
}

.players-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.players-table td.text-center {
    text-align: center;
}
</style>
