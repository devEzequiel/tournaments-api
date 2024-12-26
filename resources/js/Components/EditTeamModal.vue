<template>
    <div v-if="show" class="modal-backdrop" @click.self="close">
        <div class="modal-content">
            <h3 class="modal-title">Editar Time</h3>
            <form @submit.prevent="saveTeam">
                <div class="mb-3">
                    <label for="team-name" class="form-label">Nome do Time</label>
                    <input
                        type="text"
                        id="team-name"
                        v-model="form.name"
                        class="form-control"
                        placeholder="Digite o nome do time"
                        required
                    />
                </div>
                <div class="mb-3">
                    <label for="primary-color" class="form-label">Cor Primária</label>
                    <input
                        type="color"
                        id="primary-color"
                        v-model="form.primary_color"
                        class="form-control form-control-color"
                        required
                    />
                </div>
                <div class="mb-3">
                    <label for="secondary-color" class="form-label">Cor Secundária</label>
                    <input
                        type="color"
                        id="secondary-color"
                        v-model="form.secondary_color"
                        class="form-control form-control-color"
                        required
                    />
                </div>

                <!-- Botões -->
                <button type="submit" class="btn btn-success w-100">Salvar</button>
                <button
                    type="button"
                    class="btn btn-secondary w-100 mt-2"
                    @click="close"
                >
                    Cancelar
                </button>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        team: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            form: {
                name: this.team.name,
                primary_color: this.team.primary_color,
                secondary_color: this.team.secondary_color,
            },
        };
    },
    watch: {
        // Atualiza o formulário toda vez que o time é alterado
        team(newTeam) {
            this.form = {...newTeam};
        },
    },
    methods: {
        saveTeam() {
            // Emite os dados salvos para o componente pai
            this.$emit("save", this.form);
        },
        close() {
            this.$emit("close");
        },
    },
};
</script>

<style scoped>
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3);
    text-align: center;
}

.modal-title {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 20px;
}

.btn {
    cursor: pointer;
}
</style>
