<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-container">
            <!-- Header -->
            <div class="modal-header">
                <h2>👤 Adicionar Jogador</h2>
                <button @click="$emit('close')" class="close-btn" type="button">
                    <span>×</span>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="save" class="modal-body">
                <!-- Nome do Jogador -->
                <div class="form-group">
                    <label for="playerName">
                        <span class="label-icon">📝</span>
                        Nome do Jogador
                    </label>
                    <input
                        id="playerName"
                        type="text"
                        v-model="playerName"
                        placeholder="Ex: Lionel Messi"
                        required
                        class="form-input"
                        autofocus
                    />
                    <p class="input-hint">Digite o nome completo ou apelido do jogador</p>
                </div>

                <!-- Preview Card -->
                <div v-if="playerName" class="preview-card">
                    <div class="player-avatar">{{ getInitials(playerName) }}</div>
                    <div class="player-info">
                        <div class="player-name">{{ playerName }}</div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="modal-actions">
                    <button type="button" @click="$emit('close')" class="btn-cancel">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-save" :disabled="isSubmitting || !playerName.trim()">
                        <span v-if="!isSubmitting">💾 Adicionar</span>
                        <span v-else>Adicionando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { useToast } from "@/Composables/useToast";

export default {
    props: {
        teamId: Number
    },
    data() {
        return {
            playerName: "",
            isSubmitting: false,
            toast: useToast(),
        };
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
        async save() {
            if (this.playerName.trim() === "") {
                return;
            }

            if (this.isSubmitting) return;
            
            this.isSubmitting = true;

            try {
                await axios.post("/api/player", {
                    name: this.playerName,
                    team_id: this.teamId,
                });

                this.toast.success(`Jogador ${this.playerName} adicionado com sucesso.`);
                this.$emit("add-player", this.playerName);
                this.$emit("close");
                this.playerName = "";
            } catch (error) {
                console.error(error);
                const errorMsg = error.response?.data?.message || 'Erro ao adicionar jogador';
                this.toast.error(`${errorMsg}`);
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
    max-width: 500px;
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

/* Form Input */
.form-input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 16px;
    transition: all 0.2s ease;
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input::placeholder {
    color: #a0aec0;
}

.input-hint {
    margin-top: 6px;
    font-size: 13px;
    color: #718096;
}

/* Preview Card */
.preview-card {
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
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
    color: white;
    flex-shrink: 0;
}

.player-info {
    flex: 1;
}

.player-name {
    font-size: 18px;
    font-weight: 700;
    color: white;
    margin-bottom: 4px;
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
}
</style>
