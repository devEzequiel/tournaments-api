<template>
    <DefaultLayout>
        <div class="list-container">
            <h1 class="championship-title">{{ championship.name }}</h1>

            <!-- Banner do Campeão -->
            <div v-if="champion" class="champion-banner">
                <div class="champion-trophy">🏆</div>
                <div class="champion-content">
                    <h3 class="champion-title">CAMPEÃO</h3>
                    <div class="champion-team">
                        <TeamLogo
                            :firstColor="champion.first_color"
                            :secondColor="champion.second_color"
                            class="champion-logo"
                        />
                        <span class="champion-name">{{ champion.name }}</span>
                    </div>
                    <div v-if="awards && awards.length" class="champion-awards">
                        <div v-for="award in awards" :key="award.id" class="award-item">
                            <span class="award-icon">{{ getAwardIcon(award.type) }}</span>
                            <span class="award-player">{{ award.player_name }}</span>
                        </div>
                    </div>
                </div>
                <div class="champion-trophy">🏆</div>
            </div>

            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <button
                        class="nav-link"
                        :class="{ active: activeTab === 'matches' }"
                        @click="activeTab = 'matches'; fetchMatches()"
                    >
                        Matches
                    </button>
                </li>
                <li class="nav-item">
                    <button
                        class="nav-link"
                        :class="{ active: activeTab === 'rank' }"
                        @click="activeTab = 'rank'"
                    >
                        Rank
                    </button>
                </li>
                <li class="nav-item">
                    <button
                        class="nav-link"
                        :class="{ active: activeTab === 'table' }"
                        @click="activeTab = 'table'"
                    >
                        Table
                    </button>
                </li>
                <li class="nav-item">
                    <button
                        class="nav-link"
                        :class="{ active: activeTab === 'info' }"
                        @click="activeTab = 'info'"
                    >
                        Info
                    </button>
                </li>
                <li class="nav-item">
                    <button
                        class="nav-link"
                        :class="{ active: activeTab === 'stats' }"
                        @click="activeTab = 'stats'"
                    >
                        Stats
                    </button>
                </li>
                <li class="nav-item">
                    <button
                        class="nav-link"
                        :class="{ active: activeTab === 'results' }"
                        @click="activeTab = 'results'"
                    >
                        Results
                    </button>
                </li>
            </ul>

            <div class="tab-content mt-4">
                <Transition name="fade" mode="out-in">
                    <component
                        :is="activeTabComponent"
                        :key="activeTab"
                        :championship-data="championship"
                        :matches="matches"
                        :champion="champion"
                        :championshipId="championship.id"
                        @update-matches="updateMatches"
                    />
                </Transition>
            </div>
        </div>
    </DefaultLayout>
</template>

<script>
import axios from "axios";
import DefaultLayout from "@/Layouts/DefaultLayout.vue";
import Info from "./Tabs/Info.vue";
import Matches from "./Tabs/Matches.vue";
import Table from "./Tabs/Table.vue";
import Rank from "./Tabs/Rank.vue";
import Stats from "./Tabs/Stats.vue";
import Results from "./Tabs/Results.vue";
import TeamLogo from "@/Components/TeamLogo.vue";

export default {
    components: {
        DefaultLayout,
        Info,
        Matches,
        Table,
        Rank,
        Stats,
        Results,
        TeamLogo,
    },
    props: {
        championship: Object,
    },
    data() {
        return {
            matches: [],
            champion: null,
            awards: [],
            activeTab: localStorage.getItem('championshipActiveTab') || "matches",
        };
    },
    watch: {
        activeTab(newTab) {
            localStorage.setItem('championshipActiveTab', newTab);
        }
    },
    computed: {
        activeTabComponent() {
            const tabComponents = {
                matches: "Matches",
                rank: "Rank",
                table: "Table",
                info: "Info",
                stats: "Stats",
                results: "Results",
            };
            return tabComponents[this.activeTab] || "Info";
        },
    },
    methods: {
        async fetchMatches() {
            try {
                const response = await axios.get(
                    `/api/fixtures/${this.championship.id}/basic`
                );

                if (response.data && response.data.data) {
                    this.matches = response.data.data.fixtures || [];
                    this.champion = response.data.data.champion || null;
                }
            } catch (error) {
                console.error("Erro ao carregar partidas:", error);
            }
        },
        async fetchAwards() {
            try {
                const response = await axios.get(`/api/championship/${this.championship.id}/awards`);
                if (response.data && response.data.data) {
                    this.awards = response.data.data;
                }
            } catch (error) {
                console.error("Erro ao carregar prêmios:", error);
            }
        },
        getAwardIcon(type) {
            const icons = {
                'top_scorer': '⚽',
                'the_best': '👑',
                'playmaker': '🅰️',
            };
            return icons[type] || '🏅';
        },
        async updateMatches() {
            // Reutiliza o metodo fetchMatches para atualizar os dados
            this.fetchMatches();
        },
    },
    mounted() {
        // Carregar as partidas automaticamente ao abrir o componente
        if (this.activeTab === "matches") {
            this.fetchMatches();
        }
        // Carregar awards se houver playoffs
        if (this.championship.playoffs) {
            this.fetchAwards();
        }
    },
};
</script>

<style scoped>
.list-container {
    margin: 2rem auto;
    max-width: 1200px;
    text-align: center;
}

.championship-title {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--color-primary);
    margin-bottom: 1.5rem;
    text-align: center;
}

.nav-tabs {
    display: flex;
    justify-content: flex-start;
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    list-style: none;
    padding: 0.5rem;
    margin-bottom: 1.5rem;
    gap: 0.75rem;
    border-bottom: 1px solid var(--color-border);
    scrollbar-width: none;
}

.nav-tabs::-webkit-scrollbar {
    display: none;
}

.nav-item {
    list-style: none;
}

.nav-link {
    background-color: transparent;
    border: 1px solid transparent;
    color: var(--color-text);
    text-transform: uppercase;
    font-size: 0.95rem;
    font-weight: 600;
    text-align: center;
    padding: 0.5rem 0.95rem;
    border-radius: 999px;
    cursor: pointer;
    transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s;
    white-space: nowrap;
}

.nav-link:hover {
    background-color: rgba(79, 70, 229, 0.08);
    color: var(--color-primary);
    border-color: rgba(79, 70, 229, 0.15);
}

.nav-link.active {
    background-color: rgba(79, 70, 229, 0.15);
    color: var(--color-primary);
    border-color: rgba(79, 70, 229, 0.2);
    transform: translateY(-1px);
}

.tab-content {
    padding: 2rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: 14px;
    box-shadow: 0px 12px 30px rgba(15, 23, 42, 0.08);
    min-height: 400px;
}

.fade-enter-active {
    transition: all 0.3s ease-out;
}

.fade-leave-active {
    transition: all 0.2s ease-in;
}

.fade-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

@media (min-width: 768px) {
    .nav-tabs {
        justify-content: flex-start;
    }

    .nav-link {
        font-size: 1.1rem;
        padding: 0.6rem 1.5rem;
    }

    .championship-title {
        font-size: 3rem;
    }
}

@media (max-width: 768px) {
    .list-container {
        margin: 1rem auto;
        text-align: left;
    }

    .championship-title {
        font-size: 2rem;
    }

    .nav-link {
        font-size: 0.85rem;
        padding: 0.45rem 0.75rem;
    }

    .tab-content {
        padding: 1.25rem;
    }
}

@media (max-width: 576px) {
    .nav-tabs {
        flex-wrap: nowrap;
        justify-content: flex-start;
        gap: 0.5rem;
    }

    .nav-link {
        font-size: 0.9rem; /* Fonte menor */
        padding: 0.4rem 1rem; /* Padding reduzidos */
    }

    .tab-content {
        padding: 1rem;
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
    margin-bottom: 15px;
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

.champion-awards {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    margin-top: 15px;
}

.award-item {
    background: rgba(255, 255, 255, 0.9);
    padding: 8px 15px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #333;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.award-icon {
    font-size: 1.2rem;
}

.award-player {
    color: #4a2c0f;
}

@media (max-width: 768px) {
    .champion-banner {
        padding: 15px;
        gap: 15px;
    }
    
    .champion-trophy {
        font-size: 2.5rem;
    }
    
    .champion-title {
        font-size: 1.2rem;
    }
    
    .champion-name {
        font-size: 1rem;
    }
    
    .champion-logo {
        width: 40px;
        height: 40px;
    }
    
    .award-item {
        font-size: 0.75rem;
        padding: 6px 10px;
    }
}

</style>
