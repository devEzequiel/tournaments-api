<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-container">
            <!-- Header -->
            <div class="modal-header">
                <h2>🔄 Transferir Jogador</h2>
                <button @click="$emit('close')" class="close-btn" type="button">
                    <span>×</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <!-- Player Info -->
                <div class="player-card">
                    <div class="player-avatar">{{ getInitials(player.name) }}</div>
                    <div class="player-details">
                        <div class="player-name">{{ player.name }}</div>
                        <div class="player-current-team">{{ getCurrentTeam() }}</div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="updatePlayer">
                    <div class="form-group">
                        <label for="team">
                            <span class="label-icon">⚽</span>
                            Novo Time
                        </label>
                        <select
                            v-model="selectedTeam"
                            id="team"
                            class="form-select"
                            required
                        >
                            <option value="null" class="free-agent-option">🏠 Agente Livre (Sem Clube)</option>
                            <option v-for="team in teams" :key="team.id" :value="team.id">
                                {{ team.name }}
                            </option>
                        </select>
                        <p class="input-hint">Selecione o destino do jogador ou mantenha-o sem clube</p>
                    </div>

                    <!-- Actions -->
                    <div class="modal-actions">
                        <button type="button" @click="$emit('close')" class="btn-cancel">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-save" :disabled="isSubmitting">
                            <span v-if="!isSubmitting">💾 Confirmar Transferência</span>
                            <span v-else>Transferindo...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { useToast } from "@/Composables/useToast";

export default {
    props: {
        player: Object,
    },
    data() {
        return {
            selectedTeam: this.player.team_id === null ? "null" : this.player.team_id,
            teams: [],
            isSubmitting: false,
            toast: useToast(),
        };
    },
    mounted() {
        this.getTeams();
    },
    methods: {
        getInitials(name) {
            if (!name) return '';
            return name.split(' ')
                .slice(0, 2)
                .map(n => n[0])
                .join('')
                .toUpperCase();
        },
        getCurrentTeam() {
            if (!this.player.team_id) return 'Agente Livre';
            const team = this.teams.find(t => t.id === this.player.team_id);
            return team ? team.name : 'Carregando...';
        },
        async getTeams() {
            try {
                const response = await axios.get(`/api/team`);
                this.teams = response.data.data;
            } catch (error) {
                console.error('Erro ao carregar times:', error);
            }
        },
        async updatePlayer() {
            if (this.isSubmitting) return;
            
            this.isSubmitting = true;
            const newTeamId = this.selectedTeam === "null" ? null : this.selectedTeam;

            try {
                await axios.put(`/api/player/change-team`, {
                    new_team_id: newTeamId,
                    player_id: this.player.player_id,
                });

                this.toast.success("Jogador transferido com sucesso.");
                this.$emit("player-updated", {
                    ...this.player,
                    team_id: newTeamId,
                });
                this.$emit("close");
            } catch (error) {
                console.error("Erro ao transferir jogador:", error);
                this.toast.error("Erro ao transferir jogador. Tente novamente.");
            } finally {
                this.isSubmitting = false;
            }
        },
    },
};
</script>

<style scoped>
/* Modal Overlay */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.2s ease;
}

/* Modal Container */
.modal-container {
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 520px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s ease;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

/* Modal Header */
.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
}

.close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    color: white;
    font-size: 24px;
    padding: 0;
    line-height: 1;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

/* Modal Body */
.modal-body {
    padding: 32px;
}

/* Player Card */
.player-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.player-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 20px;
    color: white;
    flex-shrink: 0;
}

.player-details {
    flex: 1;
}

.player-name {
    font-size: 20px;
    font-weight: 700;
    color: white;
    margin-bottom: 4px;
}

.player-current-team {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 500;
}

/* Form Group */
.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 14px;
}

.label-icon {
    font-size: 18px;
}

/* Form Select */
.form-select {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.2s ease;
    background: white;
    cursor: pointer;
}

.form-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.free-agent-option {
    font-weight: 600;
    color: #667eea;
}

.input-hint {
    margin-top: 6px;
    font-size: 13px;
    color: #718096;
}

/* Modal Actions */
.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 24px;
    border-top: 2px solid #f7fafc;
}

.btn-cancel,
.btn-save {
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.btn-cancel {
    background: #edf2f7;
    color: #4a5568;
}

.btn-cancel:hover {
    background: #e2e8f0;
}

.btn-save {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-save:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 640px) {
    .modal-body {
        padding: 24px 20px;
    }
    
    .modal-header {
        padding: 20px;
    }
    
    .player-avatar {
        width: 50px;
        height: 50px;
        font-size: 18px;
    }
    
    .player-name {
        font-size: 18px;
    }
}
</style>

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
