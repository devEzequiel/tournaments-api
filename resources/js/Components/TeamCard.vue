<template>
    <div>
        <div
            class="team-card"
            @click="showOptionsModal = true"
        >
            <div class="shield-wrapper">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 100 120"
                    class="shield"
                >
                    <path
                        d="M50,0 L10,60 L50,120 L90,60 Z"
                        stroke="black"
                        stroke-width="2"
                        fill="none"
                    />
                    <path
                        :fill="firstColor"
                        d="M50,0 L10,60 L50,120 Z"
                    />
                    <path
                        :fill="secondColor"
                        d="M50,0 L90,60 L50,120 Z"
                    />
                </svg>
            </div>
            <div class="details">
                <p class="team-name">{{ name }}</p>
                <div class="loading-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>

        <div
            v-if="showOptionsModal"
            class="modal d-block"
            style="background: rgba(0, 0, 0, 0.5);"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">O que deseja fazer?</h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="closeOptionsModal"
                        ></button>
                    </div>
                    <div class="modal-body d-flex justify-content-around">
                        <button
                            class="btn btn-secondary"
                            @click="navigateToTeam"
                        >
                            Ver Time
                        </button>
                        <button
                            class="btn btn-primary"
                            @click="openEditModal"
                        >
                            Editar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <EditTeamModal
            ref="editTeamModal"
            v-show="editModalVisible"
            :teamId="teamId"
            @toast="$emit('toast', $event)"
            @reload="$emit('reload')"
        />
    </div>
</template>

<script>
import EditTeamModal from './EditTeamModal.vue';

export default {
    components: { EditTeamModal },
    props: {
        teamId: {
            type: Number,
            required: true,
        },
        name: {
            type: String,
            required: true,
        },
        firstColor: {
            type: String,
            required: true,
        },
        secondColor: {
            type: String,
            required: true,
        },
        link: {
            type: String,
            required: true,
        }
    },
    data() {
        return {
            showOptionsModal: false,
            editModalVisible: false,
        };
    },
    methods: {
        navigateToTeam() {
            console.log(this.link);
            this.$inertia.visit(this.link);
        },
        closeOptionsModal() {
            this.showOptionsModal = false;
        },
        openEditModal() {
            this.editModalVisible = true;
            this.closeOptionsModal();

            if (this.$refs.editTeamModal) {
                this.$refs.editTeamModal.openModal()
                    .then(() => {
                    })
                    .catch(error => {
                        console.error('Erro ao abrir modal:', error);
                    });
            }
        },
        closeEditModal() {
            this.editModalVisible = false;
        },
    },
};
</script>

<style scoped>
.team-card {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    position: relative;
    padding: 1rem;
    cursor: pointer;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
}

.team-card:hover {
    background-color: #f7f9fc;
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15), 0 2px 4px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.shield-wrapper {
    width: 40px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 15px;
    position: relative;
}

.shield {
    width: 100%;
    height: auto;
}

.details {
    display: flex;
    flex: 1;
    justify-content: space-between;
    align-items: center;
}

.team-name {
    font-weight: bold;
    color: #333333;
    font-size: 1rem;
    margin: 0;
    word-break: break-word;
    text-align: left;
}

.loading-dots {
    display: flex;
    gap: 4px;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.9);
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.loading-dots span {
    width: 6px;
    height: 6px;
    background-color: #6a1b9a;
    border-radius: 50%;
    animation: bounce 1.5s infinite;
}

.loading-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.loading-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes bounce {
    0%, 80%, 100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1);
    }
}

.team-card:hover .loading-dots {
    opacity: 1;
    transform: scale(1);
}
</style>
