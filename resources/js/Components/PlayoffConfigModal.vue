<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-container">
            <!-- Header -->
            <div class="modal-header">
                <h2>🏆 Configurar Playoffs</h2>
                <button @click="$emit('close')" class="close-btn" type="button">
                    <span>×</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <p class="description">
                    Escolha o formato dos playoffs (mata-mata) para decidir o campeão:
                </p>

                <!-- Opções de Playoff -->
                <div class="playoff-options">
                    <!-- Final Direta -->
                    <div 
                        class="playoff-option" 
                        :class="{ 'selected': selectedType === 'final' }"
                        @click="selectedType = 'final'"
                    >
                        <div class="option-icon">🥇</div>
                        <div class="option-content">
                            <h3>Final Direta</h3>
                            <p>1º vs 2º colocado</p>
                            <div class="option-detail">
                                Melhor de 3 jogos entre os dois primeiros colocados da fase de grupos. 
                                O melhor rankeado joga em casa no último confronto.
                            </div>
                        </div>
                        <div class="option-check">
                            <span v-if="selectedType === 'final'">✓</span>
                        </div>
                    </div>

                    <!-- Semifinal -->
                    <div 
                        class="playoff-option" 
                        :class="{ 'selected': selectedType === 'semifinal' }"
                        @click="selectedType = 'semifinal'"
                    >
                        <div class="option-icon">🏅</div>
                        <div class="option-content">
                            <h3>Semifinal + Final</h3>
                            <p>4 melhores times</p>
                            <div class="option-detail">
                                <strong>Semifinais:</strong> 1º vs 4º e 2º vs 3º<br>
                                <strong>Final:</strong> Vencedores das semifinais<br>
                                Melhor rankeado joga em casa no último confronto de cada série.
                            </div>
                        </div>
                        <div class="option-check">
                            <span v-if="selectedType === 'semifinal'">✓</span>
                        </div>
                    </div>
                </div>

                <!-- Regras -->
                <div class="rules-box">
                    <h4>📋 Regras do Mata-Mata</h4>
                    <ul>
                        <li>Cada série é melhor de 3 jogos (o primeiro a vencer 2 avança)</li>
                        <li>Em caso de empate nos 2 primeiros jogos, há um 3º jogo decisivo</li>
                        <li>O time melhor rankeado joga em casa no 3º jogo</li>
                        <li>Se o 3º jogo empatar, haverá disputa de pênaltis</li>
                        <li>Todos os gols e estatísticas contam para os rankings</li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="modal-actions">
                    <button type="button" @click="$emit('close')" class="btn-cancel">
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        @click="confirm" 
                        class="btn-confirm"
                        :disabled="!selectedType"
                    >
                        Confirmar Formato
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        initialType: {
            type: String,
            default: 'final'
        }
    },
    data() {
        return {
            selectedType: this.initialType
        };
    },
    methods: {
        confirm() {
            this.$emit('confirm', this.selectedType);
            this.$emit('close');
        }
    }
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
    max-width: 700px;
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
    overflow-y: auto;
}

.description {
    color: #4a5568;
    font-size: 15px;
    margin-bottom: 24px;
    line-height: 1.6;
}

/* Playoff Options */
.playoff-options {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

.playoff-option {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    gap: 16px;
    align-items: flex-start;
    background: white;
}

.playoff-option:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.playoff-option.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.option-icon {
    font-size: 32px;
    flex-shrink: 0;
}

.option-content {
    flex: 1;
}

.option-content h3 {
    margin: 0 0 4px 0;
    font-size: 18px;
    font-weight: 700;
    color: #2d3748;
}

.option-content p {
    margin: 0 0 12px 0;
    font-size: 14px;
    color: #718096;
    font-weight: 600;
}

.option-detail {
    font-size: 13px;
    color: #4a5568;
    line-height: 1.6;
}

.option-detail strong {
    color: #2d3748;
    font-weight: 600;
}

.option-check {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.playoff-option.selected .option-check {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
    font-weight: 700;
}

/* Rules Box */
.rules-box {
    background: #f7fafc;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.rules-box h4 {
    margin: 0 0 12px 0;
    font-size: 16px;
    font-weight: 700;
    color: #2d3748;
}

.rules-box ul {
    margin: 0;
    padding-left: 20px;
}

.rules-box li {
    font-size: 14px;
    color: #4a5568;
    line-height: 1.8;
    margin-bottom: 6px;
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
.btn-confirm {
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

.btn-confirm {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-confirm:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.btn-confirm:disabled {
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
    
    .playoff-option {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .option-check {
        margin-top: 12px;
    }
}
</style>
