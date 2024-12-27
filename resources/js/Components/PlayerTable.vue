<template>
    <div class="players-tab">
        <!-- Wrapper do scroll horizontal -->
        <div class="table-responsive">
            <table class="players-table">
                <thead>
                <tr>
                    <!-- Coluna fixa (nome) -->
                    <th>Name</th>
                    <th @click="sortBy('games_played')" :class="getSortClass('games_played')">Matches</th>
                    <th @click="sortBy('goals')" :class="getSortClass('goals')">Goals</th>
                    <th @click="sortBy('assists')" :class="getSortClass('assists')">Assists</th>
                    <th @click="sortBy('average_rate')" :class="getSortClass('average_rate')">Avg Rate</th>
                    <th @click="sortBy('best_player')" :class="getSortClass('best_player')">Best Player</th>
                    <th @click="sortBy('golden_boot')" :class="getSortClass('golden_boot')">Golden Boot</th>
                    <th @click="sortBy('playmaker')" :class="getSortClass('playmaker')">Playmaker</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(player, index) in sortedPlayers" :key="index">
                    <td class="text-center">{{ player.name }}</td>
                    <td class="text-center">{{ player.games_played }}</td>
                    <td class="text-center">{{ player.goals }}</td>
                    <td class="text-center">{{ player.assists }}</td>
                    <td class="text-center">{{ player.average_rate.toFixed(2) }}</td>
                    <td class="text-center">{{ player.best_player }}</td>
                    <td class="text-center">{{ player.golden_boot }}</td>
                    <td class="text-center">{{ player.playmaker }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        players: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            sortKey: null,
            sortOrder: 1, // Padrão: ascendente
        };
    },
    computed: {
        sortedPlayers() {
            const playersArray = Object.values(this.players);

            if (!this.sortKey) {
                return playersArray;
            }

            return playersArray.sort((a, b) => {
                if (a[this.sortKey] < b[this.sortKey]) {
                    return -1 * this.sortOrder;
                }
                if (a[this.sortKey] > b[this.sortKey]) {
                    return 1 * this.sortOrder;
                }
                return 0;
            });
        },
    },
    methods: {
        sortBy(key) {
            if (this.sortKey === key) {
                this.sortOrder = this.sortOrder * -1;
            } else {
                this.sortKey = key;
                this.sortOrder = 1;
            }
        },
        getSortClass(key) {
            return {
                active: this.sortKey === key,
                ascending: this.sortKey === key && this.sortOrder === 1,
                descending: this.sortKey === key && this.sortOrder === -1,
            };
        },
    },
};
</script>

<style scoped>
.players-tab {
    text-align: center;
    margin: 20px;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch; /* Scroll suave no mobile */
    padding: 10px;
    background-color: #fafafa; /* Fundo neutro */
    border-radius: 12px;
    border: 1px solid #ddd; /* Bordas leves */
}

/* Configuração da tabela */
.players-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0 auto;
    min-width: 800px; /* Evita que a tabela fique muito pequena no desktop */
    background-color: white;
    border-radius: 8px;
    box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.1); /* Leve sombra */
}

.players-table th,
.players-table td {
    text-align: center;
    border: 1px solid #ddd;
    padding: 12px 8px;
    transition: background-color 0.3s, color 0.3s;
}

/* Redefinição de cabeçalhos */
.players-table th {
    background-color: #6a1b9a;
    color: white;
    font-size: 1.2rem; /* Melhor visibilidade no desktop */
    font-weight: bold;
    border-top: 2px solid #6a1b9a;
    border-bottom: 2px solid #6a1b9a;
    user-select: none; /* Impede selecionar texto */
}

.players-table th:hover {
    background-color: #8148ac; /* Altera o tom ao passar o mouse */
    cursor: pointer; /* Indica itens interativos */
}

/* Texto não selecionável no geral */
.players-table th, .players-table td {
    -webkit-user-select: none;
    user-select: none;
}

.players-table tr:nth-child(even) {
    background-color: #f9f9f9; /* Alternância de cor de linha */
}

.players-table tr:hover {
    background-color: #ececec; /* Realce ao passar o mouse */
}

/* Coluna fixa */
.players-table .fixed-column {
    position: sticky;
    left: 0;
    background-color: #fff;
    z-index: 3;
    font-weight: bold;
}

/* Melhor responsividade no mobile */
@media (max-width: 768px) {
    .players-table {
        min-width: unset;
        font-size: 0.9rem; /* Maior para celular */
    }

    .players-table th, .players-table td {
        padding: 8px;
        font-size: 0.8rem; /* Melhor leitura em dispositivos menores */
    }
}
</style>
