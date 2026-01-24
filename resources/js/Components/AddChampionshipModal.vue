<template>
    <div>
        <!-- Botão para abrir a modal -->
        <button class="add-championship-btn" @click="showModal = true">
            <span>➕</span>
            <span>Novo Campeonato</span>
        </button>

        <!-- Modal Overlay -->
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
            <div class="modal-container">
                <!-- Header -->
                <div class="modal-header">
                    <h2>🏆 Criar Campeonato</h2>
                    <button class="close-btn" @click="closeModal" type="button">
                        <span>×</span>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitForm" class="modal-body">
                    <!-- Nome do Campeonato -->
                    <div class="form-group">
                        <label for="name">
                            <span class="label-icon">📝</span>
                            Título do Campeonato
                        </label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            placeholder="Ex: Copa dos Campeões 2026"
                            required
                            class="form-input"
                        />
                    </div>

                    <!-- Descrição -->
                    <div class="form-group">
                        <label for="description">
                            <span class="label-icon">📄</span>
                            Descrição
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Descreva o campeonato..."
                            required
                            rows="3"
                            class="form-textarea"
                        ></textarea>
                    </div>

                    <!-- Número de Rodadas e Playoffs -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="rounds">
                                <span class="label-icon">🔢</span>
                                Rodadas
                            </label>
                            <input
                                type="number"
                                id="rounds"
                                v-model="form.rounds"
                                min="1"
                                max="20"
                                required
                                class="form-input"
                            />
                            <small class="form-hint">Número de rodadas do campeonato</small>
                        </div>

                        <div class="form-group">
                            <label for="playoffs">
                                <span class="label-icon">🎯</span>
                                Playoffs
                            </label>
                            <select id="playoffs" v-model="form.playoffs" class="form-select" @change="onPlayoffsChange">
                                <option :value="false">Não</option>
                                <option :value="true">Sim</option>
                            </select>
                            <small class="form-hint">Fase eliminatória final</small>
                        </div>
                    </div>

                    <!-- Configuração de Playoff (se ativado) -->
                    <div v-if="form.playoffs" class="playoff-config-section">
                        <div class="playoff-config-header">
                            <span class="playoff-icon">🏆</span>
                            <div class="playoff-info">
                                <strong>Tipo de Playoff:</strong>
                                <span class="playoff-type-badge">{{ getPlayoffTypeLabel() }}</span>
                            </div>
                            <button type="button" @click="showPlayoffConfigModal = true" class="btn-config-playoff">
                                ⚙️ Configurar
                            </button>
                        </div>
                    </div>

                    <!-- Seleção de Times -->
                    <div class="form-group">
                        <label for="teamSelector">
                            <span class="label-icon">⚽</span>
                            Adicionar Times
                        </label>
                        <select
                            id="teamSelector"
                            v-model="selectedTeam"
                            @change="addTeam"
                            class="form-select"
                            :disabled="availableTeams.length === 0"
                        >
                            <option value="" disabled>
                                {{ availableTeams.length > 0 ? 'Selecione um time' : 'Todos os times foram adicionados' }}
                            </option>
                            <option
                                v-for="team in availableTeams"
                                :key="team.id"
                                :value="team.id"
                            >
                                {{ team.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Times Selecionados -->
                    <div class="form-group" v-if="form.teams.length > 0">
                        <label>
                            <span class="label-icon">✅</span>
                            Times Participantes ({{ form.teams.length }})
                        </label>
                        <div class="teams-list">
                            <div
                                v-for="(team, index) in form.teams"
                                :key="team.id"
                                class="team-chip"
                            >
                                <span class="team-number">{{ index + 1 }}</span>
                                <span class="team-name">{{ team.name }}</span>
                                <button
                                    type="button"
                                    class="remove-btn"
                                    @click="removeTeam(team.id)"
                                    title="Remover time"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-state">
                        <p>Nenhum time selecionado ainda</p>
                    </div>

                    <!-- Actions -->
                    <div class="modal-actions">
                        <button type="button" @click="closeModal" class="btn-cancel">
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="btn-save" 
                            :disabled="form.teams.length < 2 || isSubmitting"
                        >
                            <span v-if="!isSubmitting">💾 Criar Campeonato</span>
                            <span v-else>Criando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal de Configuração de Playoff -->
        <PlayoffConfigModal
            v-if="showPlayoffConfigModal"
            :initial-type="form.playoff_type"
            @confirm="handlePlayoffConfig"
            @close="showPlayoffConfigModal = false"
        />
    </div>
</template>

<script>
import axios from "axios";
import PlayoffConfigModal from "./PlayoffConfigModal.vue";
import { useToast } from "@/Composables/useToast";

export default {
    components: {
        PlayoffConfigModal
    },
    data() {
        return {
            showModal: false,
            showPlayoffConfigModal: false,
            teams: [],
            selectedTeam: "",
            isSubmitting: false,
            toast: useToast(),
            form: {
                name: "",
                description: "",
                rounds: 2,
                playoffs: false,
                playoff_type: "final",
                teams: [],
            },
        };
    },
    computed: {
        availableTeams() {
            return this.teams.filter(
                (team) => !this.form.teams.some((selected) => selected.id === team.id)
            );
        },
    },
    methods: {
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
                playoff_type: "final",
                teams: [],
            };
            this.selectedTeam = "";
            this.isSubmitting = false;
        },
        onPlayoffsChange() {
            if (this.form.playoffs) {
                // Quando ativar playoffs, abrir modal de configuração
                this.showPlayoffConfigModal = true;
            } else {
                // Quando desativar, limpar configuração
                this.form.playoff_type = "final";
            }
        },
        handlePlayoffConfig(playoffType) {
            this.form.playoff_type = playoffType;
        },
        getPlayoffTypeLabel() {
            if (this.form.playoff_type === 'final') {
                return '🥇 Final Direta (1º vs 2º)';
            } else if (this.form.playoff_type === 'semifinal') {
                return '🏅 Semifinal + Final';
            }
            return 'Não configurado';
        },
        async fetchTeams() {
            try {
                const response = await axios.get("/api/team");
                if (response.data && response.data.data) {
                    this.teams = response.data.data;
                }
            } catch (error) {
                console.error("Erro ao buscar times da API:", error);
                this.toast.error("Erro ao carregar times.");
            }
        },
        addTeam() {
            if (this.selectedTeam) {
                const teamToAdd = this.teams.find(
                    (team) => team.id === parseInt(this.selectedTeam)
                );
                if (teamToAdd && !this.form.teams.some((t) => t.id === teamToAdd.id)) {
                    this.form.teams.push(teamToAdd);
                }
                this.selectedTeam = "";
            }
        },
        removeTeam(teamId) {
            this.form.teams = this.form.teams.filter((team) => team.id !== teamId);
        },
        async submitForm() {
            if (this.form.teams.length < 2) {
                this.toast.warning("Selecione pelo menos 2 times para criar o campeonato.");
                return;
            }

            // Validar número mínimo de times para playoffs
            if (this.form.playoffs) {
                const minTeams = this.form.playoff_type === 'semifinal' ? 4 : 2;
                if (this.form.teams.length < minTeams) {
                    this.toast.warning(`Para playoffs ${this.form.playoff_type === 'semifinal' ? 'com semifinais' : ''} você precisa de pelo menos ${minTeams} times.`);
                    return;
                }
            }

            if (this.isSubmitting) return;

            this.isSubmitting = true;

            try {
                const payload = {
                    ...this.form,
                    teams: this.form.teams.map((team) => team.id),
                };

                await axios.post("/api/championship", payload);

                this.toast.success("Campeonato criado com sucesso.");
                this.closeModal();
                this.$emit("reload");
            } catch (error) {
                console.error(error);
                const errorMsg = error.response?.data?.message || "Erro ao criar campeonato";
                this.toast.error(`${errorMsg}`);
            } finally {
                this.isSubmitting = false;
            }
        },
    },
    mounted() {
        this.fetchTeams();
    },
};
</script>

<style scoped>
/* Add Championship Button */
.add-championship-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 16px;
    padding: 14px 28px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    transition: all 0.3s;
    margin-bottom: 20px;
}

.add-championship-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.add-championship-btn:active {
    transform: translateY(0);
}

/* Modal Overlay */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    padding: 20px;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Modal Container */
.modal-container {
    background: #ffffff;
    border-radius: 16px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Header */
.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 24px 30px;
    border-radius: 16px 16px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 10;
}

.modal-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}

.close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 32px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    line-height: 1;
    padding: 0;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

/* Modal Body */
.modal-body {
    padding: 30px;
}

/* Form Groups */
.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.label-icon {
    font-size: 18px;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 12px 16px;
    font-size: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    transition: all 0.2s;
    font-family: inherit;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-hint {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    color: #999;
}

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* Teams List */
.teams-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    max-height: 200px;
    overflow-y: auto;
}

.team-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: white;
    border: 2px solid #667eea;
    border-radius: 20px;
    padding: 8px 12px;
    font-size: 14px;
    transition: all 0.2s;
}

.team-chip:hover {
    background: #f0f4ff;
}

.team-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #667eea;
    color: white;
    border-radius: 50%;
    font-size: 12px;
    font-weight: 600;
}

.team-name {
    color: #333;
    font-weight: 500;
}

.remove-btn {
    width: 24px;
    height: 24px;
    border: none;
    background: #fee;
    color: #c33;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.remove-btn:hover {
    background: #fdd;
    transform: scale(1.1);
}

/* Empty State */
.empty-state {
    padding: 40px 20px;
    text-align: center;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #ddd;
}

.empty-state p {
    margin: 0;
    color: #999;
    font-size: 14px;
}

/* Playoff Config Section */
.playoff-config-section {
    margin-bottom: 24px;
    padding: 16px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    border: 2px solid #667eea;
    border-radius: 12px;
}

.playoff-config-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.playoff-icon {
    font-size: 24px;
}

.playoff-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.playoff-info strong {
    font-size: 13px;
    color: #4a5568;
    font-weight: 600;
}

.playoff-type-badge {
    display: inline-block;
    font-size: 14px;
    color: #667eea;
    font-weight: 700;
}

.btn-config-playoff {
    padding: 8px 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-config-playoff:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* Actions */
.modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.btn-cancel,
.btn-save {
    flex: 1;
    padding: 14px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel {
    background: #e0e0e0;
    color: #666;
}

.btn-cancel:hover {
    background: #d0d0d0;
}

.btn-save {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-save:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-save:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .modal-container {
        max-width: 100%;
        max-height: 100vh;
        border-radius: 0;
    }

    .modal-header h2 {
        font-size: 20px;
    }

    .modal-body {
        padding: 20px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
