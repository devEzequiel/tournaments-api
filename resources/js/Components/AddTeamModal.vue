<template>
    <div
        class="team-card shadow-sm"
        @click="openOptionsModal"
    >
        <!-- Escudo -->
        <div class="shield-wrapper">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 100 120"
                class="shield"
            >
                <!-- Borda preta do escudo -->
                <path
                    d="M50,0 L10,60 L50,120 L90,60 Z"
                    stroke="black"
                    stroke-width="2"
                    fill="none"
                />
                <!-- Metade superior esquerda (cor primária) -->
                <path
                    :fill="firstColor"
                    d="M50,0 L10,60 L50,120 Z"
                />
                <!-- Metade inferior direita (cor secundária) -->
                <path
                    :fill="secondColor"
                    d="M50,0 L90,60 L50,120 Z"
                />
            </svg>
        </div>

        <!-- Nome do Time -->
        <p class="team-name">{{ name }}</p>

        <!-- Modal de Opções -->
        <div
            v-if="showOptions"
            class="modal d-block"
            style="background: rgba(0, 0, 0, 0.5);"
            @click.self="showOptions = false"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Opções do Time</h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="showOptions = false"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <button class="btn btn-info mb-2 w-100" @click="viewTeam">Ver Time</button>
                        <button class="btn btn-primary w-100" @click="openEditModal">Editar Time</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Edição -->
        <div
            v-if="showEdit"
            class="modal d-block"
            style="background: rgba(0, 0, 0, 0.5);"
            @click.self="showEdit = false"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Time</h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="showEdit = false"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <!-- Formulário -->
                        <form @submit.prevent="updateTeam">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome</label>
                                <input
                                    type="text"
                                    id="name"
                                    class="form-control"
                                    v-model="teamData.name"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label for="primaryColor" class="form-label">Cor Primária</label>
                                <input
                                    type="color"
                                    id="primaryColor"
                                    class="form-control form-control-color"
                                    v-model="teamData.primary_color"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label for="secondaryColor" class="form-label">Cor Secundária</label>
                                <input
                                    type="color"
                                    id="secondaryColor"
                                    class="form-control form-control-color"
                                    v-model="teamData.secondary_color"
                                    required
                                />
                            </div>

                            <!-- Botões -->
                            <button type="submit" class="btn btn-success w-100">Salvar</button>
                            <button
                                type="button"
                                class="btn btn-secondary w-100 mt-2"
                                @click="showEdit = false"
                            >
                                Cancelar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: {
        name: {
            type: String,
            required: true,
        },
        teamId: {
            type: Number,
            required: true,
        },
        firstColor: {
            type: String,
            required: true,
        },
        secondColor: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            showOptions: false,
            showEdit: false,
            teamData: {
                name: "",
                primary_color: "",
                secondary_color: "",
            },
        };
    },
    methods: {
        // Abre a modal de opções
        openOptionsModal() {
            this.showOptions = true;
        },

        // Redireciona para a rota do time
        viewTeam() {
            this.$inertia.visit(`/teams/${this.name}`);
        },

        // Carrega os dados do time e abre a modal de edição
        async openEditModal() {
            this.showOptions = false; // Fecha a modal de opções
            try {
                const { data } = await axios.get(`/api/teams/${this.teamId}`);
                this.teamData = {
                    name: data.name,
                    primary_color: data.primary_color,
                    secondary_color: data.secondary_color,
                };
                this.showEdit = true; // Abre a modal de edição
            } catch (error) {
                console.error("Erro ao carregar os dados do time:", error);
            }
        },

        // Atualiza os dados do time na API
        async updateTeam() {
            try {
                await axios.put(`/api/teams/${this.teamId}`, {
                    name: this.teamData.name,
                    primary_color: this.teamData.primary_color,
                    secondary_color: this.teamData.secondary_color,
                });
                this.showEdit = false;
                // Atualize os dados no frontend, se necessário
                this.$emit("team-updated", this.teamData);
            } catch (error) {
                console.error("Erro ao atualizar o time:", error);
            }
        },
    },
};
</script>

<style scoped>
.team-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px;
    border: 1px solid #ddd;
    cursor: pointer;
    background: #fff;
    transition: background 0.3s;
}

.team-card:hover {
    background: rgba(0, 0, 0, 0.05);
}

.shield-wrapper {
    width: 50px;
    height: 60px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 15px;
}

.team-name {
    font-size: 1rem;
    font-weight: bold;
    color: #333;
    flex: 1;
}
</style>
