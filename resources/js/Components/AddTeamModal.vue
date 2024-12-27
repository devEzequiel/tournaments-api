<template>
    <div>
        <!-- Botão principal -->
        <button
            class="btn btn-primary my-4"
            @click="showModal = true"
        >
            + Adicionar Time
        </button>

        <!-- Modal -->
        <div
            v-if="showModal"
            class="modal-overlay"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Adicionar Time</h5>
                        <!-- Botão "X" para fechar -->
                        <button
                            class="btn-close"
                            @click="closeModal"
                            aria-label="Close"
                        >
                            &times;
                        </button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submitForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome do Time</label>
                                <input
                                    type="text"
                                    id="name"
                                    class="form-control"
                                    v-model="form.name"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label for="firstColor" class="form-label">Cor Primária</label>
                                <input
                                    type="color"
                                    id="firstColor"
                                    class="form-control form-control-color"
                                    v-model="form.first_color"
                                    required
                                />
                            </div>

                            <div class="mb-3">
                                <label for="secondColor" class="form-label">Cor Secundária</label>
                                <input
                                    type="color"
                                    id="secondColor"
                                    class="form-control form-control-color"
                                    v-model="form.second_color"
                                    required
                                />
                            </div>

                            <!-- Botão salvar -->
                            <button
                                type="submit"
                                class="btn btn-purple"
                            >
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
            showModal: false,
            form: {
                name: "",
                first_color: "#000000",
                second_color: "#FFFFFF",
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
                first_color: "#000000",
                second_color: "#FFFFFF",
            };
        },
        async submitForm() {
            try {
                await axios.post("/api/team", this.form);
                this.$emit("toast", "Novo time adicionado com sucesso!");
                this.closeModal();
                this.$emit("reload");
            } catch (error) {
                console.error(error);
                this.$emit("toast", "Erro ao adicionar o time.");
            }
        },
    },
};
</script>

<style scoped>
/* Botão principal estilizado */
.btn-primary {
    background: linear-gradient(45deg, #6a1b9a, #8e44ad);
    color: #ffffff;
    font-weight: bold;
    font-size: 1rem;
    border: none;
    border-radius: 50px;
    padding: 10px 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #8e44ad, #6a1b9a);
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.btn-primary:focus {
    outline: none;
    box-shadow: 0 0 8px rgba(138, 43, 226, 0.5);
}

/* Modal Overlay */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

/* Modal Dialog */
.modal-dialog {
    background: #ffffff;
    border-radius: 15px;
    padding: 2rem;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    animation: fadeIn 0.3s ease;
}

/* Botão "X" para fechar a modal */
.btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6a1b9a;
    cursor: pointer;
    transition: transform 0.3s ease, color 0.3s ease;
    font-weight: bold;
}

.btn-close:hover {
    color: #8e44ad;
    transform: scale(1.2);
}

.btn-close:focus {
    outline: none;
}

/* Modal Header */
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

/* Modal Title */
.modal-title {
    font-size: 1.25rem;
    font-weight: bold;
    color: #6a1b9a;
}

.modal-content {
    padding: 1rem;
}

/* Botão "Salvar" estilizado em roxo */
.btn-purple {
    background: linear-gradient(45deg, #6a1b9a, #8e44ad);
    color: #ffffff;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-purple:hover {
    background: linear-gradient(45deg, #8e44ad, #6a1b9a);
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

/* Animação para abrir a modal */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
