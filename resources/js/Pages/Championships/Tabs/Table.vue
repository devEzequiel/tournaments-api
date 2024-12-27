<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import ClashModal from "@/Components/ClashModal.vue";

const props = defineProps({
    championshipId: {
        type: Number,
        required: true,
    },
});

// Estados
const teams = ref([]);
const matrix = ref({});
const loading = ref(true);

// Estados para a Modal
const showModal = ref(false);
const selectedClash = ref([]);
const modalLoading = ref(false);
const team1Name = ref("");
const team2Name = ref("");

// Função para buscar dados
const fetchTableData = async () => {
    try {
        const response = await axios.get(`/api/championship/table-data/${props.championshipId}`);
        if (response.data.message === "success") {
            teams.value = Object.values(response.data.data.teams);
            matrix.value = response.data.data.matrix;
            loading.value = false;
        }
    } catch (error) {
        console.error("Erro ao buscar os dados da tabela cruzada: ", error);
    }
};

const fetchClashData = async (team1Id, team2Id, team1, team2) => {
    showModal.value = true;
    modalLoading.value = true;
    team1Name.value = team1;
    team2Name.value = team2;

    try {
        const response = await axios.post(`/api/championship/clashes`, {
            championship_id: parseInt(props.championshipId, 10),
            team1_id: parseInt(team1Id, 10),
            team2_id: parseInt(team2Id, 10),
        });

        if (response.data.message === "success") {
            selectedClash.value = response.data.data;
        }
    } catch (error) {
        console.error("Erro ao buscar os confrontos: ", error);
    } finally {
        modalLoading.value = false;
    }
};

// Obter ID do Time pelo Nome
const getTeamIdByName = (teamName) => {
    const index = teams.value.indexOf(teamName);
    return index !== -1 ? index + 1 : null; // IDs iniciam em 1
};

onMounted(() => {
    fetchTableData();
});
</script>

<template>
    <div class="table-matrix-container">
        <h2 class="table-title">Matriz de Confrontos</h2>

        <!-- Loading -->
        <div v-if="loading" class="loading">Carregando...</div>

        <!-- Tabela -->
        <div v-else>
            <table class="matrix-table">
                <thead>
                <tr>
                    <td>-</td>
                    <th v-for="team in teams" :key="team">{{ team }}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(results, teamRow) in matrix" :key="teamRow">
                    <th class="team-name">{{ teamRow }}</th>
                    <td
                        v-for="(score, teamCol) in results"
                        :key="teamCol"
                        :class="{ self: teamRow === teamCol, empty: score === null }"
                        @click="score !== null && fetchClashData(getTeamIdByName(teamRow), getTeamIdByName(teamCol), teamRow, teamCol)"
                        style="cursor: pointer"
                    >
                        {{ score === null ? "-" : score }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal Component -->
        <ClashModal
            :show="showModal"
            :loading="modalLoading"
            :title="`${team1Name} x ${team2Name}`"
            :matches="selectedClash"
            @close="showModal = false"
        />
    </div>
</template>

<style scoped>
/* Container principal */
.table-matrix-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

/* Header */
.table-title {
    text-align: center;
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 20px;
    color: #6a1b9a;
}

/* Table */
.matrix-table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.matrix-table th,
.matrix-table td {
    padding: 10px;
    text-align: center;
    font-size: 1rem;
    border: 1px solid #ddd;
}

.matrix-table th {
    background-color: #6a1b9a;
    color: #ffffff;
}

/* Modal */
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
