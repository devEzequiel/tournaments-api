<template>
    <DefaultLayout>
        <div class="list-container">
            <h1 class="championship-title">{{ championship.name }}</h1>

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

export default {
    components: {
        DefaultLayout,
        Info,
        Matches,
        Table,
        Rank,
        Stats,
        Results,
    },
    props: {
        championship: Object,
    },
    data() {
        return {
            matches: [],
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
                    `/api/fixtures/${this.championship.id}/unplayed`
                );

                if (response.data && response.data.data) {
                    this.matches = response.data.data;
                }
            } catch (error) {
                console.error("Erro ao carregar partidas:", error);
            }
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
</style>
