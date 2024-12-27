<template>
    <DefaultLayout>
        <div class="list-container">
            <!-- Título do Campeonato -->
            <h1 class="championship-title">{{ championshipData.name }}</h1>

            <!-- Tabs -->
            <ul class="nav nav-tabs">
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
                        :class="{ active: activeTab === 'matches' }"
                        @click="activeTab = 'matches'"
                    >
                        Matches
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
                        :class="{ active: activeTab === 'rank' }"
                        @click="activeTab = 'rank'"
                    >
                        Rank
                    </button>
                </li>
            </ul>

            <!-- Conteúdo das Tabs -->
            <div class="tab-content mt-4">
                <component
                    :is="activeTabComponent"
                    :championship-data="championshipData"
                    :matches="matches"
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

export default {
    components: {
        DefaultLayout,
        Info,
        Matches,
        Table,
        Rank,
    },
    data() {
        return {
            championshipData: null, // Dados do campeonato
            matches: [], // Dados dos jogos
            activeTab: "info", // Aba ativa
        };
    },
    computed: {
        // Carrega o componente da aba ativa
        activeTabComponent() {
            // Mapear abas para os componentes
            const tabComponents = {
                info: "Info",
                matches: "Matches",
                table: "Table",
                rank: "Rank",
            };

            return tabComponents[this.activeTab] || "Info"; // Default: Info
        },
    },
    methods: {
        // Carrega os dados do campeonato e jogos
        async fetchData() {
            try {
                // Pegar ID do campeonato via rota
                const championshipId = this.$route.params.id;

                // Buscar informações do campeonato
                const championshipResponse = await axios.get(
                    `/api/championship/${championshipId}`
                );

                this.championshipData = championshipResponse.data.data;

                // Buscar partidas do campeonato
                const matchesResponse = await axios.get(
                    `/api/fixtures/${championshipId}/unplayed`
                );

                this.matches = matchesResponse.data.data;
            } catch (error) {
                console.error("Erro ao carregar dados:", error);
            }
        },
    },
    mounted() {
        this.fetchData(); // Carregar dados ao montar
    },
};
</script>

<style scoped>
.list-container {
    margin: 2rem;
    padding: 1rem;
}

.championship-title {
    text-align: center;
    font-size: 2rem;
    font-weight: bold;
    color: #6a1b9a;
    margin-bottom: 1.5rem;
}

.nav-tabs .nav-link {
    cursor: pointer;
}

.nav-tabs .nav-link.active {
    background-color: #6a1b9a;
    color: #ffffff;
    border-color: #6a1b9a;
}

.tab-content {
    padding: 1rem;
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
</style>
