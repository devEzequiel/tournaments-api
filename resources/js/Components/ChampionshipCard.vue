<template>
    <div class="modern-card">
        <div class="card-header">
            <h5 class="card-title">{{ title }}</h5>
        </div>
        <div class="card-body">
            <!-- Descrição -->
            <p class="card-text">{{ description }}</p>

            <ul class="card-info">
                <li><strong>Rodadas:</strong> {{ rounds }}</li>
                <li>
                    <strong>Status:</strong>
                    <span :class="finished_at ? 'text-success' : 'text-warning'">
                        {{ finished_at ? 'Finalizado' : 'Em andamento' }}
                    </span>
                </li>
                <li>
                    <strong>Playoffs:</strong>
                    {{ playoffs ? `${playoff_rounds || 0} Rodadas` : 'Não há playoffs' }}
                </li>
                <li><strong>Início:</strong> {{ formatDate(started_at) }}</li>
                <li v-if="finished_at"><strong>Fim:</strong> {{ formatDate(finished_at) }}</li>
            </ul>

            <div class="card-footer">
                <a :href="link" class="modern-btn">Ver detalhes</a>
            </div>
        </div>
    </div>
</template>

<script>
export default {
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
    },
    methods: {
        formatDate(date) {
            if (!date) return 'Não informado';
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(date).toLocaleDateString('pt-BR', options);
        },
    },
};
</script>

<style scoped>
/* Modern Card Styles */
.modern-card {
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.modern-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

/* Card Header */
.card-header {
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 0.75rem;
    margin-bottom: 1rem;
    text-align: center;
}

.card-title {
    font-size: 1.5rem;
    color: #6a1b9a;
    font-weight: bold;
    line-height: 1.2;
}

/* Card Texto */
.card-text {
    font-size: 1rem;
    color: #666;
    margin-bottom: 1rem;
    text-align: center;
}

/* Card Informações */
.card-info {
    list-style: none;
    padding: 0;
    margin: 0 0 1rem;
    font-size: 0.95rem;
}

.card-info li {
    margin: 0.5rem 0;
    color: #333;
}

/* Status */
.text-success {
    color: #28a745 !important;
    font-weight: bold;
}

.text-warning {
    color: #ffc107 !important;
    font-weight: bold;
}

/* Botão Moderno */
.modern-btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    color: #ffffff;
    background: linear-gradient(45deg, #6a1b9a, #8e44ad);
    border: none;
    text-decoration: none;
    font-size: 1rem;
    font-weight: bold;
    border-radius: 25px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    transition: background 0.3s, transform 0.2s ease;
}

.modern-btn:hover {
    background: linear-gradient(45deg, #8e44ad, #6a1b9a);
    transform: translateY(-3px);
    box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.15);
}

/* Responsividade */
@media (max-width: 768px) {
    .modern-card {
        padding: 1rem;
    }

    .card-title {
        font-size: 1.25rem;
    }

    .modern-btn {
        font-size: 0.95rem;
        padding: 0.5rem 1.25rem;
    }
}
</style>
