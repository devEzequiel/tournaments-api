<template>
    <div class="stats-container">
        <!-- Título da seção -->
        <h2 class="stats-title">Estatísticas dos Jogadores</h2>

        <!-- Top 3 Awards (Acima da Tabela) -->
        <div class="awards-section">
            <div class="award" v-for="(award, awardKey) in awards" :key="awardKey">
                <h3 class="award-title">{{ awardTitles[awardKey] }}</h3>
                <ol class="award-list">
                    <li v-for="(player, index) in award" :key="player.id">
                        <!-- Ícones de Medalhas -->
                        <span class="medal">
                            <i :class="getMedalClass(index)"></i>
                        </span>
                        <strong>{{ index + 1 }}) {{ player.name }}</strong>
                        <span v-if="awardKey === 'best_player'"> – {{ parseFloat(player.avg_rate).toFixed(1) }} pts</span>
                        <span v-if="awardKey === 'golden_boot'"> – {{ player.total_goals }} gols</span>
                        <span v-if="awardKey === 'playmaker'"> – {{ player.total_assists }} assists</span>
                    </li>
                </ol>
            </div>
        </div>

        <!-- Tabela (Jogadores e Estatísticas) -->
        <table class="stats-table">
            <thead>
            <tr>
                <th>Jogador</th>
                <th @click="sortTable('matches_played')" class="sortable-header">
                    Partidas
                    <span v-if="sortField === 'matches_played'">{{ sortOrder === 'asc' ? '🔼' : '🔽' }}</span>
                </th>
                <th @click="sortTable('total_goals')" class="sortable-header">
                    Gols
                    <span v-if="sortField === 'total_goals'">{{ sortOrder === 'asc' ? '🔼' : '🔽' }}</span>
                </th>
                <th @click="sortTable('total_assists')" class="sortable-header">
                    Assistências
                    <span v-if="sortField === 'total_assists'">{{ sortOrder === 'asc' ? '🔼' : '🔽' }}</span>
                </th>
                <th @click="sortTable('avg_rate')" class="sortable-header">
                    Média de Nota
                    <span v-if="sortField === 'avg_rate'">{{ sortOrder === 'asc' ? '🔼' : '🔽' }}</span>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="player in sortedPlayers" :key="player.player_id">
                <td>{{ player.player_name }}</td>
                <td>{{ player.matches_played }}</td>
                <td>{{ player.total_goals }}</td>
                <td>{{ player.total_assists }}</td>
                <td>{{ parseFloat(player.avg_rate).toFixed(1) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: {
        championshipId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            players: [], // Lista completa de jogadores e estatísticas
            awards: {},  // Top 3 para cada prêmio
            sortField: null, // Campo pelo qual a tabela está sendo ordenada
            sortOrder: 'asc', // Ordem atual (ascendente ou descendente)
            awardTitles: {
                best_player: "The Best",
                golden_boot: "Golden Boot",
                playmaker: "Playmaker",
            },
        };
    },
    computed: {
        // Computed para ordenar os jogadores na tabela
        sortedPlayers() {
            if (!this.sortField) return this.players;

            return [...this.players].sort((a, b) => {
                const fieldA = a[this.sortField];
                const fieldB = b[this.sortField];

                if (this.sortOrder === 'asc') {
                    return fieldA < fieldB ? -1 : fieldA > fieldB ? 1 : 0;
                } else {
                    return fieldA > fieldB ? -1 : fieldA < fieldB ? 1 : 0;
                }
            });
        },
    },
    async created() {
        await this.fetchStats();
    },
    methods: {
        async fetchStats() {
            try {
                const response = await axios.get(`/api/championship/players-stats/${this.championshipId}`);
                if (response.data && response.data.data) {
                    this.players = response.data.data.players;
                    this.awards = response.data.data.awards;
                }
            } catch (error) {
                console.error("Erro ao buscar estatísticas:", error);
                alert("Erro ao carregar estatísticas do campeonato.");
            }
        },
        sortTable(field) {
            if (this.sortField === field) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortOrder = 'asc';
            }
        },
        getMedalClass(index) {
            switch (index) {
                case 0:
                    return "fa-solid fa-medal gold";
                case 1:
                    return "fa-solid fa-medal silver";
                case 2:
                    return "fa-solid fa-medal bronze";
                default:
                    return "";
            }
        },
    },
};
</script>

<style scoped>
.stats-container {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    max-width: 1000px;
    margin: 0 auto;
}

.stats-title {
    font-size: 2rem;
    text-align: center;
    color: #6a1b9a;
    margin-bottom: 30px;
}

/* Awards Section */
.awards-section {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
}

.award {
    background-color: #ffffff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    padding: 15px;
    width: 30%;
    min-width: 250px;
    text-align: center;
}

.award-title {
    font-size: 1.5rem;
    color: #502c71;
    margin-bottom: 10px;
    font-weight: bold;
}

.award-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.award-list li {
    font-size: 1rem;
    color: #333;
    margin: 5px 0;
}

.medal {
    font-size: 1.2rem;
    margin-right: 8px;
}

.gold {
    color: gold;
}

.silver {
    color: silver;
}

.bronze {
    color: #cd7f32;
}

/* Stats Table */
.stats-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

.stats-table thead {
    background-color: #6a1b9a;
    color: white;
}

.stats-table thead th {
    text-align: left;
    padding: 10px 15px;
    font-size: 1rem;
    cursor: pointer;
    user-select: none;
}

.stats-table tbody tr {
    border-bottom: 1px solid #ddd;
}

.stats-table tbody tr:hover {
    background-color: #f2f2f2;
}

.stats-table tbody td {
    padding: 10px 15px;
    text-align: left;
    color: #333;
}

.stats-table tbody td:first-child {
    font-weight: bold;
}

/* Responsividade */
@media (max-width: 768px) {
    .awards-section {
        flex-direction: column;
        align-items: center;
    }

    .award {
        width: 100%;
    }

    .stats-table thead {
        font-size: 0.8rem;
    }

    .stats-table tbody td {
        font-size: 0.9rem;
        padding: 5px 10px;
    }
}
</style>
