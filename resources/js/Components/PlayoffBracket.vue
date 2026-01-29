<template>
    <div class="bracket-wrapper" v-if="hasPlayoffs">
        <h3 class="bracket-heading">Chaveamento</h3>
        
        <div class="bracket-container">
            
            <!-- Quarter Finals -->
            <div class="round-column" v-if="quarterfinals.length > 0">
                <div class="round-header">Quartas de Final</div>
                <div class="match-pair" v-for="(pair, index) in quarterPairs" :key="index">
                    <div class="match-card-wrapper" v-for="match in pair" :key="match.id">
                        <div class="match-card" @click="$emit('match-click', match)">
                            <div class="team-row" :class="{'winner': isWinner(match, 'home')}">
                                <span class="team-name">{{ formatTeamName(match.home_team_name) }}</span>
                                <span class="score-badge">{{ match.home_goals ?? '-' }}</span>
                            </div>
                            <div class="team-row" :class="{'winner': isWinner(match, 'away')}">
                                <span class="team-name">{{ formatTeamName(match.away_team_name) }}</span>
                                <span class="score-badge">{{ match.away_goals ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="line-connector-right"></div>
                    </div>
                    <div class="connector-vertical"></div>
                </div>
            </div>

            <!-- Connector Column (Spacer) -->
            <div class="connector-spacer" v-if="quarterfinals.length > 0"></div>

            <!-- Semifinals -->
            <div class="round-column" v-if="semifinals.length > 0">
                <div class="round-header">Semifinal</div>
                <div class="match-pair" v-for="(pair, index) in semiPairs" :key="index">
                    <div class="match-card-wrapper" v-for="match in pair" :key="match.id">
                        <div class="line-connector-left"></div>
                        <div class="match-card" @click="$emit('match-click', match)">
                            <div class="team-row" :class="{'winner': isWinner(match, 'home')}">
                                <span class="team-name">{{ formatTeamName(match.home_team_name) }}</span>
                                <span class="score-badge">{{ match.home_goals ?? '-' }}</span>
                            </div>
                            <div class="team-row" :class="{'winner': isWinner(match, 'away')}">
                                <span class="team-name">{{ formatTeamName(match.away_team_name) }}</span>
                                <span class="score-badge">{{ match.away_goals ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="line-connector-right" v-if="finals.length > 0"></div>
                    </div>
                    <div class="connector-vertical" v-if="finals.length > 0"></div>
                </div>
            </div>

            <!-- Connector Column (Spacer) -->
            <div class="connector-spacer" v-if="semifinals.length > 0 && finals.length > 0"></div>

            <!-- Finals -->
            <div class="round-column" v-if="finals.length > 0">
                <div class="round-header">Final</div>
                <div class="match-pair final-pair">
                    <div class="match-card-wrapper final-wrapper" v-for="match in finals" :key="match.id">
                        <div class="line-connector-left"></div>
                        <div class="match-card final-card" @click="$emit('match-click', match)">
                            <i class="fas fa-trophy trophy-icon" v-if="match.is_played && (isWinner(match, 'home') || isWinner(match, 'away'))"></i>
                            <div class="team-row" :class="{'winner': isWinner(match, 'home')}">
                                <span class="team-name">{{ formatTeamName(match.home_team_name) }}</span>
                                <span class="score-badge">{{ match.home_goals ?? '-' }}</span>
                            </div>
                            <div class="team-row" :class="{'winner': isWinner(match, 'away')}">
                                <span class="team-name">{{ formatTeamName(match.away_team_name) }}</span>
                                <span class="score-badge">{{ match.away_goals ?? '-' }}</span>
                            </div>
                            <div class="final-badge">Final</div>
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
        matches: {
            type: Array,
            required: true
        }
    },
    computed: {
        playoffMatches() {
            // Sort matches to ensure they pair up correctly generally (by ID or game number)
            return this.matches
                .filter(m => m.is_playoff)
                .sort((a, b) => (a.playoff_game_number || a.id) - (b.playoff_game_number || b.id));
        },
        hasPlayoffs() {
            return this.playoffMatches.length > 0;
        },
        quarterfinals() {
            return this.playoffMatches.filter(m => m.playoff_stage === 'quarterfinal' || m.round_name === 'Quarter Final');
        },
        semifinals() {
            return this.playoffMatches.filter(m => m.playoff_stage === 'semifinal');
        },
        finals() {
            return this.playoffMatches.filter(m => m.playoff_stage === 'final');
        },
        quarterPairs() {
            return this.chunk(this.quarterfinals, 2);
        },
        semiPairs() {
            return this.chunk(this.semifinals, 2);
        },
        // Calcula o vencedor geral da final (considerando todos os jogos da série)
        finalChampion() {
            if (this.finals.length === 0) return null;
            
            const playedFinals = this.finals.filter(f => f.is_played);
            if (playedFinals.length === 0) return null;
            
            // Agrupa por time
            const teams = {};
            playedFinals.forEach(match => {
                const homeId = match.home_team_id;
                const awayId = match.away_team_id;
                
                if (!teams[homeId]) teams[homeId] = { wins: 0, name: match.home_team_name, awayGoals: 0 };
                if (!teams[awayId]) teams[awayId] = { wins: 0, name: match.away_team_name, awayGoals: 0 };
                
                const homeGoals = parseInt(match.home_goals || 0);
                const awayGoals = parseInt(match.away_goals || 0);
                
                // Conta vitórias
                if (homeGoals > awayGoals) {
                    teams[homeId].wins++;
                } else if (awayGoals > homeGoals) {
                    teams[awayId].wins++;
                }
                
                // Conta gols fora
                teams[awayId].awayGoals += awayGoals;
                teams[homeId].awayGoals += homeGoals;
            });
            
            // Verifica se algum time tem 2 vitórias
            for (const teamId in teams) {
                if (teams[teamId].wins >= 2) {
                    return { teamId: parseInt(teamId), name: teams[teamId].name };
                }
            }
            
            // Se 1-1 em vitórias, verifica gols fora
            const teamIds = Object.keys(teams);
            if (teamIds.length === 2 && playedFinals.length >= 2) {
                const team1 = teams[teamIds[0]];
                const team2 = teams[teamIds[1]];
                
                if (team1.wins === team2.wins) {
                    if (team1.awayGoals > team2.awayGoals) {
                        return { teamId: parseInt(teamIds[0]), name: team1.name };
                    } else if (team2.awayGoals > team1.awayGoals) {
                        return { teamId: parseInt(teamIds[1]), name: team2.name };
                    }
                }
            }
            
            return null;
        }
    },
    methods: {
        formatTeamName(name) {
            return name ? name : 'TBD';
        },
        isWinner(match, side) {
            // Para a final, verifica se é o campeão geral da série
            if (match.playoff_stage === 'final' && this.finalChampion) {
                const teamId = side === 'home' ? match.home_team_id : match.away_team_id;
                return teamId === this.finalChampion.teamId;
            }
            
            // Para outras fases, verifica vitória individual
            if (!match.is_played) return false;
            
            const home = parseInt(match.home_goals || 0);
            const away = parseInt(match.away_goals || 0);
            
            if (side === 'home') return home > away;
            if (side === 'away') return away > home;
            return false;
        },
        chunk(arr, size) {
            return Array.from({ length: Math.ceil(arr.length / size) }, (v, i) =>
                arr.slice(i * size, i * size + size)
            );
        }
    }
}
</script>

<style scoped>
.bracket-wrapper {
    background: #1a202c; /* Dark background like the example */
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    overflow-x: auto;
    color: white;
}

.bracket-heading {
    text-align: center;
    color: #e2e8f0;
    margin-bottom: 2rem;
    font-size: 1.5rem;
    font-weight: 700;
}

.bracket-container {
    display: flex;
    justify-content: center;
    align-items: stretch;
    min-width: 800px;
    padding-bottom: 1rem;
}

.round-column {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    flex: 1;
    max-width: 280px;
}

.round-header {
    text-align: center;
    text-transform: uppercase;
    color: #718096;
    font-weight: bold;
    font-size: 0.85rem;
    margin-bottom: 1rem;
    height: 20px;
}

.match-pair {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
    flex-grow: 1;
    margin: 1rem 0; /* Space between pairs */
}

.match-card-wrapper {
    position: relative;
    width: 100%;
    margin: 10px 0; /* Space between matches in a pair */
    display: flex;
    align-items: center;
}

.match-card {
    background: #2d3748;
    border: 1px solid #4a5568;
    border-radius: 8px;
    padding: 10px;
    width: 100%;
    cursor: pointer;
    transition: transform 0.2s, background-color 0.2s;
    z-index: 2;
    position: relative;
}

.match-card:hover {
    background: #4a5568;
    transform: scale(1.02);
}

.team-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 4px 0;
    font-size: 0.9rem;
    color: #cbd5e0;
}

.team-row.winner {
    color: #fff;
    font-weight: bold;
}

.team-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

.score-badge {
    background: #1a202c;
    padding: 2px 8px;
    border-radius: 4px;
    font-family: monospace;
    min-width: 24px;
    text-align: center;
}

/* Connectors */
.connector-vertical {
    position: absolute;
    right: 0;
    top: 50%;
    bottom: 50%;
    width: 20px; /* Reduced width */
    border-right: 2px solid #718096;
    transform: translateY(-50%); /* Start from center */
    /* This logic needs to connect the two children */
    /* Implementation using matches height is tricky, let's use fixed heights or calc */
    height: calc(100% - 70px); /* Approximate offset based on card height */
    margin-right: -20px; /* Push out of the column */
}

/* Simple Connecting Lines approach */
/* Right Connector: Line extending to the right from a match card */
.line-connector-right {
    position: absolute;
    right: -20px;
    top: 50%;
    width: 20px;
    height: 2px;
    background: #718096;
    z-index: 1;
}

/* Vertical Connector Bracket: Connects two matches in a pair */
.match-pair .connector-vertical {
    position: absolute;
    right: -20px;
    top: 25%; /* Start from center of top match */
    bottom: 25%; /* End at center of bottom match */
    width: 2px;
    background: #718096;
    height: auto; /* Override previous */
    border: none;
    margin: 0;
}

/* Left Connector: Line coming in from the left */
.line-connector-left {
    position: absolute;
    left: -20px;
    top: 50%;
    width: 20px;
    height: 2px;
    background: #718096;
    z-index: 1;
}

/* Final Card Styling */
.final-card {
    border-color: #ecc94b;
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
}

.final-badge {
    background: #ecc94b;
    color: #000;
    font-size: 0.7rem;
    font-weight: bold;
    text-transform: uppercase;
    border-radius: 10px;
    padding: 2px 8px;
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
}

.trophy-icon {
    position: absolute;
    right: -30px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 2rem;
    color: #ecc94b;
}

/* Ensure spacing in columns */
.connector-spacer {
    width: 40px;
}

/* Mobile Scroll */
@media (max-width: 768px) {
    .match-pair {
        margin: 0.5rem 0;
    }
    .bracket-container {
        min-width: 600px;
    }
}
</style>
