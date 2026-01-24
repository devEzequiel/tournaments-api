<template>
    <Teleport to="body">
        <div class="toast-container">
            <transition-group name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="['toast', `toast-${toast.type}`]"
                >
                    <div class="toast-content">
                        <p class="toast-message">{{ toast.message }}</p>
                    </div>
                    <button
                        @click="removeToast(toast.id)"
                        class="toast-close"
                        aria-label="Fechar notificação"
                        type="button"
                    >
                        ×
                    </button>
                </div>
            </transition-group>
        </div>
    </Teleport>
</template>

<script>
export default {
    data() {
        return {
            toasts: []
        };
    },
    mounted() {
        // Listener global para eventos de toast
        window.addEventListener('show-toast', this.handleToastEvent);
    },
    beforeUnmount() {
        window.removeEventListener('show-toast', this.handleToastEvent);
    },
    methods: {
        handleToastEvent(event) {
            this.addToast(event.detail.message, event.detail.type);
        },
        addToast(message, type = 'info') {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message, type });

            // Remove automaticamente após 5 segundos
            setTimeout(() => {
                this.removeToast(id);
            }, 5000);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }
};
</script>

<style scoped>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    pointer-events: none;
}

.toast {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 280px;
    max-width: 520px;
    padding: 14px 18px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
    pointer-events: auto;
    border: 1px solid rgba(148, 163, 184, 0.25);
    border-left: 5px solid;
}

:global([data-theme="dark"]) .toast {
    background: #111827;
    border-color: #1f2937;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
}

.toast-success {
    border-left-color: #10b981;
}

.toast-error {
    border-left-color: #ef4444;
}

.toast-warning {
    border-left-color: #f59e0b;
}

.toast-info {
    border-left-color: #3b82f6;
}

.toast-content {
    flex: 1;
}

.toast-message {
    margin: 0;
    font-size: 0.95rem;
    color: #0f172a;
    font-weight: 600;
    line-height: 1.4;
}

:global([data-theme="dark"]) .toast-message {
    color: #e2e8f0;
}

.toast-close {
    background: none;
    border: none;
    font-size: 20px;
    color: #94a3b8;
    cursor: pointer;
    padding: 0;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

:global([data-theme="dark"]) .toast-close {
    color: #cbd5f5;
}

.toast-close:hover {
    background: #f1f5f9;
    color: #475569;
}

/* Animações */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100px);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(50px) scale(0.95);
}

/* Responsive */
@media (max-width: 640px) {
    .toast-container {
        top: 10px;
        right: 10px;
        left: 10px;
    }

    .toast {
        min-width: auto;
        width: 100%;
    }
}
</style>
