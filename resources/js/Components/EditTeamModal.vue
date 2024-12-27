<template>
    <div>
        <div
            v-if="showModal"
            ref="editTeamModal"
            class="modal d-block"
            style="background: rgba(0, 0, 0, 0.5);"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Time</h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="closeModal"
                        ></button>
                    </div>
                    <div v-if="isLoading">
                        <p>Carregando...</p>
                    </div>
                    <div v-else class="modal-body">
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
                                <label for="firstColor" class="form-label">Cor Primária</label>
                                <input
                                    type="color"
                                    id="firstColor"
                                    class="form-control form-control-color"
                                    v-model="form.first_color"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label for="secondColor" class="form-label">Cor Secundária</label>
                                <input
                                    type="color"
                                    id="secondColor"
                                    class="form-control form-control-color"
                                    v-model="form.second_color"
                                    required
                                />
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
        teamId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            showModal: false,
            isLoading: false, // Novo estado para controlar o carregamento
            form: {
                name: '',
                first_color: '#000000',
                second_color: '#FFFFFF',
            },
        };
    },
    methods: {
        async openModal() {
            if (!this.teamId) {
                this.$emit('toast', 'Erro: Nenhum time identificado para edição.');
                return;
            }

            try {
                // Indica que estamos carregando os dados
                this.isLoading = true;
                this.showModal = true; // Mostrar modal, mesmo que com indicador de carregamento
                this.resetForm(); // Limpa dados antigos antes de carregar os novos
                await this.loadTeamDetails(); // Aguarda os dados serem carregados
            } catch (error) {
                this.$emit('toast', 'Erro ao carregar os dados do time.');
                this.showModal = false; // Fecha o modal se falhar no carregamento
            } finally {
                this.isLoading = false; // Removemos o indicador de carregamento ao finalizar
            }
        },
        closeModal() {
            this.showModal = false;
            this.resetForm();
        },
        resetForm() {
            this.form = {
                name: '',
                first_color: '#000000',
                second_color: '#FFFFFF',
            };
        },
        async loadTeamDetails() {
            try {
                const response = await axios.get(`/api/team/${this.teamId}/detail`);

                // Extraindo dados de 'response.data.data'
                const teamData = response.data.data;

                // Atualizando os valores no formulário
                this.form = {
                    name: teamData.name,
                    first_color: teamData.first_color,
                    second_color: teamData.second_color,
                };

            } catch (error) {
                throw error; // Levanta o erro para ser tratado
            }
        },
        async submitForm() {
            try {
                const response = await axios.put(`/api/team/${this.teamId}`, this.form);
                this.$emit('toast', 'Time atualizado com sucesso!');
                this.closeModal();
                this.$emit('reload'); // Dispara o evento para atualizar a listagem
            } catch (error) {W
                this.$emit('toast', 'Erro ao atualizar o time.');
            }
        }
    }
};
</script>
