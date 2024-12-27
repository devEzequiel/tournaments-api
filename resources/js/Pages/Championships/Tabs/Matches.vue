<template>
    <div>
        <h2>Partidas</h2>
        <div v-for="round in groupedMatches" :key="round.round" class="round">
            <h3>Rodada {{ round.round }}</h3>
            <div v-for="match in round.matches" :key="match.id" class="match">
                <div class="team">
                    <span
                        class="team-name"
                        :style="{ color: match.home_team_color }"
                    >
                        {{ match.home_team_name }}
                    </span>
                    <img
                        :src="getTeamLogo(match.home_team_name)"
                        alt="Escudo {{ match.home_team_name }}"
                        class="team-logo"
                    />
                </div>
                <span class="vs">VS</span>
                <div class="team">
                    <img
                        :src="getTeamLogo(match.away_team_name)"
                        alt="Escudo {{ match.away_team_name }}"
                        class="team-logo"
                    />
                    <span
                        class="team-name"
                        :style="{ color: match.away_team_color }"
                    >
                        {{ match.away_team_name }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        matches: Array, // Partidas do campeonato
    },
    computed: {
        // Organizar partidas por rodada
        groupedMatches() {
            const rounds = {};
            this.matches.forEach((match) => {
                if (!rounds[match.round_number]) {
                    rounds[match.round_number] = [];
                }
                rounds[match.round_number].push(match);
            });

            return Object.keys(rounds).map((key) => ({
                round: key,
                matches: rounds[key],
            }));
        },
    },
    methods: {
        getTeamLogo(teamName) {
            return `/logos/${teamName.toLowerCase().replace(/\s+/g, "-")}.png`;
        },
    },
};
</script>
