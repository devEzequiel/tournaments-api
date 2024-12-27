<script setup>
import {ref, onMounted} from "vue";
import axios from "axios";

const standings = ref([]);

const props = defineProps({
    championshipId: {
        type: Number,
        required: true,
    },
});

const fetchStandings = async () => {
    try {
        const response = await axios.get(`/api/championship/standings/${props.championshipId}`);
        if (response.data.message === "success") {
            standings.value = response.data.data;
        }
    } catch (error) {
        console.error("Erro ao buscar os dados do campeonato:", error);
    }
};

onMounted(() => {
    fetchStandings();
});
</script>

<template>
    <div class="standings-container">
        <table class="standings-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Time</th>
                <th>J</th>
                <th>V</th>
                <th>E</th>
                <th>D</th>
                <th>SG</th>
                <th>GF</th>
                <th>GC</th>
                <th>P</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="team in standings" :key="team.team_id">
                <td>{{ team.position }}</td>
                <td>
                    <div class="team-info">
                        <div
                            class="team-logo"
                            :style="{
                  background: `linear-gradient(45deg, ${team.first_color}, ${team.second_color})`
                }"
                        ></div>
                        <span class="team-name">{{ team.name }}</span>
                    </div>
                </td>
                <td>{{ team.played }}</td>
                <td>{{ team.wins }}</td>
                <td>{{ team.draws }}</td>
                <td>{{ team.losses }}</td>
                <td>{{ team.goal_diff }}</td>
                <td>{{ team.goals_scored }}</td>
                <td>{{ team.goals_conceded }}</td>
                <td class="points">{{ team.points }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
/* Container Principal */
.standings-container {
    padding: 20px;
    background: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    max-width: 900px;
    margin: 0 auto;
}

/* Título */
.standings-title {
    text-align: center;
    font-size: 2rem; /* Aumenta o título */
    font-weight: bold;
    margin-bottom: 20px;
    color: #333;
}

/* Estilo da Tabela */
.standings-table {
    width: 100%;
    border-collapse: collapse;
    text-align: center;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.standings-table thead {
    background: #6a1b9a;
    color: #fff;
}

.standings-table thead th {
    padding: 10px 5px;
    font-size: 1rem; /* Fonte maior para cabeçalho */
    font-weight: bold;
}

.standings-table tbody td {
    padding: 12px;
    font-size: 0.9rem; /* Fonte levemente maior */
    color: #333;
    border-bottom: 1px solid #ddd;
}

.standings-table tbody tr:last-child td {
    border-bottom: none;
}

.standings-table tbody tr:nth-child(odd) {
    background: #f7f7f7;
}

.standings-table tbody tr:hover {
    background: #efefef;
}

.standings-table tbody td.points {
    font-weight: bold; /* Destaca a coluna PONTOS */
    color: #222;
}

/* Informações do Time */
.team-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.team-logo {
    width: 35px; /* Maior para enfatizar escudo no PC */
    height: 35px;
    border-radius: 50%;
}

.team-name {
    font-size: 0.9rem; /* Nome do time maior */
    font-weight: bold;
    color: #333;
    text-align: left;
}

/* Responsividade */
@media (max-width: 768px) {
    .standings-table thead th,
    .standings-table tbody td {
        font-size: 0.7rem;
        padding: 8px 5px;
    }

    .team-logo {
        width: 20px; /* Escudo menor em telas pequenas */
        height: 20px;
    }

    .team-name {
        font-size: 0.7rem; /* Fonte menor para o nome também */
    }
}

@media (min-width: 1024px) {
    .standings-title {
        font-size: 2.5rem; /* Título ainda maior em telas grandes */
    }

    .standings-table thead th {
        font-size: 1.2rem; /* Aumenta ainda mais a fonte do cabeçalho */
    }

    .standings-table tbody td {
        font-size: 1rem; /* Aumenta a fonte das células no PC */
        padding: 14px; /* Mais espaçamento em telas grandes */
    }

    .team-logo {
        width: 40px; /* Escudo maior em telas grandes */
        height: 40px;
    }

    .team-name {
        font-size: 1rem; /* Nome do time maior para telas grandes */
    }
}
</style>
