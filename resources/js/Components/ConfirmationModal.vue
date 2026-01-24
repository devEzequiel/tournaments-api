<template>
    <div class="modal-overlay" @click.self="$emit('cancel')">
        <div class="modal-container">
            <!-- Header -->
            <div class="modal-header">
                <h2>⚠️ Confirmar Ação</h2>
                <button @click="$emit('cancel')" class="close-btn" type="button">
                    <span>×</span>
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <div class="message-box">
                    <div class="warning-icon">⚠️</div>
                    <p class="message-text">{{ message }}</p>
                </div>

                <!-- Actions -->
                <div class="modal-actions">
                    <button type="button" @click="$emit('cancel')" class="btn-cancel">
                        Cancelar
                    </button>
                    <button type="button" @click="$emit('confirm')" class="btn-confirm">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        message: String,
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
    max-width: 480px;
    overflow: hidden;
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

/* Message Box */
.message-box {
    text-align: center;
    margin-bottom: 32px;
}

.warning-icon {
    font-size: 64px;
    margin-bottom: 16px;
    animation: pulse 2s infinite;
}

.message-text {
    font-size: 16px;
    color: #2d3748;
    line-height: 1.6;
    margin: 0;
}

/* Modal Actions */
.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.btn-cancel,
.btn-confirm {
    padding: 12px 32px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    min-width: 120px;
}

.btn-cancel {
    background: #edf2f7;
    color: #4a5568;
}

.btn-cancel:hover {
    background: #e2e8f0;
    transform: translateY(-2px);
}

.btn-confirm {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-confirm:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
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

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Responsive */
@media (max-width: 640px) {
    .modal-body {
        padding: 24px 20px;
    }
    
    .modal-header {
        padding: 20px;
    }
    
    .modal-actions {
        flex-direction: column;
    }
    
    .btn-cancel,
    .btn-confirm {
        width: 100%;
    }
}
</style>
