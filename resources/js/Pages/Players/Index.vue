<template>
    <DefaultLayout>
        <div class="tabs-container">
            <!-- Navegação entre abas -->
            <div class="tabs-navigation">
                <button
                    class="tab-button"
                    :class="{ active: activeTab === 'stats' }"
                    @click="activeTab = 'stats'"
                >
                    Stats
                </button>
                <button
                    class="tab-button"
                    :class="{ active: activeTab === 'champs' }"
                    @click="activeTab = 'champs'"
                >
                    Champs
                </button>
            </div>

            <!-- Carrega o componente da aba ativa -->
            <div class="tab-content">
                <component :is="activeTabComponent" />
            </div>
        </div>
    </DefaultLayout>
</template>

<script>
import DefaultLayout from "@/Layouts/DefaultLayout.vue"; // Importa o layout padrão
import Stats from "./Tabs/Stats.vue";
import Champs from "./Tabs/Champs.vue";

export default {
    components: {
        DefaultLayout,
        Stats,
        Champs,
    },
    data() {
        return {
            activeTab: "stats", // Aba padrão
        };
    },
    computed: {
        activeTabComponent() {
            if (this.activeTab === "stats") return "Stats";
            if (this.activeTab === "champs") return "Champs";
            return null;
        },
    },
};
</script>

<style scoped>
.tabs-container {
    margin: 1rem;
}

.tabs-navigation {
    display: flex;
    margin-bottom: 1rem;
}

.tab-button {
    margin-right: 1rem;
    padding: 0.5rem 1rem;
    border: 1px solid #6a1b9a;
    background: #f9f9f9;
    color: #6a1b9a;
    cursor: pointer;
    font-weight: bold;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.tab-button.active {
    background-color: #6a1b9a;
    color: white;
}

.tab-content {
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #ffffff;
}
</style>
