<template>
    <div class="players-list-container">
        <h2 class="title">Plantel Atual</h2>

        <table class="players-table">
            <thead>
            <tr>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="player in players" :key="player.id">
                <td>{{ player.player_name }}</td>
                <td>
                    <button class="action-btn edit-btn" @click="openEditModal(player)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete-btn" @click="confirmDelete(player)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Modal Edição -->
        <EditPlayerModal
            v-if="showEditModal"
            :player="selectedPlayer"
            @close="showEditModal = false"
            @player-updated="onPlayerUpdated"
        />

        <!-- Modal Confirmação -->
        <ConfirmationModal
            v-if="showDeleteModal"
            message="Você tem certeza que deseja excluir este jogador?"
            @confirm="deletePlayer"
            @cancel="showDeleteModal = false"
        />
    </div>
</template>

<script>
import EditPlayerModal from "@/Components/EditPlayerModal.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import axios from "axios";

export default {
    props: {
        team: Object,
    },
    components: {
        EditPlayerModal,
        ConfirmationModal,
    },
    data() {
        return {
            players: [],
            showEditModal: false, // Controla modal de edição
            showDeleteModal: false, // Controla modal de exclusão
            selectedPlayer: null, // Jogador atualmente selecionado
        };
    },
    methods: {
        async fetchCurrentPlayers() {
            const response = await axios.get(`/api/team/${this.team.id}/current`);
            this.players = response.data.data;
        },
        openEditModal(player) {
            this.selectedPlayer = player;
            this.showEditModal = true;
        },
        confirmDelete(player) {
            console.log(player);
            this.selectedPlayer = player; // Define o jogador para exclusão
            this.showDeleteModal = true;
        },
        deletePlayer() {
            axios.delete(`/api/player/${this.selectedPlayer.player_id}`)
                .then(() => {
                    this.showDeleteModal = false; // Close the modal
                    this.selectedPlayer = null; // Clear selected player
                    this.fetchCurrentPlayers(); // Refresh the list after deletion
                })
                .catch((errors) => {
                    console.error(errors); // Log errors for debugging (if any)
                });
        },
        onPlayerUpdated() {
            this.fetchCurrentPlayers();
        },
    },
    mounted() {
        this.fetchCurrentPlayers()
    }
};
</script>

<style scoped>
/* Container */
.players-list-container {
    padding: 1rem;
}

/* Título */
.title {
    text-align: center;
    color: #6a1b9a;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

/* Tabela */
.players-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.players-table th,
.players-table td {
    text-align: left;
    padding: 0.75rem;
    border: 1px solid #ddd;
}

.players-table th {
    background-color: #f3f3f3;
}

.players-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Botões de ação */
.action-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.2rem;
    margin-right: 0.5rem;
    transition: transform 0.2s;
}

.action-btn:hover {
    transform: scale(1.1);
}

/* Botão de edição */
.edit-btn {
    color: #007bff;
}

/* Botão de exclusão */
.delete-btn {
    color: #dc3545;
}
</style>
