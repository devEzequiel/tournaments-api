<template>
    <div v-if="show" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Confrontos: {{ title }}</h3>
                <button @click="$emit('close')" class="close-btn">X</button>
            </div>

            <div class="modal-body">
                <div v-if="loading" class="loading">Carregando confrontos...</div>

                <table v-else class="clash-table">
                    <thead>
                    <tr>
                        <th>Casa</th>
                        <th>Fora</th>
                        <th>Placar</th>
                        <th>Disputado</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-for="match in matches"
                        :key="`${match.home_team_id}-${match.away_team_id}-${match.is_played}`"
                    >
                        <td>{{ match.home_team }}</td>
                        <td>{{ match.away_team }}</td>
                        <td>{{ match.home_goals }} - {{ match.away_goals }}</td>
                        <td>{{ match.is_played === 1 ? "Sim" : "Não" }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    show: Boolean, // Controla a exibição da modal
    loading: Boolean, // Status de carregamento
    title: String, // Título dinâmico da modal (Times em confronto)
    matches: Array, // Dados dos confrontos
});
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal {
    background: #ffffff;
    width: 500px;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 1.5rem;
    color: #6a1b9a;
}

.close-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.2rem;
    color: #666;
}

.clash-table {
    width: 100%;
    border-collapse: collapse;
}

.clash-table th,
.clash-table td {
    padding: 10px;
    text-align: center;
    border: 1px solid #ccc;
}

.clash-table th {
    background-color: #6a1b9a;
    color: #fff;
}
</style>
