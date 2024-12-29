<template>
    <!-- Overlay da modal -->
    <div
        class="modal-overlay d-flex align-items-center justify-content-center"
        tabindex="-1"
        role="dialog"
    >
        <!-- Elemento da Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modern-modal">
                    <!-- Header -->
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-primary">Editar Jogador</h5>
                        <button
                            type="button"
                            class="btn-close"
                            aria-label="Close"
                            @click="$emit('close')"
                        ></button>
                    </div>

                    <!-- Corpo -->
                    <div class="modal-body">
                        <p class="text-muted mb-4">
                            Por favor, selecione o novo time para o jogador <strong>{{ player.name }}</strong>.
                        </p>

                        <div class="mb-3">
                            <label for="team" class="form-label">Novo Time</label>
                            <select
                                v-model="selectedTeam"
                                id="team"
                                class="form-select shadow-sm"
                                required
                            >
                                <!-- Opção "Sem Clube" -->
                                <option value="null">Nenhum Clube</option>
                                <!-- Lista de times -->
                                <option v-for="team in teams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" @click="$emit('close')">
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary shadow-sm"
                            @click="updatePlayer"
                        >
                            Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    mounted() {
        this.getTeams();
    },
    props: {
        player: Object,
    },
    data() {
        return {
            selectedTeam: this.player.team_id === null ? "null" : this.player.team_id, // Inicializa como "null" se o jogador não tiver time
            teams: [], // Importante: Define um array vazio como valor inicial de 'teams'
        };
    },
    methods: {
        async getTeams() {
            // Busca lista de times da API
            axios
                .get(`/api/team`)
                .then((response) => {
                    this.teams = response.data.data;
                })
                .catch((err) => console.error(err));
        },
        updatePlayer() {
            // Transforma a string "null" em valor real null antes de enviar
            const newTeamId = this.selectedTeam === "null" ? null : this.selectedTeam;

            // Requisição para atualizar o Jogador
            axios
                .put(`/api/player/change-team`, {
                    new_team_id: newTeamId,
                    player_id: this.player.player_id,
                })
                .then(() => {
                    this.$emit("player-updated", {
                        ...this.player,
                        team_id: newTeamId,
                    });
                    this.$emit("close");
                })
                .catch((error) => {
                    console.error("Error updating player:", error);
                });
        },
    },
};
</script>

<style scoped>
/* Overlay (Fundo escuro) */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Fundo semi-transparente */
    z-index: 1050; /* Sobreposição sobre a interface */
}

/* Estilo Moderno da Modal */
.modern-modal {
    border-radius: 15px; /* Arredonda bordas */
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25); /* Sombra suave */
}

.modal-header {
    border-bottom: none; /* Remove linha inferior padrão */
}

.modal-footer {
    border-top: none; /* Remove linha superior padrão */
}

.modal-body {
    font-size: 0.95rem;
}

/* Estilo do botão padrão */
.btn {
    padding: 0.5rem 1.5rem;
    border-radius: 50px; /* Botões com bordas arredondadas */
}

/* Sombras para botões */
.shadow-sm {
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-primary {
    background-color: #6a1b9a;
    border: none;
}

.btn-primary:hover {
    background-color: #502c71;
}

.btn-secondary {
    color: #6a1b9a;
    background-color: #f3f3f3;
    border: none;
}

.btn-secondary:hover {
    color: white;
    background-color: #6a1b9a;
    border: none;
}
</style>
