<template>
    <!-- Overlay da Modal -->
    <div
        class="modal-overlay d-flex align-items-center justify-content-center"
        tabindex="-1"
        role="dialog"
    >
        <!-- Container da Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modern-modal shadow-lg">
                    <!-- Corpo da Modal -->
                    <div class="modal-body text-center">
                        <p class="message-text text-muted mb-4">
                            {{ message }}
                        </p>
                    </div>
                    <!-- Ações -->
                    <div class="modal-footer justify-content-center">
                        <button
                            type="button"
                            class="btn btn-secondary shadow-sm px-4"
                            @click="$emit('cancel')"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-danger shadow-sm px-4 ms-2"
                            @click="$emit('confirm')"
                        >
                            Confirmar
                        </button>
                    </div>
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
/* Overlay: Fundo escuro */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Fundo escuro semi-transparente */
    z-index: 1050; /* Garantir sobreposição sobre outros elementos */
}

/* Estilização moderna da Modal */
.modern-modal {
    border-radius: 15px; /* Arredondar bordas da modal */
    overflow: hidden; /* Garante que bordas sejam respeitadas */
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3); /* Sombra suave */
}

.message-text {
    font-size: 1.1rem;
    font-weight: 500;
}

.btn {
    border-radius: 50px; /* Botões arredondados */
}

/* Estilo do botão Secundário */
.btn-secondary {
    background-color: #f8f9fa;
    color: #6a1b9a;
    border: none;
}

.btn-secondary:hover {
    background-color: #6a1b9a;
    color: white;
    border: none;
}

/* Estilo do botão de Confirmar */
.btn-danger {
    background-color: #dc3545;
    border: none;
}

.btn-danger:hover {
    background-color: #b02a37;
    border: none;
}
</style>
