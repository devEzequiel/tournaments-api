<template>
    <div class="modal">
        <div class="modal-content">
            <h2>Jogar Partida</h2>

            <div>
                <div>
                    <label>{{ match.home_team_name }}:</label>
                    <select v-model="homeGoalCount" @change="updateHomeGoals">
                        <option v-for="n in 16" :key="'home-goals-' + n" :value="n-1">{{ n-1 }}</option>
                    </select>

                    <label>{{ match.away_team_name }}:</label>
                    <select v-model="awayGoalCount" @change="updateAwayGoals">
                        <option v-for="n in 16" :key="'away-goals-' + n" :value="n-1">{{ n-1 }}</option>
                    </select>
                </div>

                <!-- Exibir gols do time da casa -->
                <div v-for="(goal, index) in homeGoals" :key="'home-goal-' + index" class="goal-row">
                    <label class="goal-label">{{ match.home_team_name.substring(0, 3).toUpperCase() }} {{ index + 1 }}:</label>
                    <select v-model="goal.scorer_id" class="select-small">
                        <option disabled value="">Select</option>
                        <option v-for="player in homeTeamPlayers" :key="'home-scorer-' + player.player_id" :value="player.player_id">
                            {{ player.player_name }}
                        </option>
                    </select>

                    <label class="goal-label">Assist:</label>
                    <select v-model="goal.assist_id" class="select-small">
                        <option disabled value="">Select Assist</option>
                        <option v-for="player in homeTeamPlayers" :key="'home-assist-' + player.player_id" :value="player.player_id">
                            {{ player.player_name }}
                        </option>
                    </select>
                </div>

                <div v-for="(goal, index) in awayGoals" :key="'away-goal-' + index" class="goal-row">
                    <label class="goal-label">{{ match.away_team_name.substring(0, 3).toUpperCase() }}  {{ index + 1 }}:</label>
                    <select v-model="goal.scorer_id" class="select-small">
                        <option disabled value="">Select</option>
                        <option v-for="player in awayTeamPlayers" :key="'away-scorer-' + player.player_id" :value="player.player_id">
                            {{ player.player_name }}
                        </option>
                    </select>

                    <label class="goal-label">Assist:</label>
                    <select v-model="goal.assist_id" class="select-small">
                        <option disabled value="">Select Assist</option>
                        <option v-for="player in awayTeamPlayers" :key="'away-assist-' + player.player_id" :value="player.player_id">
                            {{ player.player_name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Notas dos jogadores -->
            <div class="players-container">
                <div class="team-column">
                    <h3>{{ match.home_team_name }}</h3>
                    <div v-for="player in homeTeamPlayers" :key="'home-player-' + player.player_id" class="rate-input">
                        <label>{{ player.player_name }}</label>
                        <div class="rate-controls">
                            <input type="number" v-model="playerRate[player.player_id]" step="0.5" min="0" max="10" />
                            <button @click.prevent="adjustRate(player.player_id, 0.5)">+</button>
                            <button @click.prevent="adjustRate(player.player_id, -0.5)">-</button>
                        </div>
                    </div>
                </div>

                <div class="team-column">
                    <h3>{{ match.away_team_name }}</h3>
                    <div v-for="player in awayTeamPlayers" :key="'away-player-' + player.player_id" class="rate-input">
                        <label>{{ player.player_name }}</label>
                        <div class="rate-controls">
                            <input type="number" v-model="playerRate[player.player_id]" step="0.5" min="0" max="10" />
                            <button @click.prevent="adjustRate(player.player_id, 0.5)">+</button>
                            <button @click.prevent="adjustRate(player.player_id, -0.5)">-</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="actions">
                <button @click="$emit('close')">Cancelar</button>
                <button @click="submitMatch">Salvar Partida</button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: {
        match: Object // Dados da partida
    },
    data() {
        return {
            homeTeamPlayers: [],
            awayTeamPlayers: [],
            homeGoals: [],
            awayGoals: [],
            homeGoalCount: 0,
            awayGoalCount: 0,
            playerRate: {}, // Notas dos jogadores com valor padrão de 6
        };
    },
    computed: {
        // Combinar todos os jogadores para exibir na seção de avaliações
        allPlayers() {
            return [...this.homeTeamPlayers, ...this.awayTeamPlayers];
        }
    },
    watch: {
        match: {
            immediate: true,
            async handler(newMatch) {
                if (newMatch) {
                    // Buscar jogadores dos times quando a partida for carregada
                    await this.fetchHomePlayers(newMatch.home_team_id);
                    await this.fetchAwayPlayers(newMatch.away_team_id);
                }
            }
        }
    },
    methods: {
        // Atualiza o array de gols baseado no número selecionado
        updateHomeGoals() {
            this.homeGoals = Array.from({ length: this.homeGoalCount }, () => ({
                scorer_id: null,
                assist_id: ""
            }));
        },
        updateAwayGoals() {
            this.awayGoals = Array.from({ length: this.awayGoalCount }, () => ({
                scorer_id: null,
                assist_id: ""
            }));
        },

        // Buscar jogadores do time da casa
        async fetchHomePlayers(teamId) {
            try {
                const { data } = await axios.get(`/api/team/${teamId}/current`);
                this.homeTeamPlayers = data.data || [];

                this.homeTeamPlayers.forEach(player => {
                    if (!this.playerRate[player.player_id]) {
                        this.playerRate[player.player_id] = 6;
                    }
                });
            } catch (error) {
                console.error("Erro ao carregar jogadores do time da casa:", error);
                this.homeTeamPlayers = [];
            }
        },

        // Buscar jogadores do time visitante
        async fetchAwayPlayers(teamId) {
            try {
                const { data } = await axios.get(`/api/team/${teamId}/current`);
                this.awayTeamPlayers = data.data || [];

                this.awayTeamPlayers.forEach(player => {
                    if (!this.playerRate[player.player_id]) {
                        this.playerRate[player.player_id] = 6;
                    }
                });
            } catch (error) {
                console.error("Erro ao carregar jogadores do time visitante:", error);
                this.awayTeamPlayers = [];
            }
        },

        // Ajustar notas dos jogadores
        adjustRate(playerId, value) {
            // Garante que o jogador sempre começa com a nota padrão
            if (!this.playerRate[playerId]) {
                this.playerRate[playerId] = 6;
            }
            // Ajusta a nota dentro do intervalo [0, 10]
            const newRate = Math.min(10, Math.max(0, this.playerRate[playerId] + value));
            this.playerRate[playerId] = newRate;
        },
        // Enviar dados ao backend
        async submitMatch() {
            const payload = {
                fixture_id: this.match.id,
                home_goals: this.homeGoalCount,
                away_goals: this.awayGoalCount,
                goals: [
                    ...this.homeGoals.map(goal => ({
                        scorer_id: goal.scorer_id,
                        assist_id: goal.assist_id
                    })),
                    ...this.awayGoals.map(goal => ({
                        scorer_id: goal.scorer_id,
                        assist_id: goal.assist_id
                    }))
                ],
                rates: Object.entries(this.playerRate).map(([id, rate]) => ({
                    player_id: Number(id),
                    rate
                }))
            };

            try {
                await axios.put(`/api/fixtures`, payload);
                alert("Partida salva com sucesso!");
                this.$emit("updated"); // Avisar o componente pai
                this.$emit("close"); // Fechar o modal
            } catch (error) {
                console.error("Erro ao salvar a partida:", error);
                alert("Erro ao tentar salvar a partida.");
            }
        }
    }
};
</script>

<style scoped>
/* Estrutura */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: #fff;
    padding: 20px 30px;
    border-radius: 10px;
    width: 90%;
    max-width: 700px;
    overflow-y: auto;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.3);
    font-family: Arial, sans-serif;
}

h2, h3 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

/* Inputs de gols */
.goal-input {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}

.goal-input label {
    flex: 1 1 100%; /* Ocupa toda a linha */
    font-weight: bold;
    color: #555;
}

.select-group {
    display: flex;
    flex-wrap: nowrap;
    gap: 10px;
    width: 100%;
}

select {
    flex: 1;
    padding: 6px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

select:focus {
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Responsividade para inputs de gols: uma linha no mobile */
@media (max-width: 768px) {
    .select-group {
        flex-direction: column; /* Queremos os selects empilhados no mobile */
    }
}

.players-container {
    display: grid;
    grid-template-columns: 1fr 1fr; /* Duas colunas */
    gap: 20px;
    margin-top: 20px;
}

.team-column {
    background: #f9f9f9; /* Fundo leve */
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    overflow-x: auto; /* Garante que o conteúdo não quebre */
}

.team-column h3 {
    text-align: center;
    color: #333;
    margin-bottom: 15px;
    font-size: 18px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 10px;
}

.rate-input {
    display: flex;
    justify-content: space-between; /* Alinha os elementos horizontalmente */
    align-items: center; /* Centraliza verticalmente */
    margin-bottom: 10px;
    background: white;
    padding: 10px;
    border-radius: 8px;
    box-shadow: inset 0 1px 5px rgba(0, 0, 0, 0.1);
}

.rate-input label {
    flex: 2; /* O nome do jogador ocupa mais espaço */
    font-size: 14px;
    color: #555;
    font-weight: bold;
}

.rate-controls {
    display: flex;
    align-items: center;
    gap: 5px; /* Espaço entre os comandos */
}

input[type="number"] {
    width: 50px;
    text-align: center;
    font-size: 14px;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.rate-controls button {
    background: #007bff;
    color: white;
    font-size: 14px;
    border: none;
    border-radius: 5px;
    padding: 5px 8px;
    cursor: pointer;
    transition: 0.3s;
}

.rate-controls button:hover {
    background: #0056b3;
}

/* Responsividade */
@media (max-width: 768px) {
    .players-container {
        grid-template-columns: repeat(2, 1fr); /* Mantém as duas colunas com tamanhos iguais */
        gap: 10px; /* Reduz o espaçamento entre as colunas */
    }

    .rate-input {
        flex-wrap: nowrap; /* Garante que os elementos não empilhem */
        gap: 5px; /* Mantém um pequeno espaçamento horizontal */
    }

    .team-column {
        padding: 10px; /* Ajusta o padding para telas menores */
    }

    .rate-input label {
        font-size: 12px; /* Reduz o tamanho da fonte dos nomes */
    }

    input[type="number"] {
        width: 40px; /* Reduz o tamanho do input */
        font-size: 12px; /* Ajusta o texto do input */
    }

    .rate-controls button {
        padding: 4px 6px; /* Reduz o tamanho dos botões */
        font-size: 12px; /* Reduz o tamanho da fonte */
    }
}

/* Botões da Modal */
.actions {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.actions button {
    flex: 1;
    margin: 5px;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: 0.3s;
}

.actions button:last-child {
    background-color: #28a745;
    color: white;
}

.actions button:last-child:hover {
    background-color: #218838;
}

.actions button:first-child {
    background-color: #dc3545;
    color: white;
}

.actions button:first-child:hover {
    background-color: #c82333;
}

.goal-row {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 15px; /* Espaço horizontal entre os elementos */
    margin-bottom: 10px; /* Espaço entre as linhas */
    flex-wrap: wrap; /* Permite que elementos se ajustem em telas menores */
}

.goal-label {
    font-size: 14px;
    font-weight: bold;
    color: #555;
    flex: none; /* Evita que o label fique muito comprimido */
}

.select-small {
    width: 150px; /* Reduz o tamanho dos selects */
    font-size: 14px;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.select-small:focus {
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

@media (max-width: 768px) {
    .goal-row {
        flex-wrap: nowrap; /* Garante que fiquem na mesma linha */
        gap: 10px; /* Reduz o espaço entre os elementos */
    }

    .select-small {
        width: 120px; /* Diminui o tamanho dos selects em telas pequenas */
    }

    .goal-label {
        font-size: 12px; /* Ajusta o tamanho do texto para telas menores */
    }
}
</style>
