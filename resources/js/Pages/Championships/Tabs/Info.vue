<template>
    <div class="info-container">
        <div class="info-content">
            <div class="info-item">
                <span class="info-label">Descrição:</span>
                <span class="info-value">{{ championshipData.description }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Rodadas:</span>
                <span class="info-value">{{ championshipData.rounds }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Playoffs:</span>
                <span class="info-value">
                    {{ championshipData.playoffs ? "Sim" : "Não" }}
                </span>
            </div>
            <div
                class="info-item"
                v-if="championshipData.started_at"
            >
                <span class="info-label">Início:</span>
                <span class="info-value">
                    {{ formatDate(championshipData.started_at) }}
                </span>
            </div>
            <div
                class="info-item"
                v-if="championshipData.finished_at"
            >
                <span class="info-label">Término:</span>
                <span class="info-value">
                    {{ formatDate(championshipData.finished_at) }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        championshipData: Object, // Dados do campeonato
    },
    methods: {
        // Formata datas
        formatDate(date) {
            if (!date) return "Não informado";
            const options = {year: "numeric", month: "long", day: "numeric"};
            return new Date(date).toLocaleDateString("pt-BR", options);
        },
    },
};
</script>

<style scoped>
.info-container {
    background-color: #FFFFFF;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.info-container:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

.info-header h2 {
    font-size: 1.8rem;
    font-weight: bold;
    color: #6A1B9A;
    display: inline-block;
    padding-bottom: 5px;
    border-bottom: 3px solid #6A1B9A;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #e5e5e5;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 1rem;
    font-weight: bold;
    color: #333;
}

.info-value {
    font-size: 1rem;
    font-weight: normal;
    color: #6A1B9A;
    text-align: right;
    max-width: 70%;
    word-wrap: break-word;
}

.info-value:not(:last-child) {
    font-size: 1rem;
}
</style>
