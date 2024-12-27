<template>
    <div>
        <h2>Informações do Campeonato</h2>
        <ul>
            <li><strong>Nome:</strong> {{ championshipData.name }}</li>
            <li><strong>Descrição:</strong> {{ championshipData.description }}</li>
            <li><strong>Rodadas:</strong> {{ championshipData.rounds }}</li>
            <li><strong>Playoffs:</strong> {{ championshipData.playoffs ? "Sim" : "Não" }}</li>
            <li v-if="championshipData.started_at">
                <strong>Início:</strong> {{ formatDate(championshipData.started_at) }}
            </li>
            <li v-if="championshipData.finished_at">
                <strong>Término:</strong> {{ formatDate(championshipData.finished_at) }}
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        championshipData: Object, // Dados do campeonato
    },
    methods: {
        // Formatar datas
        formatDate(date) {
            if (!date) return "Não informado";
            const options = { year: "numeric", month: "long", day: "numeric" };
            return new Date(date).toLocaleDateString("pt-BR", options);
        },
    },
};
</script>
