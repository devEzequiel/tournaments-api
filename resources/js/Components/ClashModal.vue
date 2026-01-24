<template>
    <div v-if="show" class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-container">
            <!-- Header -->
            <div class="modal-header">
                <h2>⚔️ {{ title }}</h2>
                <button @click="$emit('close')" class="close-btn" type="button">
                    <span>×</span>
                </button>
            </div>

            <div class="modal-body">
                <div v-if="loading" class="loading">
                    <div class="spinner"></div>
                    <p>Carregando confrontos...</p>
                </div>

                <div v-else>
                    <!-- Statistics Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-label">Vitórias</div>
                            <div class="stat-value">
                                <span :class="{ winner: stats.team1Wins > stats.team2Wins }">{{ stats.team1Wins }}</span>
                                <span class="separator">×</span>
                                <span :class="{ winner: stats.team2Wins > stats.team1Wins }">{{ stats.team2Wins }}</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-label">Gols Marcados</div>
                            <div class="stat-value">
                                <span :class="{ winner: stats.team1Goals > stats.team2Goals }">{{ stats.team1Goals }}</span>
                                <span class="separator">×</span>
                                <span :class="{ winner: stats.team2Goals > stats.team1Goals }">{{ stats.team2Goals }}</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-label">Gols Fora</div>
                            <div class="stat-value">
                                <span :class="{ winner: stats.team1AwayGoals > stats.team2AwayGoals }">{{ stats.team1AwayGoals }}</span>
                                <span class="separator">×</span>
                                <span :class="{ winner: stats.team2AwayGoals > stats.team1AwayGoals }">{{ stats.team2AwayGoals }}</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-label">Empates</div>
                            <div class="stat-value centered">
                                {{ stats.draws }}
                            </div>
                        </div>
                    </div>

                    <!-- Final Round Prediction (if odd rounds) -->
                    <div v-if="finalRoundPrediction" class="prediction-banner">
                        <div class="prediction-icon">🏆</div>
                        <div class="prediction-content">
                            <div class="prediction-title">Rodada Final (Projeção)</div>
                            <div class="prediction-text">
                                <strong>{{ finalRoundPrediction.homeTeam }}</strong> jogaria em casa
                                <span class="prediction-reason">({{ finalRoundPrediction.reason }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Matches List -->
                    <div class="matches-section">
                        <h3 class="section-title">📋 Histórico de Confrontos</h3>
                        
                        <div v-if="matchesList.length === 0" class="empty-state">
                            <p>Nenhum confronto ainda</p>
                        </div>

                        <div v-else class="matches-list">
                            <div
                                v-for="(match, index) in matchesList"
                                :key="index"
                                :class="['match-card', { played: match.is_played, pending: !match.is_played, projected: match.isProjected }]"
                            >
                                <div class="match-round">Rodada {{ match.round || '?' }}</div>
                                <div class="match-teams">
                                    <span class="team-home">{{ match.home_team }}</span>
                                    <span class="match-score">
                                        <span v-if="match.is_played">
                                            {{ match.home_goals }} × {{ match.away_goals }}
                                        </span>
                                        <span v-else-if="match.isProjected" class="projected-label">
                                            (Projeção)
                                        </span>
                                        <span v-else class="pending-label">
                                            vs
                                        </span>
                                    </span>
                                    <span class="team-away">{{ match.away_team }}</span>
                                </div>
                                <div v-if="match.is_played" class="match-result">
                                    <span v-if="match.home_goals > match.away_goals" class="result-win">
                                        Vitória {{ match.home_team }}
                                    </span>
                                    <span v-else-if="match.away_goals > match.home_goals" class="result-win">
                                        Vitória {{ match.away_team }}
                                    </span>
                                    <span v-else class="result-draw">
                                        Empate
                                    </span>
                                </div>
                                <div v-else-if="match.isProjected" class="match-result projected">
                                    Confronto final baseado no desempenho
                                </div>
                                <div v-else class="match-result pending">
                                    Aguardando
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        show: {
            type: Boolean,
            required: true,
        },
        loading: {
            type: Boolean,
            required: false,
        },
        title: {
            type: String,
            required: true,
        },
        matches: {
            type: [Array, Object],
            required: true,
        },
        championshipRounds: {
            type: Number,
            default: 0
        },
        team1Name: {
            type: String,
            default: ''
        },
        team2Name: {
            type: String,
            default: ''
        }
    },
    computed: {
        matchesList() {
            if (Array.isArray(this.matches)) {
                return this.matches;
            }
            if (this.matches && this.matches.results) {
                return this.matches.results;
            }
            return [];
        },
        stats() {
            const stats = {
                team1Wins: 0,
                team2Wins: 0,
                draws: 0,
                team1Goals: 0,
                team2Goals: 0,
                team1AwayGoals: 0,
                team2AwayGoals: 0
            };

            this.matchesList.forEach(match => {
                if (match.is_played && !match.isProjected) {
                    const homeGoals = parseInt(match.home_goals) || 0;
                    const awayGoals = parseInt(match.away_goals) || 0;

                    // Determina qual time é team1 (baseado no título)
                    const isTeam1Home = match.home_team === this.team1Name;

                    if (isTeam1Home) {
                        stats.team1Goals += homeGoals;
                        stats.team2Goals += awayGoals;
                        stats.team2AwayGoals += awayGoals;
                    } else {
                        stats.team1Goals += awayGoals;
                        stats.team2Goals += homeGoals;
                        stats.team1AwayGoals += awayGoals;
                    }

                    if (homeGoals > awayGoals) {
                        if (isTeam1Home) stats.team1Wins++;
                        else stats.team2Wins++;
                    } else if (awayGoals > homeGoals) {
                        if (isTeam1Home) stats.team2Wins++;
                        else stats.team1Wins++;
                    } else {
                        stats.draws++;
                    }
                }
            });

            return stats;
        },
        finalRoundPrediction() {
            // Verifica se é campeonato com rodadas ímpares
            if (!this.championshipRounds || this.championshipRounds % 2 === 0) {
                return null;
            }

            const s = this.stats;
            let homeTeam = '';
            let reason = '';

            // Aplica os mesmos critérios do FinalRoundGeneratorService
            if (s.team1Wins > s.team2Wins) {
                homeTeam = this.team1Name;
                reason = 'mais vitórias';
            } else if (s.team2Wins > s.team1Wins) {
                homeTeam = this.team2Name;
                reason = 'mais vitórias';
            } else if (s.team1Goals > s.team2Goals) {
                homeTeam = this.team1Name;
                reason = 'mais gols totais';
            } else if (s.team2Goals > s.team1Goals) {
                homeTeam = this.team2Name;
                reason = 'mais gols totais';
            } else if (s.team1AwayGoals > s.team2AwayGoals) {
                homeTeam = this.team1Name;
                reason = 'mais gols fora';
            } else if (s.team2AwayGoals > s.team1AwayGoals) {
                homeTeam = this.team2Name;
                reason = 'mais gols fora';
            } else {
                homeTeam = 'Sorteio';
                reason = 'empate em todos os critérios';
            }

            return { homeTeam, reason };
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
    max-width: 700px;
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
    font-size: 22px;
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

/* Modal Body */
.modal-body {
    padding: 30px;
}

/* Loading */
.loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    gap: 16px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading p {
    color: #999;
    margin: 0;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    border: 2px solid #e0e0e0;
    transition: all 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-label {
    font-size: 13px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.stat-value.centered {
    justify-content: center;
}

.stat-value .separator {
    color: #999;
    font-size: 20px;
}

.stat-value .winner {
    color: #28a745;
}

/* Prediction Banner */
.prediction-banner {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 2px solid #ffc107;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    display: flex;
    gap: 16px;
    align-items: center;
}

.prediction-icon {
    font-size: 32px;
    flex-shrink: 0;
}

.prediction-content {
    flex: 1;
}

.prediction-title {
    font-size: 14px;
    font-weight: 600;
    color: #856404;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.prediction-text {
    font-size: 15px;
    color: #533f03;
}

.prediction-text strong {
    color: #dc3545;
}

.prediction-reason {
    display: block;
    font-size: 12px;
    color: #856404;
    margin-top: 4px;
    font-style: italic;
}

/* Matches Section */
.matches-section {
    margin-top: 24px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0 0 16px 0;
}

.empty-state {
    padding: 40px 20px;
    text-align: center;
    background: #f8f9fa;
    border-radius: 12px;
    border: 2px dashed #ddd;
}

.empty-state p {
    margin: 0;
    color: #999;
    font-size: 14px;
}

/* Matches List */
.matches-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.match-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 16px;
    border: 2px solid #e0e0e0;
    transition: all 0.2s;
}

.match-card:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.match-card.played {
    border-color: #28a745;
    background: linear-gradient(135deg, #f1f9f3 0%, #e8f5e9 100%);
}

.match-card.pending {
    border-color: #ffc107;
    background: linear-gradient(135deg, #fffbf0 0%, #fff8e1 100%);
}

.match-card.projected {
    border-color: #667eea;
    background: linear-gradient(135deg, #f0f4ff 0%, #e7edff 100%);
}

.match-round {
    font-size: 11px;
    font-weight: 600;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.match-teams {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.team-home,
.team-away {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    flex: 1;
}

.team-away {
    text-align: right;
}

.match-score {
    font-size: 20px;
    font-weight: bold;
    color: #667eea;
    padding: 0 16px;
}

.projected-label,
.pending-label {
    font-size: 14px;
    color: #999;
}

.match-result {
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    padding: 6px 12px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.5);
}

.result-win {
    color: #28a745;
    font-weight: 600;
}

.result-draw {
    color: #6c757d;
}

.match-result.pending {
    color: #856404;
}

.match-result.projected {
    color: #667eea;
    font-style: italic;
}

/* Responsive */
@media (max-width: 768px) {
    .modal-container {
        max-width: 100%;
        max-height: 100vh;
        border-radius: 0;
    }

    .modal-body {
        padding: 20px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .stat-value {
        font-size: 24px;
    }

    .prediction-banner {
        flex-direction: column;
        text-align: center;
    }

    .match-teams {
        flex-direction: column;
        gap: 8px;
    }

    .team-home,
    .team-away {
        text-align: center;
    }
}
</style>
