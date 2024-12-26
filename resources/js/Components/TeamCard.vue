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
            class="modal-backdrop"
            @click.self="showOptions = false"
        >
            <div class="modal-content">
                <h3>Opções do Time</h3>
                <button class="modal-btn" @click="viewTeam">Ver Time</button>
                <button class="modal-btn" @click="openEditModal">Editar Time</button>
                <button class="modal-close-btn" @click="showOptions = false">Fechar</button>
            </div>
        </div>

        <!-- Modal de Edição -->
        <div
            v-if="showEdit"
            class="modal-backdrop"
            @click.self="showEdit = false"
        >
            <div class="modal-content">
                <h3>Editar Time</h3>
                <form @submit.prevent="updateTeam">
                    <div>
                        <label for="team-name">Nome do Time:</label>
                        <input id="team-name" v-model="teamData.name" required />
                    </div>
                    <div>
                        <label for="team-color-1">Cor Primária:</label>
                        <input id="team-color-1" v-model="teamData.primary_color" type="color" />
                    </div>
                    <div>
                        <label for="team-color-2">Cor Secundária:</label>
                        <input id="team-color-2" v-model="teamData.secondary_color" type="color" />
                    </div>
                    <div>
                        <button type="submit">Salvar</button>
                        <button type="button" @click="showEdit = false">Cancelar</button>
                    </div>
                </form>
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
            this.showOptions = false;
            try {
                const { data } = await axios.get(`/api/team/${this.teamId}/detail`);
                this.teamData = {
                    name: data.name,
                    primary_color: data.primary_color,
                    secondary_color: data.secondary_color,
                };
                this.showEdit = true;
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
                // Atualize os dados no frontend se necessário
                this.$emit("team-updated", this.teamData);
            } catch (error) {
                console.error("Erro ao atualizar o time:", error);
            }
        },
    },
};
</script>

<style scoped>
/* Geral do card */
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

/* Escudo */
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

/* Estilização do modal */
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
    border-radius: 5px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    text-align: center;
}
.modal-content h3 {
    margin-bottom: 20px;
}
.modal-btn {
    display: block;
    margin: 10px auto;
    padding: 10px 15px;
    font-size: 1rem;
    color: #fff;
    background: #6c63ff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.modal-btn:hover {
    background: #574ce0;
}
.modal-close-btn {
    margin-top: 10px;
    color: #333;
    background: transparent;
    border: none;
    font-size: 0.9rem;
    cursor: pointer;
}
</style>
