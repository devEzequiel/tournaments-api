<template>
    <DefaultLayout>
        <div class="team-container">
            <div class="add-player-container">
                <button class="add-player-btn" @click="showAddPlayerModal = true">
                    Adicionar Jogador
                </button>
            </div>

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
                <li class="nav-item">
                    <button
                        class="nav-link"
                        :class="{ active: activeTab === 'squad' }"
                        @click="activeTab = 'squad'"
                    >
                        Squad
                    </button>
                </li>
            </ul>

            <div class="tab-content mt-4">
                <Transition name="fade" mode="out-in">
                    <component :is="activeTabComponent" :key="activeTab" :team="team"/>
                </Transition>
            </div>

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
import { useToast } from "@/Composables/useToast";

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
            activeTab: localStorage.getItem('teamActiveTab') || "info",
            showAddPlayerModal: false, // Exibe ou fecha a modal de adicionar jogador
            toast: useToast(),
        };
    },
    watch: {
        activeTab(newTab) {
            localStorage.setItem('teamActiveTab', newTab);
        }
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
            this.showAddPlayerModal = false;
            router.reload({only: ['team']});
        },
    },
};
</script>

<style scoped>
.team-container {
    margin: 1rem;
    padding: 1rem;
}

.team-title {
    text-align: center;
    margin: 1rem auto;
}

.add-player-container {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 1rem;
}

.add-player-btn {
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
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
    transform: translateY(-1px) scale(1.02);
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
    margin-bottom: 1rem;
}

.nav-tabs {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    list-style: none;
    border-bottom: 1px solid var(--color-border);
    padding: 0.5rem;
    gap: 0.5rem;
    margin-bottom: 1rem;
    scrollbar-width: none;
}

.nav-tabs::-webkit-scrollbar {
    display: none;
}

.nav-item {
    flex: 0 0 auto;
    text-align: center;
    white-space: nowrap;
}

.nav-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: transparent;
    border: 1px solid transparent;
    border-radius: 999px;
    padding: 0.5rem 0.9rem;
    color: var(--color-text);
    font-size: 0.95rem;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s;
}

.nav-link:hover {
    background-color: rgba(79, 70, 229, 0.08);
    color: var(--color-primary);
}

.nav-link.active {
    background-color: rgba(79, 70, 229, 0.15);
    color: var(--color-primary);
    border-color: rgba(79, 70, 229, 0.2);
    transform: translateY(-1px);
}

.tab-content {
    padding: 1rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: 14px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
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

@media screen and (max-width: 768px) {
    .team-container {
        margin: 0.5rem;
        padding: 0.5rem;
    }

    .team-title {
        font-size: 2rem;
    }

    .nav-link {
        font-size: 0.85rem;
        padding: 0.4rem 0.65rem;
    }

    .nav-tabs {
        gap: 0.3rem;
        padding: 0.35rem;
    }
}
</style>
