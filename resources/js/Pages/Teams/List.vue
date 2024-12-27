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
        team: Object,
    },
    data() {
        return {
            activeTab: "players",
        };
    },
    computed: {
        activeTabComponent() {
            const tabComponents = {
                info: "Info",
                players: "Players",
                championships: "Championships",
                matches: "Matches",
                stats: "Stats",
            };

            return tabComponents[this.activeTab] || "Info";
        },
    },
};
</script>

<style scoped>
/* Container Geral */
.team-container {
    margin: 2rem auto;
    max-width: 1200px; /* Limitar o conteúdo */
    text-align: center;
}

/* Títulos */
.team-title {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 0.8rem;
    text-align: center;
}

.team-subtitle {
    font-size: 1.8rem;
    font-weight: 500;
    margin-bottom: 2rem;
}

/* Tabs */
.nav-tabs {
    display: flex;
    justify-content: center; /* Centralização das tabs */
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #ddd;
    gap: 1rem; /* Espaço entre tabs */
}

/* Estilo das Tabs */
.nav-item {
    list-style: none;
}

.nav-link {
    background-color: transparent;
    border: 2px solid transparent;
    color: #6a1b9a;
    font-size: 1rem;
    font-weight: bold;
    text-transform: uppercase;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background-color: #e2cdf1;
    color: #502c71;
    border-color: #502c71;
}

.nav-link.active {
    background-color: #6a1b9a;
    color: white;
    border-color: #6a1b9a;
    transform: scale(1.1); /* Destaca aba ativa */
}

/* Tab Content */
.tab-content {
    padding: 2rem;
    background: white;
    border: 2px solid #ddd;
    border-radius: 14px;
    box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.15); /* Mais destaque */
    text-align: left;
}

/* Estilo responsivo */
@media (min-width: 768px) {
    .nav-tabs {
        justify-content: flex-start; /* Tabs ficam alinhadas à esquerda */
    }

    .nav-link {
        padding: 0.5rem 1.5rem; /* Mais espaçamento no desktop */
        font-size: 1.1rem; /* Texto levemente maior */
    }

    .team-title {
        font-size: 3.5rem; /* Aumentar título no desktop */
    }

    .team-subtitle {
        font-size: 1.8rem;
    }
}

@media (max-width: 576px) {
    .nav-tabs {
        flex-wrap: wrap; /* Tabs ocupam mais de uma linha no mobile */
    }

    .nav-link {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }

    .tab-content {
        padding: 1rem;
    }
}
</style>
