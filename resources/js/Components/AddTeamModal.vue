<template>
    <div>
        <!-- Botão principal -->
        <button class="add-team-btn" @click="showModal = true">
            <span>⚽</span>
            <span>Novo Time</span>
        </button>

        <!-- Modal -->
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
            <div class="modal-container">
                <!-- Header -->
                <div class="modal-header">
                    <h2>⚽ Adicionar Time</h2>
                    <button @click="closeModal" class="close-btn" type="button">
                        <span>×</span>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitForm" class="modal-body">
                    <!-- Nome do Time -->
                    <div class="form-group">
                        <label for="name">
                            <span class="label-icon">📝</span>
                            Nome do Time
                        </label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            placeholder="Ex: FC Barcelona"
                            required
                            class="form-input"
                        />
                    </div>

                    <!-- Cores -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstColor">
                                <span class="label-icon">🎨</span>
                                Cor Primária
                            </label>
                            <input
                                type="color"
                                id="firstColor"
                                v-model="form.first_color"
                                class="form-color"
                                required
                            />
                            <span class="color-preview" :style="{ background: form.first_color }"></span>
                        </div>

                        <div class="form-group">
                            <label for="secondColor">
                                <span class="label-icon">🎨</span>
                                Cor Secundária
                            </label>
                            <input
                                type="color"
                                id="secondColor"
                                v-model="form.second_color"
                                class="form-color"
                                required
                            />
                            <span class="color-preview" :style="{ background: form.second_color }"></span>
                        </div>
                    </div>

                    <!-- Preview das Cores -->
                    <div class="color-preview-box">
                        <div class="preview-label">Preview:</div>
                        <div class="team-badge" :style="{ background: `linear-gradient(135deg, ${form.first_color} 0%, ${form.second_color} 100%)` }">
                            {{ form.name || 'Nome' }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="modal-actions">
                        <button type="button" @click="closeModal" class="btn-cancel">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-save" :disabled="isSubmitting">
                            <span v-if="!isSubmitting">💾 Salvar</span>
                            <span v-else>Salvando...</span>
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
    data() {
        return {
            showModal: false,
            isSubmitting: false,
            toast: useToast(),
            form: {
                name: "",
                first_color: "#667eea",
                second_color: "#764ba2",
            },
        };
    },
    methods: {
        closeModal() {
            this.showModal = false;
            this.resetForm();
        },
        resetForm() {
            this.form = {
                name: "",
                first_color: "#667eea",
                second_color: "#764ba2",
            };
            this.isSubmitting = false;
        },
        async submitForm() {
            if (this.isSubmitting) return;
            
            this.isSubmitting = true;
            
            try {
                await axios.post("/api/team", this.form);
                this.toast.success("Time criado com sucesso.");
                this.closeModal();
                this.$emit("reload");
            } catch (error) {
                console.error(error);
                const errorMsg = error.response?.data?.message || "Erro ao criar time";
                this.toast.error(`${errorMsg}`);
            } finally {
                this.isSubmitting = false;
            }
        },
    },
};
</script>

<style scoped>
/* Botão principal */
.add-team-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    margin: 24px 0;
}

.add-team-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.add-team-btn span:first-child {
    font-size: 20px;
}

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
    max-width: 550px;
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
    position: sticky;
    top: 0;
    z-index: 10;
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
    overflow-y: auto;
}

/* Form Groups */
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
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
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

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* Color Input */
.form-color {
    width: 60px;
    height: 60px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.form-color:hover {
    border-color: #667eea;
    transform: scale(1.05);
}

.color-preview {
    display: inline-block;
    width: 100%;
    height: 8px;
    border-radius: 4px;
    margin-top: 8px;
}

/* Color Preview Box */
.color-preview-box {
    background: #f7fafc;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    text-align: center;
}

.preview-label {
    font-size: 12px;
    font-weight: 600;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.team-badge {
    display: inline-block;
    padding: 12px 32px;
    border-radius: 12px;
    color: white;
    font-weight: 700;
    font-size: 18px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
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
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .modal-body {
        padding: 24px 20px;
    }
    
    .modal-header {
        padding: 20px;
    }
}
</style>
