<template>
    <DefaultLayout>
        <div class="tabs-container">
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

            <div class="tab-content">
                <Transition name="fade" mode="out-in">
                    <component :is="activeTabComponent" :key="activeTab" />
                </Transition>
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
            activeTab: localStorage.getItem('playerActiveTab') || "stats",
        };
    },
    watch: {
        activeTab(newTab) {
            localStorage.setItem('playerActiveTab', newTab);
        }
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
    gap: 0.5rem;
    margin-bottom: 1rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.tabs-navigation::-webkit-scrollbar {
    display: none;
}

.tab-button {
    margin-right: 0;
    padding: 0.5rem 0.95rem;
    border: 1px solid transparent;
    background: transparent;
    color: var(--color-text);
    cursor: pointer;
    font-weight: 600;
    border-radius: 999px;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.tab-button.active {
    background-color: rgba(79, 70, 229, 0.15);
    color: var(--color-primary);
    border-color: rgba(79, 70, 229, 0.2);
}

.tab-content {
    padding: 1rem;
    border: 1px solid var(--color-border);
    border-radius: 14px;
    background: var(--color-surface);
    min-height: 400px;
}

@media (max-width: 768px) {
    .tabs-container {
        margin: 0.5rem;
    }

    .tab-button {
        font-size: 0.85rem;
        padding: 0.4rem 0.75rem;
    }
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
</style>
