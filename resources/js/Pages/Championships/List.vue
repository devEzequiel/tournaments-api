<template>
    <DefaultLayout>
        <div class="list-container">
            <!-- Título do Campeonato -->
            <h1 class="championship-title">{{ championship.name }}</h1>

            <!-- Tabs -->
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
            </ul>

            <!-- Conteúdo das Tabs -->
            <div class="tab-content mt-4">
                <component
                    :is="activeTabComponent"
                    :championship-data="championship"
                    :matches="matches"
                    :championship-id="championship.id"
                />
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

export default {
    components: {
        DefaultLayout,
        Info,
        Matches,
        Table,
        Rank,
        Stats
    },
    props: {
        championship: Object,
    },
    data() {
        return {
            matches: [],
            activeTab: "matches",
        };
    },
    computed: {
        activeTabComponent() {
            const tabComponents = {
                matches: "Matches",
                rank: "Rank",
                table: "Table",
                info: "Info",
                stats: "Stats",
            };
            return tabComponents[this.activeTab] || "Info";
        },
    },
    methods: {
        async fetchMatches() {
            try {
                if (this.matches.length > 0) return;

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
    },
};
</script>

<style scoped>
/* Container principal */
.list-container {
    margin: 2rem auto;
    max-width: 1200px; /* Centralizar o conteúdo e limitar o tamanho */
    text-align: center;
}

/* Título */
.championship-title {
    font-size: 2.5rem;
    font-weight: bold;
    color: #6a1b9a;
    margin-bottom: 1.5rem;
    text-align: center;
}

/* Tabs */
.nav-tabs {
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
    margin-bottom: 2rem;
    gap: 1rem;
    border-bottom: 2px solid #ddd;
}

/* Itens das tabs */
.nav-item {
    list-style: none;
}

.nav-link {
    background-color: transparent;
    border: 2px solid transparent;
    color: #6a1b9a;
    text-transform: uppercase;
    font-size: 1rem;
    font-weight: bold;
    text-align: center;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s;
}

.nav-link:hover {
    background-color: #e2cdf1;
    color: #502c71;
    border-color: #502c71;
}

.nav-link.active {
    background-color: #6a1b9a;
    color: #ffffff;
    border-color: #6a1b9a;
    transform: scale(1.1); /* Destaque ao selecionar */
}

/* Conteúdo das Tabs */
.tab-content {
    padding: 2rem;
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 14px;
    box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.15); /* Sombra para destaque */
}

/* ---------- Responsividade ---------- */
/* Tela grande */
@media (min-width: 768px) {
    .nav-tabs {
        justify-content: flex-start; /* Alinhar à esquerda no desktop */
    }

    .nav-link {
        font-size: 1.1rem;
        padding: 0.6rem 1.5rem; /* Ajustar tamanho no desktop */
    }

    .championship-title {
        font-size: 3rem;
    }
}

/* Tela pequena (mobile) */
@media (max-width: 576px) {
    .nav-tabs {
        flex-wrap: wrap; /* Permitir quebra de linha */
        justify-content: center; /* Centralizar as tabs */
        gap: 0.5rem; /* Menor espaçamento em telas pequenas */
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
