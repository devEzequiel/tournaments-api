<template>
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title" style="color: #6a1b9a;">{{ title }}</h5>
            <p class="card-text text-muted">{{ description }}</p>
            <a href="#" class="btn btn-outline-primary btn-sm">Ver mais</a>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: String,
        description: String,
    },
};
</script>

<style scoped>
.card-title {
    font-weight: bold;
}
.btn-outline-primary {
    color: #6a1b9a;
    border-color: #6a1b9a;
}
.btn-outline-primary:hover {
    background-color: #6a1b9a;
    color: #fff;
}
</style>
