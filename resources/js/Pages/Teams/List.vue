<template>
    <DefaultLayout>
        <div class="team-container">
            <!-- Título do Time -->
            <h1 class="team-title" :style="{ color: team.first_color }">{{ team.name }}</h1>
            <h2 class="team-subtitle" :style="{ color: team.second_color }">Info & Players</h2>

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
                        :class="{ active: activeTab === 'players' }"
                        @click="activeTab = 'players'"
                    >
                        Players
                    </button>
                </li>
                <li class="nav-item">
                    <button
                        class="nav-link"
                        :class="{ active: activeTab === 'championships' }"
                        @click="activeTab = 'championships'"
                    >
                        Championships
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
                        :class="{ active: activeTab === 'stats' }"
                        @click="activeTab = 'stats'"
                    >
                        Stats
                    </button>
                </li>
            </ul>

            <!-- Conteúdo das Tabs -->
            <div class="tab-content mt-4">
                <component :is="activeTabComponent" :team="team" />
            </div>
        </div>
    </DefaultLayout>
</template>

<script>
import Info from "./Tabs/Info.vue";
import Players from "./Tabs/Players.vue";
import Championships from "./Tabs/Championships.vue";
import Matches from "./Tabs/Matches.vue";
import Stats from "./Tabs/Stats.vue";
import DefaultLayout from "@/Layouts/DefaultLayout.vue";

export default {
    components: {
        DefaultLayout,
        Info,
        Players,
        Championships,
        Matches,
        Stats,
    },
    props: {
        team: Object, // Dados do time vindos do backend
    },
    data() {
        return {
            activeTab: "players", // Define a aba padrão como Players
        };
    },
    computed: {
        // Define o componente a ser carregado com base na aba ativa
        activeTabComponent() {
            const tabComponents = {
                info: "Info",
                players: "Players",
                championships: "Championships",
                matches: "Matches",
                stats: "Stats",
            };

            return tabComponents[this.activeTab] || "Info"; // Retorna Info como padrão
        },
    },
};
</script>

<style scoped>
.team-container {
    margin: 2rem;
    padding: 1rem;
}

.team-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.team-subtitle {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 500;
    margin-bottom: 1.5rem;
}

/* Tabs */
.nav-tabs {
    display: flex;
    gap: 0.5rem;
    list-style: none;
    justify-content: center;
    border-bottom: 2px solid #ddd;
    padding-bottom: 0.5rem;
}

.nav-item {
    list-style: none;
}

.nav-link {
    background: transparent;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 0.5rem 1.5rem;
    color: #6a1b9a;
    text-align: center;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s;
}

.nav-link:hover {
    background-color: #e2cdf1;
    color: #5a1484;
}

.nav-link.active {
    background-color: #6a1b9a;
    color: #ffffff;
    border-color: #6a1b9a;
    transform: scale(1.1);
}

/* Conteúdo das Tabs */
.tab-content {
    padding: 1.5rem;
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
</style>
