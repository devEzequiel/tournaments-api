<template>
    <DefaultLayout>
        <div class="team-container">
            <!-- Botão Adicionar Jogador -->
            <div class="add-player-container">
                <button class="add-player-btn" @click="showAddPlayerModal = true">
                    Adicionar Jogador
                </button>
            </div>

            <!-- Título do Time -->
            <h3
                class="team-title"
                :style="{
        color: team.first_color,
        WebkitTextStroke: `4px ${team.second_color}`,
        textStroke: `3px ${team.second_color}`
    }"
            >
                {{ team.name }}
            </h3>

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
                    <li class="nav-item">
                        <button
                            class="nav-link"
                            :class="{ active: activeTab === 'squad' }"
                            @click="activeTab = 'squad'"
                        >
                            Squad
                        </button>
                    </li>
                </li>
            </ul>

            <!-- Conteúdo das Tabs -->
            <div class="tab-content mt-4">
                <component :is="activeTabComponent" :team="team"/>
            </div>

            <!-- Modal Adicionar Jogador -->
            <AddPlayerModal
                :teamId="team.id"
                v-if="showAddPlayerModal"
                @close="showAddPlayerModal = false"
                @add-player="handleAddPlayer"
            />
        </div>
    </DefaultLayout>
</template>

<script>
import Info from "./Tabs/Info.vue";
import Players from "./Tabs/Players.vue";
import Championships from "./Tabs/Championships.vue";
import Matches from "./Tabs/Matches.vue";
import Stats from "./Tabs/Stats.vue";
import Squad from "./Tabs/Squad.vue";
import DefaultLayout from "@/Layouts/DefaultLayout.vue";
import AddPlayerModal from "@/Components/AddPlayerModal.vue";
import {router} from '@inertiajs/vue3'

export default {
    components: {
        DefaultLayout,
        Info,
        Players,
        Championships,
        Matches,
        Stats,
        Squad,
        AddPlayerModal,
    },
    props: {
        team: Object, // Dados do time vindos do backend
    },
    data() {
        return {
            activeTab: "info", // Define a aba padrão como Players
            showAddPlayerModal: false, // Exibe ou fecha a modal de adicionar jogador
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
                squad: "Squad"
            };

            return tabComponents[this.activeTab] || "Info"; // Retorna Info como padrão
        },
    },
    methods: {
        async handleAddPlayer(playerName) {
            alert(`O jogador ${playerName} foi adicionado ao time ${this.team.name}!`);
            this.showAddPlayerModal = false;

            router.reload({only: ['team']});
        },
    },
};
</script>

<style scoped>
/* Container Geral */
.team-container {
    margin: 1rem;
    padding: 1rem;
}

.team-title {
    text-align: center;
    margin: 1rem auto; /* Adiciona espaço ao redor do título */
}

/* Container do botão */
.add-player-container {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 1rem;
}

/* Botão Adicionar Jogador */
.add-player-btn {
    background-color: #6a1b9a;
    color: white;
    font-weight: bold;
    font-size: 1rem;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s;
}

.add-player-btn:hover {
    background-color: #502c71;
    transform: scale(1.05);
}

/* Títulos */
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
    margin-bottom: 1rem;
}

.nav-tabs {
    display: flex; /* Garante que as tabs fiquem em uma linha */
    flex-wrap: nowrap; /* Evita que as tabs passem para outra linha */
    overflow-x: auto; /* Adiciona scroll horizontal no mobile */
    list-style: none;
    border-bottom: 2px solid #ddd;
    padding: 0.75rem; /* Espaço ao redor das tabs */
    gap: 0.5rem; /* Espaçamento entre tabs */
    margin-bottom: 1rem; /* Espaçamento inferior */
}

/* Estilo das Tabs */
.nav-item {
    flex: 1; /* Tabs ocupam o mesmo espaço/proporcional */
    text-align: center; /* Alinha o texto das tabs no centro */
    white-space: nowrap; /* Impede quebra de linha nas tabs */
}

/* Links das Tabs */
.nav-link {
    display: inline-block; /* Garante que os botões tenham padding uniforme */
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    color: #6a1b9a;
    font-size: 1rem;
    font-weight: bold;
    text-align: center;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s;
}

.nav-link:hover {
    background-color: #eaddf7;
    color: #5a1484;
}

.nav-link.active {
    background-color: #6a1b9a;
    color: white;
    border-color: #6a1b9a;
    transform: scale(1.05);
}

/* Conteúdo das Tabs */
.tab-content {
    padding: 1rem;
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

@media screen and (max-width: 768px) {
    .nav-link {
        font-size: 0.85rem; /* Diminui a fonte para telas menores */
        padding: 0.4rem 0.6rem; /* Ajusta o padding */
    }

    .nav-tabs {
        gap: 0.3rem; /* Reduz o espaçamento entre tabs */
    }
}
</style>
