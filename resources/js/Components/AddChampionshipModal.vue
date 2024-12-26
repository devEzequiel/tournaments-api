<template>
    <div>
        <!-- Botão para abrir a modal -->
        <button
            class="btn btn-primary my-4"
            @click="showModal = true"
        >
            + Adicionar Campeonato
        </button>

        <!-- Modal -->
        <div
            v-if="showModal"
            class="modal d-block"
            style="background: rgba(0, 0, 0, 0.5);"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Adicionar Campeonato</h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="closeModal"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <!-- Formulário -->
                        <form @submit.prevent="submitForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome</label>
                                <input
                                    type="text"
                                    id="name"
                                    class="form-control"
                                    v-model="form.name"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <textarea
                                    id="description"
                                    class="form-control"
                                    v-model="form.description"
                                    required
                                ></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="rounds" class="form-label">Número de Rodadas</label>
                                <input
                                    type="number"
                                    id="rounds"
                                    class="form-control"
                                    v-model="form.rounds"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label for="teams" class="form-label">Times</label>
                                <select
                                    id="teams"
                                    class="form-select"
                                    multiple
                                    v-model="form.teams"
                                >
                                    <option v-for="team in teams" :key="team.id" :value="team.id">
                                        {{ team.name }}
                                    </option>
                                </select>
                                <small class="text-muted">Segure Ctrl (ou Cmd) para selecionar múltiplos times.</small>
                            </div>

                            <div class="mb-3">
                                <label for="playoffs" class="form-label">Playoffs</label>
                                <select
                                    id="playoffs"
                                    class="form-select"
                                    v-model="form.playoffs"
                                >
                                    <option :value="false">Não</option>
                                    <option :value="true">Sim</option>
                                </select>
                            </div>

                            <!-- Botão de confirmação -->
                            <button type="submit" class="btn btn-success">Salvar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        teams: Array, // Lista de times passada via prop
    },
    data() {
        return {
            showModal: false,
            form: {
                name: '',
                description: '',
                rounds: 4,
                teams: [],
                playoffs: false
            }
        };
    },
    methods: {
        closeModal() {
            this.showModal = false;
            this.resetForm();
        },
        resetForm() {
            this.form = {
                name: '',
                description: '',
                rounds: 2,
                teams: [],
                playoffs: false
            };
        },
        async submitForm() {
            try {
                // Envia os dados para a API
                await axios.post('/api/championship', this.form);

                // Exibe o toast
                this.$emit('toast', 'Novo campeonato adicionado com sucesso!');

                // Fecha a modal
                this.closeModal();

                // Recarrega a página
                this.$emit('reload');
            } catch (error) {
                console.error(error);
                this.$emit('toast', 'Erro ao adicionar campeonato.');
            }
        },
    },
};
</script>

<style scoped>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
</style>
