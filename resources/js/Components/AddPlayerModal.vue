<template>
    <div class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Adicionar Jogador</h3>
                <button class="close-btn" @click="$emit('close')">X</button>
            </div>
            <div class="modal-body">
                <label for="playerName">Nome do Jogador:</label>
                <input
                    id="playerName"
                    class="modal-input"
                    v-model="playerName"
                    placeholder="Digite o nome do jogador"
                    type="text"
                />
            </div>
            <div class="modal-footer">
                <button class="save-btn" @click="save">Salvar</button>
                <button class="cancel-btn" @click="$emit('close')">Cancelar</button>
            </div>
        </div>
    </div>
</template>

<script>

import axios from "axios";
export default {
    data() {
        return {
            playerName: "", // Nome do jogador a ser adicionado
        };
    },
    props: {
      teamId : Number
    },
    methods: {
        async save() {
            if (this.playerName.trim() === "") {
                alert("Por favor, insira o nome do jogador.");
                return;
            }
            try {
                await axios.post("/api/player", {
                    name: this.playerName,
                    team_id: this.teamId,
                });

                this.$emit("add-player", this.playerName);

                this.$emit("close");

                this.playerName = "";
            } catch (error) {
                alert("Ocorreu um erro ao adicionar o jogador. Tente novamente.");
            }
        },
    },
};
</script>

<style scoped>
/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-container {
    background: white;
    padding: 20px;
    width: 90%;
    max-width: 400px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.modal-header h3 {
    color: #6a1b9a;
    font-size: 1.5rem;
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
}

.modal-body {
    margin-bottom: 15px;
}

.modal-body label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

.modal-input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.modal-footer {
    text-align: right;
}

.save-btn,
.cancel-btn {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
}

.save-btn {
    background-color: #6a1b9a;
    color: white;
    margin-right: 8px;
}

.save-btn:hover {
    background-color: #502c71;
}

.cancel-btn {
    background-color: #ddd;
    color: black;
}

.cancel-btn:hover {
    background-color: #bbb;
}
</style>
