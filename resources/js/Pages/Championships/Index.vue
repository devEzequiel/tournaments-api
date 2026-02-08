<template>
    <DefaultLayout>
        <div class="championships-container">
            <!-- Header da página -->
            <div class="page-header">
                <div class="header-content">
                    <h1 class="page-title">Meus Campeonatos</h1>
                    <p class="page-subtitle">Gerencie suas ligas e torneios</p>
                </div>
                
                <AddModal
                    :teams="teams"
                    @reload="reloadPage"
                />
            </div>

            <!-- Lista de Campeonatos -->
            <div v-if="championships.length === 0" class="empty-state">
                <div class="empty-content">
                    <i class="fas fa-trophy empty-icon"></i>
                    <h3>Nenhum campeonato encontrado</h3>
                    <p>Comece criando seu primeiro campeonato para gerenciar partidas e estatísticas.</p>
                </div>
            </div>

            <div v-else class="championships-grid">
                <div
                    v-for="championship in championships"
                    :key="championship.id"
                    class="grid-item"
                >
                    <Card
                        :title="championship.name"
                        :description="championship.description"
                        :rounds="championship.rounds"
                        :status="championship.finished_at ? 'Finalizado' : 'Em andamento'"
                        :playoffs="championship.playoffs"
                        :playoff_rounds="championship.playoff_rounds"
                        :started_at="championship.started_at"
                        :finished_at="championship.finished_at"
                        :link="`/championships/${championship.name.replace(/\s+/g, '-').toLowerCase()}`"
                        :championship-id="championship.id"
                        :champion="getChampionName(championship)"
                        @delete="handleDelete"
                    />
                </div>
            </div>
        </div>
    </DefaultLayout>
</template>

<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import AddModal from '@/Components/AddChampionshipModal.vue';
import Card from '@/Components/ChampionshipCard.vue';
import { useToast } from "@/Composables/useToast";
import axios from "axios";

export default {
    components: {
        DefaultLayout,
        AddModal,
        Card,
    },
    props: {
        championships: Array,
        teams: Array,
    },
    data() {
        return {
            toast: useToast(),
        };
    },
    methods: {
        getChampionName(championship) {
            // Verifica se tem awards e first_place definido
            if (championship.awards && championship.awards.length > 0) {
                const award = championship.awards[0];
                if (award.get_first_place && award.get_first_place.name) {
                    return award.get_first_place.name;
                }
            }
            return null;
        },
        async reloadPage() {
            this.$inertia.reload();
        },
        async handleDelete(championshipId) {
            try {
                await axios.delete(`/api/championship/${championshipId}`);
                this.toast.success('Campeonato deletado com sucesso');
                this.reloadPage();
            } catch (error) {
                console.error('Erro ao deletar campeonato:', error);
                this.toast.error('Erro ao deletar campeonato');
            }
        },
    },
};
</script>

<style scoped>
.championships-container {
    padding: 2rem 1rem;
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3rem;
    flex-wrap: wrap;
    gap: 1rem;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 1.5rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 800;
    color: #1a202c;
    margin: 0;
    letter-spacing: -0.025em;
}

.page-subtitle {
    font-size: 1rem;
    color: #718096;
    margin: 0.25rem 0 0 0;
}

.empty-state {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
    background: #f8fafc;
    border-radius: 16px;
    border: 2px dashed #e2e8f0;
}

.empty-content {
    text-align: center;
    color: #a0aec0;
    max-width: 400px;
    padding: 2rem;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    color: #cbd5e0;
}

.championships-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 1.5rem;
}

.grid-item {
    display: flex;
}

@media (min-width: 768px) {
    .championships-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .championships-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 640px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
