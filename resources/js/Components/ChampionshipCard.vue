<template>
    <div class="championship-card" :class="{ 'has-champion': champion }">
        <!-- Banner de Campeão -->
        <div v-if="champion" class="champion-banner">
            <i class="fas fa-trophy"></i>
            <span>{{ champion }}</span>
        </div>
        
        <div class="card-header-custom">
            <h3 class="card-title" :title="title">{{ title }}</h3>
            <button class="btn-delete" @click.stop="showDeleteModal = true" title="Deletar campeonato">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>

        <div class="card-body-custom">
            <p class="description" v-if="description">{{ description }}</p>
            <p class="description placeholder" v-else>Sem descrição.</p>

            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Status</span>
                    <span class="value badge" :class="finished_at ? 'badge-finished' : 'badge-active'">
                        {{ finished_at ? 'Finalizado' : 'Em andamento' }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="label">Rodadas</span>
                    <span class="value">{{ rounds }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Tipo</span>
                    <span class="value">{{ playoffs ? 'Mata-mata' : 'Pontos corridos' }}</span>
                </div>
            </div>

            <div class="dates">
                <small>Início: {{ formatDate(started_at) }}</small>
                <small v-if="finished_at">Fim: {{ formatDate(finished_at) }}</small>
            </div>
        </div>

        <div class="card-footer-custom">
            <a :href="link" class="btn-details">
                Acessar
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <!-- Modal de Deletar -->
        <DeleteChampionshipModal
            :show="showDeleteModal"
            :championship-name="title"
            :championship-id="championshipId"
            @close="showDeleteModal = false"
            @confirm="confirmDelete"
        />
    </div>
</template>

<script>
import DeleteChampionshipModal from "./DeleteChampionshipModal.vue";

export default {
    components: {
        DeleteChampionshipModal,
    },
    props: {
        title: String,
        description: String,
        rounds: Number,
        status: String,
        playoffs: Number,
        playoff_rounds: [Number, null],
        started_at: String,
        finished_at: [String, null],
        link: String,
        championshipId: Number,
        champion: [String, null], // Nome do time campeão
    },
    data() {
        return {
            showDeleteModal: false,
        };
    },
    methods: {
        formatDate(date) {
            if (!date) return '---';
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(date).toLocaleDateString('pt-BR', options);
        },
        confirmDelete() {
            this.$emit('delete', this.championshipId);
            this.showDeleteModal = false;
        },
    },
};
</script>

<style scoped>
.championship-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border: 1px solid #edf2f7;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.championship-card.has-champion {
    border: 2px solid #f6ad55;
    box-shadow: 0 4px 6px -1px rgba(246, 173, 85, 0.3), 0 2px 4px -1px rgba(246, 173, 85, 0.2);
}

/* Banner de Campeão */
.champion-banner {
    background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
    color: white;
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.champion-banner i {
    font-size: 1rem;
    animation: trophy-shine 2s ease-in-out infinite;
}

@keyframes trophy-shine {
    0%, 100% {
        transform: rotate(0deg);
        opacity: 1;
    }
    50% {
        transform: rotate(-15deg) scale(1.1);
        opacity: 0.8;
    }
}

.championship-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.card-header-custom {
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background: #f7fafc;
    border-bottom: 1px solid #edf2f7;
}

.card-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-width: 85%;
}

.btn-delete {
    background: transparent;
    border: none;
    color: #cbd5e0;
    cursor: pointer;
    font-size: 1rem;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
}

.btn-delete:hover {
    color: #e53e3e;
    background-color: #fff5f5;
    transform: scale(1.1);
}

.card-body-custom {
    padding: 1.5rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    background-color: white;
}

.description {
    font-size: 0.875rem;
    color: #718096;
    margin-bottom: 1.5rem;
    line-height: 1.5;
    flex-grow: 1;
}

.description.placeholder {
    color: #a0aec0;
    font-style: italic;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #a0aec0;
    margin-bottom: 0.25rem;
    font-weight: 700;
}

.value {
    font-size: 0.95rem;
    font-weight: 600;
    color: #4a5568;
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    width: fit-content;
    height: fit-content;
}

.badge-finished {
    background-color: #c6f6d5;
    color: #22543d;
}

.badge-active {
    background-color: #ebf8ff;
    color: #2c5282;
}

.dates {
    border-top: 1px solid #edf2f7;
    padding-top: 1rem;
    margin-top: auto;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: #a0aec0;
}

.card-footer-custom {
    padding: 1rem 1.5rem;
    background: #ffffff;
    border-top: 1px solid #edf2f7;
}

.btn-details {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem;
    background-color: #4299e1;
    color: white;
    text-decoration: none;
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.btn-details:hover {
    background-color: #3182ce;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(66, 153, 225, 0.4);
}

.btn-details i {
    font-size: 0.875rem;
    transition: transform 0.2s;
}

.btn-details:hover i {
    transform: translateX(4px);
}
</style>
