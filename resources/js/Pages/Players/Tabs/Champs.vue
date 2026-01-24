<template>
    <div class="champs-tab">
        <h3>Championships</h3>
        <p>Conteúdo da aba Champs.</p>
    </div>
</template>

<script>
export default {
    data() {
        return {};
    },
};
</script>

<style scoped>
.champs-tab {
    padding: 1rem;
    text-align: center;
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
</style>
