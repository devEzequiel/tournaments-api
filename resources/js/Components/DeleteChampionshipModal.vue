<template>
    <Teleport to="body">
        <div v-if="show" class="modal-overlay" @click.self="close">
            <div class="modal-content">
                <h2 class="modal-title">⚠️ Deletar Campeonato</h2>
                
                <div class="warning-message">
                <p>Você está prestes a deletar o campeonato:</p>
                <p class="championship-name">{{ championshipName }}</p>
                <p class="danger-text">⚠️ Esta ação é IRREVERSÍVEL!</p>
                <p>Todos os dados serão permanentemente deletados:</p>
                <ul>
                    <li>✖️ Partidas (fixtures)</li>
                    <li>✖️ Gols</li>
                    <li>✖️ Avaliações de jogadores</li>
                    <li>✖️ Prêmios</li>
                    <li>✖️ Estatísticas</li>
                </ul>
            </div>

            <div class="confirmation-input">
                <label for="confirm-text">Digite <strong>"sim"</strong> para confirmar:</label>
                <input
                    id="confirm-text"
                    v-model="confirmText"
                    type="text"
                    placeholder="Digite 'sim'"
                    @keyup.enter="confirmDelete"
                />
            </div>

            <div class="modal-actions">
                <button class="btn-cancel" @click="close">Cancelar</button>
                <button 
                    class="btn-delete" 
                    :disabled="confirmText.toLowerCase() !== 'sim' || isDeleting"
                    @click="confirmDelete"
                >
                    {{ isDeleting ? 'Deletando...' : 'Deletar' }}
                </button>
            </div>
        </div>
        </div>
    </Teleport>
</template>

<script>
export default {
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        championshipName: {
            type: String,
            required: true,
        },
        championshipId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            confirmText: '',
            isDeleting: false,
        };
    },
    methods: {
        close() {
            if (!this.isDeleting) {
                this.confirmText = '';
                this.$emit('close');
            }
        },
        async confirmDelete() {
            if (this.confirmText.toLowerCase() !== 'sim' || this.isDeleting) {
                return;
            }
            
            this.isDeleting = true;
            this.$emit('confirm');
        },
    },
    watch: {
        show(newVal) {
            if (!newVal) {
                this.confirmText = '';
                this.isDeleting = false;
            }
        },
    },
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 30px;
    max-width: 500px;
    width: 90%;
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

.modal-title {
    font-size: 1.8rem;
    font-weight: bold;
    color: #dc2626;
    margin-bottom: 20px;
    text-align: center;
}

.warning-message {
    background: #fef2f2;
    border: 2px solid #fecaca;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}

.warning-message p {
    margin-bottom: 10px;
    color: #333;
}

.championship-name {
    font-size: 1.3rem;
    font-weight: bold;
    color: #dc2626;
    text-align: center;
    margin: 15px 0;
}

.danger-text {
    font-size: 1.1rem;
    font-weight: bold;
    color: #dc2626;
    text-align: center;
    margin: 15px 0 !important;
}

.warning-message ul {
    list-style: none;
    padding: 0;
    margin-top: 10px;
}

.warning-message li {
    padding: 5px 0;
    color: #dc2626;
    font-weight: 500;
}

.confirmation-input {
    margin-bottom: 25px;
}

.confirmation-input label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #333;
}

.confirmation-input input {
    width: 100%;
    padding: 12px;
    border: 2px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.confirmation-input input:focus {
    outline: none;
    border-color: #dc2626;
}

.modal-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

.btn-cancel {
    padding: 12px 24px;
    background: #6b7280;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-cancel:hover {
    background: #4b5563;
}

.btn-delete {
    padding: 12px 24px;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-delete:hover:not(:disabled) {
    background: #b91c1c;
    transform: translateY(-1px);
}

.btn-delete:disabled {
    background: #d1d5db;
    cursor: not-allowed;
}

@media (max-width: 576px) {
    .modal-content {
        padding: 20px;
    }

    .modal-title {
        font-size: 1.4rem;
    }

    .championship-name {
        font-size: 1.1rem;
    }

    .modal-actions {
        flex-direction: column;
    }

    .btn-cancel,
    .btn-delete {
        width: 100%;
    }
}
</style>
