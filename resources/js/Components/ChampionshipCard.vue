<template>
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <!-- Título -->
            <h5 class="card-title" style="color: #6a1b9a; font-weight: bold;">{{ title }}</h5>

            <!-- Descrição -->
            <p class="card-text text-muted">{{ description }}</p>

            <!-- Informações adicionais -->
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <strong>Rodadas:</strong> {{ rounds }}
                </li>
                <li class="list-group-item">
                    <strong>Status:</strong>
                    <span
                        :class="finished_at ? 'text-success' : 'text-warning'"
                    >
                        {{ finished_at ? 'Finalizado' : 'Em andamento' }}
                    </span>
                </li>
                <li class="list-group-item">
                    <strong>Playoffs:</strong>
                    {{ playoffs ? `${playoff_rounds || 0} Rodadas` : 'Não há playoffs' }}
                </li>
                <li class="list-group-item">
                    <strong>Início:</strong> {{ formatDate(started_at) }}
                </li>
                <li v-if="finished_at" class="list-group-item">
                    <strong>Fim:</strong> {{ formatDate(finished_at) }}
                </li>
            </ul>

            <!-- Botão de ação -->
            <div class="mt-3">
                <a :href="link" class="btn btn-outline-primary btn-sm">Ver detalhes</a>
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
        link: String, // URL para redirecionar, exemplo: detalhes do campeonato
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
.card-title {
    font-size: 1.25rem;
}

.list-group-item {
    font-size: 0.9rem;
}

/* Botões */
.btn-outline-primary {
    color: #6a1b9a;
    border-color: #6a1b9a;
}
.btn-outline-primary:hover {
    background-color: #6a1b9a;
    color: #fff;
}
.text-success {
    color: #28a745 !important;
}
.text-warning {
    color: #ffc107 !important;
}
</style>
