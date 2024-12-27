<template>
    <button :class="['modern-btn', `btn-${type}`]" :disabled="disabled">
        <slot />
    </button>
</template>

<script>
export default {
    props: {
        type: {
            type: String,
            default: 'primary',
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    },
};
</script>

<style scoped>
/* Estilo base do botão */
.modern-btn {
    display: inline-block;
    padding: 0.75rem 1.25rem;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: bold;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    text-transform: uppercase;
}

/* Tipos de botão */

/* Botão Primary */
.btn-primary {
    background: linear-gradient(45deg, #6a1b9a, #8e44ad);
    color: #ffffff;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #8e44ad, #6a1b9a);
    transform: translateY(-2px);
    box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.15);
}

.btn-primary:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(106, 27, 154, 0.2);
}

/* Botão Secondary */
.btn-secondary {
    background: linear-gradient(45deg, #34495e, #2c3e50);
    color: #ffffff;
}

.btn-secondary:hover {
    background: linear-gradient(45deg, #2c3e50, #34495e);
    transform: translateY(-2px);
    box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.15);
}

.btn-secondary:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(52, 73, 94, 0.2);
}

/* Botão Success */
.btn-success {
    background: linear-gradient(45deg, #28a745, #218838);
    color: #ffffff;
}

.btn-success:hover {
    background: linear-gradient(45deg, #218838, #28a745);
    transform: translateY(-2px);
    box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.15);
}

.btn-success:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.2);
}

/* Botão Warning */
.btn-warning {
    background: linear-gradient(45deg, #ffc107, #e0a800);
    color: #212529;
}

.btn-warning:hover {
    background: linear-gradient(45deg, #e0a800, #ffc107);
    transform: translateY(-2px);
    box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.15);
}

.btn-warning:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.2);
}

/* Estados */

/* Desabilitado */
.modern-btn:disabled {
    background: #e0e0e0;
    color: #aaaaaa;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}

/* Animações */
.modern-btn:hover {
    transition: all 0.3s ease;
}
</style>
