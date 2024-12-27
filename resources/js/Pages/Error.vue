<template>
    <div class="error-container">
        <div class="error-box">
            <div class="icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="error-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 15h3.75m-3.75-6h3.75m-8.25 9.75h12.75c1.8 0 3.375-1.125 3.825-2.925l3.186-12.75C23.445 2.475 22.065 1.5 20.25 1.5H3.75C1.935 1.5.555 2.475.18 4.125L3.375 16.5c.45 1.8 2.025 2.925 3.825 2.925z" />
                </svg>
            </div>
            <h2 class="error-title">{{ title }}</h2>
            <p class="error-message">{{ message }}</p>
            <button class="retry-button" @click="onRetry">Tentar Novamente</button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            default: "Erro Inesperado"
        },
        message: {
            type: String,
            default: "Algo deu errado. Por favor, tente novamente."
        },
        retryAction: {
            type: Function,
            default: null
        }
    },
    methods: {
        onRetry() {
            if (this.retryAction) {
                this.retryAction();
            } else {
                console.error("Nenhuma ação de reinicialização foi definida.");
            }
        }
    }
};
</script>

<style scoped>
/* Container principal */
.error-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: #f9f9f9;
    padding: 20px;
}

/* Caixa de erro */
.error-box {
    max-width: 400px;
    text-align: center;
    background: white;
    border-radius: 15px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    padding: 20px;
}

/* Ícone de erro */
.icon-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 20px;
}
.error-icon {
    width: 50px;
    height: 50px;
    color: #e74c3c;
}

/* Título */
.error-title {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin-bottom: 10px;
}

/* Mensagem */
.error-message {
    font-size: 16px;
    color: #666;
    margin-bottom: 20px;
}

/* Botão de tentativa */
.retry-button {
    display: inline-block;
    background-color: #e74c3c;
    color: white;
    font-size: 16px;
    font-weight: bold;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
.retry-button:hover {
    background-color: #c0392b;
}
.retry-button:active {
    background-color: #a93226;
}
</style>
