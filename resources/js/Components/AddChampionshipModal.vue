<template>
    <div>
        <!-- Botão para abrir a modal -->
        <button
            class="modern-btn mb-5"
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
                            <!-- Nome -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Título</label>
                                <input
                                    type="text"
                                    id="name"
                                    class="form-control"
                                    v-model="form.name"
                                    required
                                />
                            </div>

                            <!-- Descrição -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <textarea
                                    id="description"
                                    class="form-control"
                                    v-model="form.description"
                                    required
                                ></textarea>
                            </div>

                            <!-- Número de Rodadas -->
                            <div class="mb-3">
                                <label for="rounds" class="form-label">Número de Rodadas</label>
                                <input
                                    type="number"
                                    id="rounds"
                                    class="form-control"
                                    v-model="form.rounds"
                                    min="1"
                                    required
                                />
                            </div>

                            <!-- Seleção Única de Times -->
                            <div class="mb-3">
                                <label for="teamSelector" class="form-label">Selecione os Times</label>
                                <select
                                    id="teamSelector"
                                    class="form-select"
                                    v-model="selectedTeam"
                                    @change="addTeam"
                                >
                                    <option value="" disabled>Selecione um time</option>
                                    <option
                                        v-for="team in availableTeams"
                                        :key="team.id"
                                        :value="team.id"
                                    >
                                        {{ team.name }}
                                    </option>
                                </select>
                                <small class="text-muted">Os times selecionados serão exibidos abaixo.</small>
                            </div>

                            <!-- Lista de Times Selecionados -->
                            <div class="mb-3">
                                <label class="form-label">Times Selecionados</label>
                                <div class="team-list">
                                    <div
                                        v-for="(team, index) in form.teams"
                                        :key="team.id"
                                        class="team-item"
                                    >
                                        <span>{{ index + 1 }}. {{ team.name }}</span>
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm ms-2"
                                            @click="removeTeam(team.id)"
                                        >
                                            Remover
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Playoffs -->
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

                            <!-- Botão de Confirmação -->
                            <button type="submit" class="btn btn-success">
                                Salvar
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
    data() {
        return {
            showModal: false, // Controla a visibilidade da modal
            teams: [], // Times obtidos da API
            selectedTeam: "", // Time selecionado no dropdown
            form: {
                name: "",
                description: "",
                rounds: 2,
                playoffs: false,
                teams: [], // Armazena os times selecionados
            },
        };
    },
    computed: {
        // Retorna os times disponíveis (excluindo os já selecionados)
        availableTeams() {
            return this.teams.filter(
                (team) => !this.form.teams.some((selected) => selected.id === team.id)
            );
        },
    },
    methods: {
        // Fecha a modal e reseta os dados
        closeModal() {
            this.showModal = false;
            this.resetForm();
        },
        resetForm() {
            this.form = {
                name: "",
                description: "",
                rounds: 2,
                playoffs: false,
                teams: [],
            };
            this.selectedTeam = "";
        },
        // Obtém os times da API ao montar o componente
        async fetchTeams() {
            try {
                const response = await axios.get("/api/team");
                if (response.data && response.data.data) {
                    this.teams = response.data.data; // Armazena os times obtidos da API
                }
            } catch (error) {
                console.error("Erro ao buscar times da API:", error);
            }
        },
        // Adiciona o time selecionado à lista de times
        addTeam() {
            if (this.selectedTeam) {
                const teamToAdd = this.teams.find(
                    (team) => team.id === parseInt(this.selectedTeam)
                );
                if (teamToAdd && !this.form.teams.some((t) => t.id === teamToAdd.id)) {
                    this.form.teams.push(teamToAdd);
                }
                this.selectedTeam = ""; // Reseta o valor do select
            }
        },
        // Remove um time da lista de times selecionados
        removeTeam(teamId) {
            this.form.teams = this.form.teams.filter((team) => team.id !== teamId);
        },
        // Submete o formulário
        async submitForm() {
            try {
                // Prepara payload
                const payload = {
                    ...this.form,
                    teams: this.form.teams.map((team) => team.id), // Envia apenas IDs dos times
                };

                await axios.post("/api/championship", payload);

                // Exibe mensagem de sucesso
                this.$emit("toast", "Novo campeonato adicionado com sucesso!");

                // Fecha a modal e reseta o formulário
                this.closeModal();

                // Atualiza a página ou tabela principal
                this.$emit("reload");
            } catch (error) {
                console.error(error);
                this.$emit("toast", "Erro ao adicionar campeonato.");
            }
        },
    },
    mounted() {
        this.fetchTeams(); // Busca os times ao inicializar o componente
    },
};
</script>

<style scoped>
/* Estilos para o botão */
.modern-btn {
    display: inline-block;
    background: linear-gradient(45deg, #6a1b9a, #8e44ad);
    color: #ffffff;
    font-weight: bold;
    font-size: 1rem;
    text-transform: uppercase;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    letter-spacing: 0.05rem;
    text-align: center;
}

.modern-btn:hover {
    background: linear-gradient(45deg, #8e44ad, #6a1b9a);
    transform: translateY(-3px);
    box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.15);
}

.modern-btn:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(106, 27, 154, 0.4);
}

.modern-btn:active {
    transform: translateY(1px);
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
}

.team-list {
    padding: 0.5rem;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.team-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    padding: 0.5rem;
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.team-item:last-child {
    margin-bottom: 0;
}

/* Botão de Salvar */
.btn-success {
    display: inline-block;
    background: linear-gradient(45deg, #28a745, #218838);
    color: #ffffff;
    font-weight: bold;
    font-size: 1rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    text-transform: uppercase;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    letter-spacing: 0.05rem;
    text-align: center;
    width: 100%; /* Largura completa para usabilidade */
    max-width: 200px; /* Limita largura máxima */
    margin: 0 auto; /* Centraliza o botão */
}

.btn-success:hover {
    background: linear-gradient(45deg, #218838, #1e7e34); /* Altera o gradiente ao passar o mouse */
    transform: scale(1.05); /* Leve aumento de tamanho */
    box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.15); /* Sombras mais intensas */
}

.btn-success:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.4); /* Destaque ao focar */
}

.btn-success:active {
    background: linear-gradient(45deg, #1e7e34, #155724); /* Gradiente mais escuro */
    transform: scale(0.98); /* Efeito de botão pressionado */
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1); /* Sombras diminuídas */
}

.btn-success:disabled {
    background: #cccccc; /* Cinza claro para botões desabilitados */
    color: #666666; /* Texto mais apagado */
    cursor: not-allowed;
    box-shadow: none;
}
</style>
