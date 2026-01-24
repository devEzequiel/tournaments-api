<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-container">
            <!-- Header -->
            <div class="modal-header">
                <h2>⚽ Jogar Partida</h2>
                <button class="close-btn" @click="$emit('close')" type="button">
                    <span>×</span>
                </button>
            </div>

            <!-- Score Section -->
            <div class="score-section">
                <div class="team-score">
                    <TeamLogo
                        v-if="match.home_team_color"
                        :firstColor="match.home_team_color"
                        :secondColor="match.home_team_second_color"
                        class="team-logo"
                    />
                    <div class="team-info">
                        <h3>{{ match.home_team_name }}</h3>
                        <select v-model="homeGoalCount" @change="updateHomeGoals" class="score-select">
                            <option v-for="n in 16" :key="'home-goals-' + n" :value="n-1">{{ n-1 }}</option>
                        </select>
                    </div>
                </div>
                
                <div class="vs-divider">×</div>
                
                <div class="team-score">
                    <TeamLogo
                        v-if="match.away_team_color"
                        :firstColor="match.away_team_color"
                        :secondColor="match.away_team_second_color"
                        class="team-logo"
                    />
                    <div class="team-info">
                        <h3>{{ match.away_team_name }}</h3>
                        <select v-model="awayGoalCount" @change="updateAwayGoals" class="score-select">
                            <option v-for="n in 16" :key="'away-goals-' + n" :value="n-1">{{ n-1 }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Penalty Decision Checkbox (only for 3rd playoff games) -->
            <div v-if="isPlayoffDecisiveGame" class="penalty-section">
                <label class="penalty-checkbox">
                    <input type="checkbox" v-model="decidedByPenalty" />
                    <span class="checkbox-label">
                        <span class="checkbox-icon">🥅</span>
                        Partida decidida nos pênaltis
                    </span>
                </label>
                <p class="penalty-hint">Marque esta opção se o jogo terminou empatado e foi decidido na disputa de pênaltis</p>
            </div>

            <!-- Goals Section -->
            <div v-if="homeGoals.length > 0 || awayGoals.length > 0" class="goals-section">
                <h3 class="section-title">⚽ Gols</h3>
                
                <div v-if="homeGoals.length > 0" class="team-goals">
                    <h4 class="team-subtitle">{{ match.home_team_name }}</h4>
                    <div v-for="(goal, index) in homeGoals" :key="'home-goal-' + index" class="goal-item">
                        <span class="goal-number">Gol {{ index + 1 }}</span>
                        <div class="goal-inputs">
                            <div class="input-group">
                                <label>Marcador</label>
                                <select v-model="goal.scorer_id">
                                    <option value="">Selecione</option>
                                    <option v-for="player in homeTeamPlayers" :key="'home-scorer-' + player.player_id" :value="player.player_id">
                                        {{ player.player_name }}
                                    </option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Assistência</label>
                                <select v-model="goal.assist_id">
                                    <option value="">Sem assistência</option>
                                    <option v-for="player in homeTeamPlayers" :key="'home-assist-' + player.player_id" :value="player.player_id">
                                        {{ player.player_name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="awayGoals.length > 0" class="team-goals">
                    <h4 class="team-subtitle">{{ match.away_team_name }}</h4>
                    <div v-for="(goal, index) in awayGoals" :key="'away-goal-' + index" class="goal-item">
                        <span class="goal-number">Gol {{ index + 1 }}</span>
                        <div class="goal-inputs">
                            <div class="input-group">
                                <label>Marcador</label>
                                <select v-model="goal.scorer_id">
                                    <option value="">Selecione</option>
                                    <option v-for="player in awayTeamPlayers" :key="'away-scorer-' + player.player_id" :value="player.player_id">
                                        {{ player.player_name }}
                                    </option>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Assistência</label>
                                <select v-model="goal.assist_id">
                                    <option value="">Sem assistência</option>
                                    <option v-for="player in awayTeamPlayers" :key="'away-assist-' + player.player_id" :value="player.player_id">
                                        {{ player.player_name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Player Ratings Section -->
            <div class="ratings-section">
                <h3 class="section-title">⭐ Avaliações dos Jogadores</h3>
                <div class="ratings-grid">
                    <div class="team-ratings">
                        <h4 class="team-subtitle">{{ match.home_team_name }}</h4>
                        <div v-for="player in homeTeamPlayers" :key="'home-player-' + player.player_id" class="player-rating">
                            <span class="player-name">{{ player.player_name }}</span>
                            <div class="rating-controls">
                                <button @click.prevent="adjustRate(player.player_id, -0.5)" type="button" class="rate-btn minus">−</button>
                                <input 
                                    type="number" 
                                    v-model="playerRate[player.player_id]" 
                                    step="0.5" 
                                    min="0" 
                                    max="10"
                                    class="rate-input"
                                />
                                <button @click.prevent="adjustRate(player.player_id, 0.5)" type="button" class="rate-btn plus">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="team-ratings">
                        <h4 class="team-subtitle">{{ match.away_team_name }}</h4>
                        <div v-for="player in awayTeamPlayers" :key="'away-player-' + player.player_id" class="player-rating">
                            <span class="player-name">{{ player.player_name }}</span>
                            <div class="rating-controls">
                                <button @click.prevent="adjustRate(player.player_id, -0.5)" type="button" class="rate-btn minus">−</button>
                                <input 
                                    type="number" 
                                    v-model="playerRate[player.player_id]" 
                                    step="0.5" 
                                    min="0" 
                                    max="10"
                                    class="rate-input"
                                />
                                <button @click.prevent="adjustRate(player.player_id, 0.5)" type="button" class="rate-btn plus">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="modal-actions">
                <button @click="$emit('close')" type="button" class="btn-cancel">Cancelar</button>
                <button @click="submitMatch" type="button" class="btn-save" :disabled="isSubmitting">
                    <span v-if="!isSubmitting">💾 Salvar Partida</span>
                    <span v-else>Salvando...</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import TeamLogo from "@/Components/TeamLogo.vue";
import { useToast } from "@/Composables/useToast";

export default {
    components: {
        TeamLogo
    },
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
            isSubmitting: false,
            decidedByPenalty: false,
            toast: useToast()
        };
    },
    computed: {
        // Combinar todos os jogadores para exibir na seção de avaliações
        allPlayers() {
            return [...this.homeTeamPlayers, ...this.awayTeamPlayers];
        },
        // Verifica se é jogo decisivo de playoff (3º jogo)
        isPlayoffDecisiveGame() {
            return this.match.is_playoff && this.match.playoff_game_number === 3;
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
            if (this.isSubmitting) return;
            
            this.isSubmitting = true;
            
            const payload = {
                fixture_id: this.match.id,
                home_goals: this.homeGoalCount,
                away_goals: this.awayGoalCount,
                decided_by_penalty: this.decidedByPenalty,
                goals: [
                    ...this.homeGoals.map(goal => ({
                        scorer_id: goal.scorer_id || null,
                        assist_id: goal.assist_id || null
                    })),
                    ...this.awayGoals.map(goal => ({
                        scorer_id: goal.scorer_id || null,
                        assist_id: goal.assist_id || null
                    }))
                ],
                rates: Object.entries(this.playerRate).map(([id, rate]) => ({
                    player_id: Number(id),
                    rate
                }))
            };

            try {
                const response = await axios.put(`/api/fixtures`, payload);
                
                // Verifica se rodada final foi gerada
                if (response.data?.data?.final_round_generated) {
                    this.toast.success("Partida salva. Rodada final gerada automaticamente.");
                } else {
                    this.toast.success("Partida salva com sucesso.");
                }
                
                this.$emit("updated"); // Avisar o componente pai
                this.$emit("close"); // Fechar o modal
            } catch (error) {
                console.error("Erro ao salvar a partida:", error);
                this.toast.error("Erro ao tentar salvar a partida.");
            } finally {
                this.isSubmitting = false;
            }
        }
    }
};
</script>

<style scoped>
/* Modal Overlay */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    padding: 20px;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Modal Container */
.modal-container {
    background: #ffffff;
    border-radius: 16px;
    width: 100%;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Header */
.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 24px 30px;
    border-radius: 16px 16px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 10;
}

.modal-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}

.close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 32px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    line-height: 1;
    padding: 0;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

/* Score Section */
.score-section {
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 30px 20px;
    background: linear-gradient(to bottom, #f8f9fa, #ffffff);
    gap: 20px;
}

.team-score {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.team-logo {
    width: 60px;
    height: 60px;
}

.team-info {
    text-align: center;
    width: 100%;
}

.team-info h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.score-select {
    width: 100px;
    padding: 12px;
    font-size: 28px;
    font-weight: bold;
    text-align: center;
    border: 2px solid #667eea;
    border-radius: 12px;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.score-select:focus {
    outline: none;
    border-color: #764ba2;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.vs-divider {
    font-size: 32px;
    font-weight: bold;
    color: #999;
}

/* Section Titles */
.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0 0 16px 0;
    padding: 0 30px;
}

/* Goals Section */
.goals-section {
    padding: 30px 0;
    border-top: 1px solid #e0e0e0;
}

.team-goals {
    padding: 0 30px;
    margin-bottom: 20px;
}

.team-subtitle {
    font-size: 16px;
    font-weight: 600;
    color: #667eea;
    margin: 0 0 12px 0;
}

.goal-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
}

.goal-number {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #666;
    margin-bottom: 8px;
}

.goal-inputs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.input-group label {
    font-size: 12px;
    font-weight: 500;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.input-group select {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.input-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Ratings Section */
.ratings-section {
    padding: 30px 0;
    border-top: 1px solid #e0e0e0;
}

.ratings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    padding: 0 30px;
}

.team-ratings {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
}

.player-rating {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e0e0e0;
}

.player-rating:last-child {
    border-bottom: none;
}

.player-name {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    flex: 1;
}

.rating-controls {
    display: flex;
    align-items: center;
    gap: 6px;
}

.rate-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rate-btn.minus {
    background: #fee;
    color: #c33;
}

.rate-btn.minus:hover {
    background: #fdd;
}

.rate-btn.plus {
    background: #efe;
    color: #3c3;
}

.rate-btn.plus:hover {
    background: #dfd;
}

.rate-input {
    width: 60px;
    padding: 8px;
    text-align: center;
    font-size: 16px;
    font-weight: 600;
    border: 2px solid #ddd;
    border-radius: 6px;
    background: white;
}

.rate-input:focus {
    outline: none;
    border-color: #667eea;
}

/* Penalty Section */
.penalty-section {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 152, 0, 0.1) 100%);
    border: 2px solid #ffc107;
    border-radius: 12px;
    padding: 20px;
    margin: 0 30px 20px 30px;
}

.penalty-checkbox {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.penalty-checkbox input[type="checkbox"] {
    width: 24px;
    height: 24px;
    margin-right: 12px;
    cursor: pointer;
    accent-color: #ff9800;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.checkbox-icon {
    font-size: 20px;
}

.penalty-hint {
    margin: 8px 0 0 36px;
    font-size: 13px;
    color: #666;
    line-height: 1.5;
}

/* Actions */
.modal-actions {
    display: flex;
    gap: 12px;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 0 0 16px 16px;
    position: sticky;
    bottom: 0;
}

.btn-cancel,
.btn-save {
    flex: 1;
    padding: 14px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel {
    background: #e0e0e0;
    color: #666;
}

.btn-cancel:hover {
    background: #d0d0d0;
}

.btn-save {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-save:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .modal-container {
        max-width: 100%;
        max-height: 100vh;
        border-radius: 0;
    }
    
    .score-section {
        flex-direction: column;
        gap: 20px;
    }
    
    .vs-divider {
        transform: rotate(90deg);
    }
    
    .goal-inputs {
        grid-template-columns: 1fr;
    }
    
    .ratings-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-header h2 {
        font-size: 20px;
    }
}
</style>
