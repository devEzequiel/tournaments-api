<template>
    <div class="matches-container">
        <h2 class="title">Resultados</h2>
        <div v-for="round in groupedMatches" :key="round.round" class="round-block">
            <h3 class="round-title">Rodada {{ round.round }}</h3>

            <div
                v-for="match in round.matches"
                :key="match.id"
                :class="['match-block', { 'match-played': match.played_at, 'match-unplayed': !match.played_at }]"
                @click="match.played_at && goToMatch(match.id)"
            >
                <div class="match-row">
                    <!-- Time da casa -->
                    <div class="team home">
                        <TeamLogo
                            :firstColor="match.home_team_first_color"
                            :secondColor="match.home_team_second_color"
                        />
                        <span class="team-name">{{ match.home_team_name }}</span>
                    </div>

                    <!-- Resultado ou VS -->
                    <div class="result">
                        <span v-if="match.played_at">{{ match.home_goals }} - {{ match.away_goals }}</span>
                        <span v-else>VS</span>
                    </div>

                    <!-- Time visitante -->
                    <div class="team away">
                        <span class="team-name text-end">{{ match.away_team_name }}</span>
                        <TeamLogo
                            :firstColor="match.away_team_first_color"
                            :secondColor="match.away_team_second_color"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import TeamLogo from "@/Components/TeamLogo.vue"; // Importa o componente do logo do time
import axios from "axios";

export default {
    components: {
        TeamLogo,
    },
    props: {
        championshipId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            matches: [], // Armazena os dados das partidas
        };
    },
    computed: {
        groupedMatches() {
            // Agrupa partidas por rodada
            const rounds = {};
            this.matches.forEach((match) => {
                if (!rounds[match.round_number]) {
                    rounds[match.round_number] = [];
                }
                rounds[match.round_number].push(match);
            });

            return Object.entries(rounds).map(([round, matches]) => ({
                round: parseInt(round, 10),
                matches,
            }));
        },
    },
    methods: {
        async fetchMatches() {
            try {
                const response = await axios.get(`/api/fixtures/${this.championshipId}/basic`);
                if (response.data && response.data.data) {
                    this.matches = response.data.data;
                }
            } catch (error) {
                console.error("Erro ao carregar os resultados das partidas:", error);
            }
        },
        goToMatch(id) {
            this.$router.push(`/fixtures/${id}`); // Navega para a página do fixture
        },
    },
    mounted() {
        this.fetchMatches(); // Carrega as partidas ao montar o componente
    },
};
</script>

<style scoped>
/* Container principal */
.matches-container {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 700px;
    margin: 0 auto; /* Centraliza o container */    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }}

/* Título */
.title {
    text-align: center;
    font-weight: bold;
    font-size: 2rem;
    margin-bottom: 20px;
}

/* Rodada */
.round-block {
    margin-bottom: 30px;
    padding: 20px;
    border-radius: 8px;
    background-color: white;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

/* Título da rodada */
.round-title {
    text-align: center;
    font-size: 1.4rem;
    font-weight: bold;
    margin-bottom: 15px;
    color: #6a1b9a;
}

/* Partida */
.match-block {
    margin-bottom: 10px;
    cursor: pointer;
    transition: transform 0.2s ease;
    border-radius: 8px;
}

/* Efeitos de hover para jogos jogados */
.match-played:hover {
    transform: scale(1.02);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Linha da partida */
.match-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    border-radius: 8px;
    background-color: #ffffff;
    border: 1px solid #e0e0e0;
}

/* Time */
.team {
    display: flex;
    align-items: center;
    flex-basis: 45%;
}

/* Time da casa */
.team.home {
    justify-content: flex-start; /* Mantém o time da casa no início */
}

/* Time visitante */
.team.away {
    justify-content: flex-end; /* Alinha o time visitante ao final */
}

/* Nome do time */
.team-name {
    font-size: 0.9rem;
    font-weight: bold;
    margin: 0 8px; /* Espaço entre nome e logo */
    white-space: nowrap; /* Previne quebra */
}

/* Resultado */
.result {
    font-size: 1rem;
    font-weight: bold;
    color: #444;
    flex-shrink: 0;
    text-align: center;
}

/* Cor e estilo para jogos jogados */
.match-played .match-row {
    background-color: #e8f5e9;
    cursor: pointer;
}

/* Cor e estilo para jogos não jogados */
.match-unplayed .match-row {
    background-color: #f0f0f0;
    color: #aaa;
    pointer-events: none;
}
</style>
