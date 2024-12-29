<template>
    <div class="matches-container">
        <h2 class="title">Partidas</h2>

        <div v-for="round in groupedMatches" :key="round.round" class="round-block">
            <h3 class="round-title">Rodada {{ round.round }}</h3>

            <div v-for="match in round.matches" :key="match.id" class="match-block" @click="openPlayMatchModal(match)">
                <div class="match-row">
                    <div class="team">
                        <TeamLogo
                            :firstColor="match.home_team_color"
                            :secondColor="match.home_team_second_color"
                        />
                        <span class="team-name">
                            {{ match.home_team_name.slice(0, 3).toUpperCase() }}
                        </span>
                    </div>
                    <span class="vs">VS</span>
                    <div class="team">
                        <TeamLogo
                            :firstColor="match.away_team_color"
                            :secondColor="match.away_team_second_color"
                        />
                        <span class="team-name">
                            {{ match.away_team_name.slice(0, 3).toUpperCase() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <PlayMatchModal
            v-if="selectedMatch"
            :match="selectedMatch"
            @close="closePlayMatchModal"
            @updated="handleMatchUpdated"
        />
    </div>
</template>

<script>
import TeamLogo from "@/Components/TeamLogo.vue";
import PlayMatchModal from "@/Components/PlayMatchModal.vue";
import axios from "axios";

export default {
    components: {
        TeamLogo,
        PlayMatchModal,
    },
    props: {
        matches: {
            type: Array,
            default: () => [], // Garante que "matches" inicie como array vazio
        },
        championshipId: {
            type: Number,
            required: true, // Exige ID do campeonato
        },
    },
    data() {
        return {
            selectedMatch: null, // Mantém o estado da partida selecionada
            localMatches: [...this.matches],
        };
    },
    computed: {
        groupedMatches() {
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
        async openPlayMatchModal(match) {
            this.selectedMatch = match;
        },
        async closePlayMatchModal() {
            this.selectedMatch = null;
        },
        async handleMatchUpdated() {
            try {
                const response = await axios.get(`/api/fixtures/${this.championshipId}/unplayed`);
                if (response.data && response.data.data) {
                    // Notifica o pai para buscar as partidas novamente
                    this.$emit("update-matches");
                }

                alert("Partidas atualizadas!");
            } catch (error) {
                console.error("Erro ao recarregar partidas:", error);
                alert("Erro ao carregar partidas após a atualização.");
            }
        },
    },
};
</script>

<style scoped>
/* Container Principal */
.matches-container {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 700px; /* Diminuir largura da div para PC */
    margin: 0 auto; /* Centralizar o container na tela */
}

/* Título */
.title {
    text-align: center; /* Centraliza a palavra Partidas */
    color: #333333;
    font-weight: bold;
    font-size: 2rem; /* Aumenta um pouco o tamanho da palavra */
    margin-bottom: 20px;
}

/* Rodada */
.round-block {
    margin-bottom: 30px;
    padding: 20px;
    border-radius: 8px;
    background-color: #ffffff;
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

/* Informações do Jogo */
.match-info {
    text-align: center;
    margin-bottom: 10px;
}

.game-number {
    font-size: 1rem;
    color: #555555;
    font-weight: 400;
}

/* Partida */
.match-block {
    margin-bottom: 10px;
    cursor: pointer;
}

/* Linha da Partida */
.match-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background-color: #f7f7f7;
    flex-wrap: nowrap; /* Garante que tudo esteja na mesma linha */
    overflow: hidden; /* Previne estouro lateral */
}

/* Time da Casa */
.team.home {
    display: flex;
    align-items: center;
    text-align: left; /* Alinhamento à esquerda */
    flex: 1; /* Permite ocupar o espaço esquerdo */
}

/* VS */
.vs {
    flex-shrink: 0; /* Mantém o tamanho do VS fixo */
    font-size: 1rem;
    font-weight: bold;
    color: #d40a0a;
    margin: 0 10px; /* Margem ao lado do VS */
    text-align: center;
}

/* Time Visitante */
.team.away {
    display: flex;
    align-items: center;
    text-align: right; /* Alinha nome à direita */
    justify-content: flex-end;
    flex: 1; /* Mantém na extrema direita */
}

/* Escudo do time */
.team-logo {
    width: 40px; /* Aumenta o tamanho do escudo no PC */
    height: 40px;
}

/* Nome do Time */
.team-name {
    font-size: 0.7rem;
    font-weight: bold;
    margin-left: 5px; /* Espaço entre escudo e nome */
    color: black;
    white-space: nowrap; /* Previne quebra de texto */
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Responsividade */
@media (max-width: 768px) {
    .matches-container {
        max-width: 100%; /* Ocupa toda a tela no celular */
    }

    .team-logo {
        width: 20px; /* Reduz tamanho do escudo no celular */
        height: 20px;
    }

    .team-name {
        font-size: 0.6rem; /* Fonte menor no celular */
        margin-left: 3px;
    }

    .vs {
        font-size: 0.8rem; /* Tamanho menor do VS */
        margin: 0 5px;
    }

    .match-row {
        padding: 8px 14px; /* Redução no padding interno */
        cursor: pointer;
    }
}
</style>
