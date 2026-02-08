<template>
    <div class="matches-container">
        <h2 class="title">Partidas</h2>

        <!-- Playoff Bracket (Show first if exists) -->
        <PlayoffBracket 
            :matches="matches" 
            @match-click="openPlayMatchModal" 
        />

        <div v-for="round in groupedMatches" :key="round.round" class="round-block">
            <h3 class="round-title">
                {{ round.isPlayoff ? (round.stage === 'semifinal' ? 'Semifinais' : 'Final') : `Rodada ${round.round}` }}
            </h3>

            <div 
                v-for="match in round.matches" 
                :key="match.id" 
                class="match-block" 
                :class="{ 
                    'playoff-semifinal': match.is_playoff && match.playoff_stage === 'semifinal',
                    'playoff-final': match.is_playoff && match.playoff_stage === 'final'
                }"
                @click="openPlayMatchModal(match)"
            >
                <div class="playoff-badge" v-if="match.is_playoff">
                    {{ match.playoff_stage === 'semifinal' ? '🏆 Semifinal' : '👑 Final' }}
                    <span class="game-label">Jogo {{ match.playoff_game_number }}</span>
                </div>
                <div class="match-row">
                    <div class="team">
                        <TeamLogo
                            v-if="match.home_team_id"
                            :firstColor="match.home_team_color"
                            :secondColor="match.home_team_second_color"
                        />
                        <span class="team-name">
                            {{ match.home_team_name ? match.home_team_name.slice(0, 3).toUpperCase() : '' }}
                        </span>
                    </div>
                    <span class="vs">VS</span>
                    <div class="team">
                        <TeamLogo
                            v-if="match.away_team_id"
                            :firstColor="match.away_team_color"
                            :secondColor="match.away_team_second_color"
                        />
                        <span class="team-name">
                            {{ match.away_team_name ? match.away_team_name.slice(0, 3).toUpperCase() : '' }}
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
import PlayoffBracket from "@/Components/PlayoffBracket.vue";
import axios from "axios";
import { useToast } from "@/Composables/useToast";

export default {
    components: {
        TeamLogo,
        PlayMatchModal,
        PlayoffBracket,
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
        champion: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            selectedMatch: null, // Mantém o estado da partida selecionada
            localMatches: [...this.matches],
            toast: useToast(),
        };
    },
    computed: {
        groupedMatches() {
            const rounds = {};
            // Filtrar apenas partidas não jogadas para exibição na lista
            const matchesToShow = this.matches.filter(match => !match.is_played);
            
            matchesToShow.forEach((match) => {
                const key = match.is_playoff 
                    ? `playoff-${match.playoff_stage}` 
                    : `round-${match.round_number}`;
                    
                if (!rounds[key]) {
                    rounds[key] = {
                        round: match.round_number,
                        matches: [],
                        isPlayoff: match.is_playoff || false,
                        stage: match.playoff_stage || null
                    };
                }
                rounds[key].matches.push(match);
            });

            return Object.values(rounds).sort((a, b) => {
                if (!a.isPlayoff && !b.isPlayoff) return a.round - b.round;
                if (!a.isPlayoff) return -1;
                if (!b.isPlayoff) return 1;
                if (a.stage === 'semifinal' && b.stage === 'final') return -1;
                if (a.stage === 'final' && b.stage === 'semifinal') return 1;
                return 0;
            });
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
            } catch (error) {
                console.error("Erro ao recarregar partidas:", error);
                this.toast.error("Erro ao carregar partidas após a atualização.");
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
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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
    position: relative;
}

.playoff-badge {
    background: linear-gradient(135deg, #6a1b9a, #8e24aa);
    color: white;
    padding: 4px 12px;
    border-radius: 6px 6px 0 0;
    font-size: 0.8rem;
    font-weight: bold;
    text-align: center;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.playoff-final .playoff-badge {
    background: linear-gradient(135deg, #d4af37, #ffd700);
    color: #000;
}

.game-label {
    font-size: 0.7rem;
    opacity: 0.9;
}

.playoff-semifinal .match-row {
    border: 2px solid #6a1b9a;
    background: linear-gradient(to right, #f3e5f5, #ffffff);
    box-shadow: 0 4px 8px rgba(106, 27, 154, 0.2);
}

.playoff-final .match-row {
    border: 3px solid #d4af37;
    background: linear-gradient(to right, #fffbea, #ffffff);
    box-shadow: 0 6px 12px rgba(212, 175, 55, 0.3);
}

.team-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e0e0e0, #bdbdbd);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: bold;
    color: #666;
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
    
    .champion-banner {
        padding: 15px;
    }
    
    .champion-title {
        font-size: 1.2rem;
    }
    
    .champion-name {
        font-size: 1rem;
    }
}

/* Banner do Campeão */
.champion-banner {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
    border: 3px solid #ff6b00;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 8px 20px rgba(255, 215, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 30px;
    animation: championPulse 2s ease-in-out infinite;
}

@keyframes championPulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 8px 20px rgba(255, 215, 0, 0.5);
    }
    50% {
        transform: scale(1.02);
        box-shadow: 0 12px 30px rgba(255, 215, 0, 0.7);
    }
}

.champion-trophy {
    font-size: 4rem;
    animation: rotateTrophy 3s ease-in-out infinite;
}

@keyframes rotateTrophy {
    0%, 100% {
        transform: rotate(-5deg);
    }
    50% {
        transform: rotate(5deg);
    }
}

.champion-content {
    text-align: center;
}

.champion-title {
    font-size: 1.8rem;
    font-weight: 900;
    color: #8b4513;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    margin-bottom: 15px;
    letter-spacing: 3px;
}

.champion-team {
    display: flex;
    align-items: center;
    gap: 15px;
    justify-content: center;
}

.champion-logo {
    width: 60px;
    height: 60px;
}

.champion-name {
    font-size: 1.5rem;
    font-weight: bold;
    color: #4a2c0f;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

</style>
