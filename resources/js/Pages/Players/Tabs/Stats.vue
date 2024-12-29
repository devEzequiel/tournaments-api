<template>
    <div class="stats-tab">
        <!-- Título estilizado -->
        <h3 class="stats-title">🏅 Player Statistics</h3>

        <table class="stats-table">
            <thead>
            <tr>
                <th></th> <!-- Coluna reservada para o emblema -->
                <th>Nome</th>
                <th @click="sortBy('team_name')" class="sortable">
                    Time
                    <span v-if="currentSort === 'team_name'" class="sort-icon">
              {{ sortOrder === 'asc' ? '▲' : '▼' }}
            </span>
                </th>
                <th @click="sortBy('matches')" class="sortable">
                    Matches
                    <span v-if="currentSort === 'matches'" class="sort-icon">
              {{ sortOrder === 'asc' ? '▲' : '▼' }}
            </span>
                </th>
                <th @click="sortBy('goals')" class="sortable">
                    Goals
                    <span v-if="currentSort === 'goals'" class="sort-icon">
              {{ sortOrder === 'asc' ? '▲' : '▼' }}
            </span>
                </th>
                <th @click="sortBy('assists')" class="sortable">
                    Assists
                    <span v-if="currentSort === 'assists'" class="sort-icon">
              {{ sortOrder === 'asc' ? '▲' : '▼' }}
            </span>
                </th>
                <th @click="sortBy('average_rate')" class="sortable">
                    Rate
                    <span v-if="currentSort === 'average_rate'" class="sort-icon">
              {{ sortOrder === 'asc' ? '▲' : '▼' }}
            </span>
                </th>
                <th @click="sortBy('best_player_awards')" class="sortable">
                    Best Player
                    <span v-if="currentSort === 'best_player_awards'" class="sort-icon">
              {{ sortOrder === 'asc' ? '▲' : '▼' }}
            </span>
                </th>
                <th @click="sortBy('golden_boot_awards')" class="sortable">
                    Golden Boot
                    <span v-if="currentSort === 'golden_boot_awards'" class="sort-icon">
              {{ sortOrder === 'asc' ? '▲' : '▼' }}
            </span>
                </th>
                <th @click="sortBy('playmaker_awards')" class="sortable">
                    Playmaker
                    <span v-if="currentSort === 'playmaker_awards'" class="sort-icon">
              {{ sortOrder === 'asc' ? '▲' : '▼' }}
            </span>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="(player, index) in sortedPlayers"
                :key="player.id"
                :class="{ 'highlight-gold': index === 0 }"
            >
                <td>
                    <div
                        class="team-emblem"
                        :style="{
                background: `linear-gradient(45deg, ${player.first_color}, ${player.second_color})`
              }"
                    ></div>
                </td>
                <td>{{ player.name }}</td>
                <td>{{ player.team_name }}</td>
                <td>{{ player.matches }}</td>
                <td>{{ player.goals }}</td>
                <td>{{ player.assists }}</td>
                <td>
                    {{ player.average_rate !== null ? player.average_rate.toFixed(1) : "N/A" }}
                </td>
                <td>{{ player.best_player_awards }}</td>
                <td>{{ player.golden_boot_awards }}</td>
                <td>{{ player.playmaker_awards }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            players: [],
            currentSort: null,
            sortOrder: "asc",
        };
    },
    computed: {
        sortedPlayers() {
            if (!this.currentSort) {
                return this.players;
            }
            return [...this.players].sort((a, b) => {
                if (this.currentSort === "team_name") {
                    return this.sortOrder === "asc"
                        ? a[this.currentSort].localeCompare(b[this.currentSort])
                        : b[this.currentSort].localeCompare(a[this.currentSort]);
                }
                const sortValue =
                    this.sortOrder === "asc"
                        ? a[this.currentSort] - b[this.currentSort]
                        : b[this.currentSort] - a[this.currentSort];
                return isNaN(sortValue) ? 0 : sortValue;
            });
        },
    },
    methods: {
        async fetchPlayerStats() {
            try {
                const response = await axios.get("/api/players/analytic");
                if (response.data.message === "success") {
                    this.players = response.data.data;
                }
            } catch (error) {
                console.error("Erro ao buscar dados dos jogadores:", error);
            }
        },
        sortBy(column) {
            if (this.currentSort === column) {
                this.sortOrder = this.sortOrder === "asc" ? "desc" : "asc";
            } else {
                this.currentSort = column;
                this.sortOrder = "asc";
            }
        },
    },
    mounted() {
        this.fetchPlayerStats();
    },
};
</script>

<style scoped>
/* Estilo geral */
.stats-tab {
    margin: 1rem;
    padding: 1rem;
    border-radius: 8px;
    background: #f9f9f9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.stats-title {
    color: #6a1b9a;
    text-transform: uppercase;
    font-size: 1.5rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #6a1b9a;
    padding-bottom: 0.5rem;
}

/* Estilo superior da tabela */
.stats-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.stats-table thead th {
    background-color: #6a1b9a;
    color: white;
    text-transform: uppercase;
    padding: 0.8rem;
    font-weight: 600;
    border: 1px solid #ddd;
    cursor: pointer;
}

/* Highlight para o 1º lugar */
.highlight-gold td {
    background-color: #f5c542;
    font-weight: bold;
    color: #533f03;
    border-bottom: 2px solid #e0a500;
    text-transform: uppercase;
}

/* Ajustes para a setinha menor */
.sort-icon {
    font-size: 0.7rem;
    margin-left: 0.3rem;
    vertical-align: middle;
}

/* Estilo da tabela em geral */
.stats-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.stats-table tbody tr:nth-child(odd) {
    background-color: #f1f1f1;
}

.stats-table td,
.stats-table th {
    border: 1px solid #ddd;
    padding: 0.6rem;
    text-align: center;
}

/* Emblema */
.team-emblem {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-block;
    margin: auto;
}
</style>
