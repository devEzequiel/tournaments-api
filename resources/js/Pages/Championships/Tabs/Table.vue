<script>
import axios from "axios";
import ClashModal from "../../../Components/ClashModal.vue";

export default {
    components: {ClashModal},
    props: {
        championshipId: {
            type: Number,
            required: true,
        },
    },

    data() {
        return {
            teams: {}, // Armazena o objeto de times direto { id: "nome" }
            teamOrder: [], // Armazena a ordem dos nomes dos times
            matrix: {}, // Matriz dos confrontos
            loading: true, // Indicador de carregamento

            // Modal States
            showModal: false,
            selectedClash: [],
            modalLoading: false,
            team1Name: "",
            team2Name: "",
        };
    },

    methods: {
        async fetchTableData() {
            try {
                const response = await axios.get(`/api/championship/table-data/${this.championshipId}`);
                if (response.data.message === "success") {
                    // Atualiza os estados:
                    this.teams = response.data.data.teams;
                    this.teamOrder = Object.values(this.teams); // Mantém a ordem dos times
                    this.matrix = response.data.data.matrix;
                    this.loading = false;
                }
            } catch (error) {
                console.error("Erro ao buscar os dados da tabela cruzada:", error);
            }
        },

        async fetchClashData(team1Id, team2Id, team1, team2) {
            console.log('Abertura da Modal:', {team1Id, team2Id, team1, team2});
            this.showModal = true; // Ativar exibição da modal
            this.modalLoading = true;

            this.team1Name = team1;
            this.team2Name = team2;

            try {
                const response = await axios.post(`/api/championship/clashes`, {
                    championship_id: parseInt(this.championshipId, 10),
                    team1_id: parseInt(team1Id, 10),
                    team2_id: parseInt(team2Id, 10),
                });

                if (response.data.message === "success") {
                    let clashes = response.data.data;

                    // Se for um array, encapsule como objeto com a estrutura necessária
                    if (Array.isArray(clashes)) {
                        clashes = {results: clashes}; // Adiciona uma chave `results` dentro de `data`
                    }

                    this.selectedClash = clashes; // Atualiza o estado da modal
                } else {
                    console.error("Nenhum confronto encontrado ou erro nos dados.");
                }
            } catch (error) {
                console.error("Erro ao buscar os confrontos:", error);
            } finally {
                this.modalLoading = false;
            }
        },

        getTeamIdByName(teamName) {
            // Garante correspondência exata no objeto `teams`
            const id = Object.entries(this.teams).find(([key, value]) => value.trim() === teamName.trim());
            return id ? parseInt(id[0], 10) : null; // Retorna a chave (ID) como número
        }
    },

    mounted() {
        this.fetchTableData();
    },
};
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
                    <!-- Cabeçalhos dos times seguem a ordem da `teamOrder` -->
                    <th v-for="team in teamOrder" :key="team">
                        {{ team.substring(0, 3).toUpperCase() }}
                    </th>
                </tr>
                </thead>
                <tbody>
                <!-- Linhas seguem a ordem da `teamOrder` -->
                <tr v-for="teamRow in teamOrder" :key="teamRow">
                    <th class="team-name">
                        {{ teamRow.substring(0, 3).toUpperCase() }}
                    </th>
                    <td
                        v-for="teamCol in teamOrder"
                        :key="teamCol"
                        :class="{ self: teamRow === teamCol, empty: matrix[teamRow]?.[teamCol] === null }"
                        @click="() => {
                            const team1Id = getTeamIdByName(teamRow);
                            const team2Id = getTeamIdByName(teamCol);
                            if (matrix[teamRow]?.[teamCol] !== null && team1Id && team2Id) {
                                fetchClashData(team1Id, team2Id, teamRow, teamCol);
                            }
                        }"
                        style="cursor: pointer"
                    >
                        {{ matrix[teamRow]?.[teamCol] === null ? "-" : matrix[teamRow]?.[teamCol] }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal Component -->
        <ClashModal
            v-if="showModal"
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
    max-width: 100%;
    margin: 0 auto;
    padding: 10px 15px; /* Reduz o padding para telas menores */
}

/* Header */
.table-title {
    text-align: center;
    font-size: 1.5rem; /* Reduz o tamanho do título no mobile */
    font-weight: bold;
    margin-bottom: 15px; /* Margem menor no mobile */
    color: #6a1b9a;
}

/* Table */
.matrix-table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 8px;
    overflow-x: auto; /* Adiciona rolagem horizontal no mobile */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 0.9rem; /* Ajusta o tamanho da fonte para caber em telas pequenas */
}

/* Cabeçalhos e Celular */
.matrix-table th,
.matrix-table td {
    padding: 5px 8px; /* Padding menor no mobile */
    text-align: center;
    border: 1px solid #ddd;
    font-size: 0.85rem; /* Menor tamanho para mobile */
}

/* Header das Colunas */
.matrix-table th {
    background-color: #6a1b9a;
    color: #ffffff;
    font-size: 0.9rem; /* Menor tamanho em mobile */
}

/* Linhas "self" destacadas */
.matrix-table td.self {
    background-color: #f3e5f5;
    font-weight: bold;
}

/* Celular vazio */
.matrix-table td.empty {
    background-color: #f9f9f9;
    color: #bbb;
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
    width: 90%; /* Reduz o tamanho para telas menores */
    max-width: 400px; /* Limita o tamanho no desktop */
    padding: 15px; /* Padding menor no mobile */
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 1.2rem; /* Reduz o tamanho do cabeçalho */
    color: #6a1b9a;
}

.close-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    color: #666;
}

.clash-table {
    width: 100%;
    border-collapse: collapse;
}

.clash-table th,
.clash-table td {
    padding: 8px;
    text-align: center;
    border: 1px solid #ccc;
    font-size: 0.9rem;
}

.clash-table th {
    background-color: #6a1b9a;
    color: #fff;
}

/* Responsividade */
@media (max-width: 576px) {
    /* Tabela */
    .matrix-table {
        font-size: 0.8rem; /* Reduz o tamanho da tabela em mobile */
    }

    .matrix-table th,
    .matrix-table td {
        padding: 4px; /* Ajusta padding para telas muito pequenas */
    }

    /* Títulos */
    .table-title {
        font-size: 1.4rem; /* Menor título no mobile */
        margin-bottom: 10px;
    }

    /* Modal */
    .modal {
        padding: 10px; /* Padding ainda menor */
    }

    .modal-header h3 {
        font-size: 1rem; /* Ajusta o tamanho do título da modal */
    }
}
</style>
