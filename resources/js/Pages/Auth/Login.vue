<template>
    <div class="login-page">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h1 class="login-title">DivanScore</h1>
                    <p class="login-subtitle">Faça login para continuar</p>
                </div>

                <form @submit.prevent="submit" class="login-form">
                    <div class="form-group">
                        <label for="email" class="form-label">E-mail</label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.email }"
                            placeholder="seu@email.com"
                            required
                            autofocus
                        />
                        <div v-if="form.errors.email" class="invalid-feedback">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Senha</label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.password }"
                            placeholder="••••••••"
                            required
                        />
                        <div v-if="form.errors.password" class="invalid-feedback">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div class="form-group remember-group">
                        <label class="remember-label">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="remember-checkbox"
                            />
                            <span>Lembrar-me</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="login-button"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="spinner"></span>
                        <span v-else>Entrar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';

export default {
    setup() {
        const form = useForm({
            email: '',
            password: '',
            remember: false,
        });

        const submit = () => {
            form.post('/login', {
                onFinish: () => form.reset('password'),
            });
        };

        return {
            form,
            submit,
        };
    },
};
</script>

<style scoped>
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    padding: 1rem;
}

.login-container {
    width: 100%;
    max-width: 420px;
}

.login-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.login-header {
    text-align: center;
    margin-bottom: 2rem;
}

.login-title {
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(45deg, #6a1b9a, #8e44ad, #e91e63);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
}

.login-subtitle {
    color: rgba(255, 255, 255, 0.6);
    font-size: 1rem;
    margin: 0;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.875rem;
    font-weight: 500;
}

.form-control {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 0.875rem 1rem;
    color: #ffffff;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

.form-control:focus {
    outline: none;
    border-color: #8e44ad;
    background: rgba(255, 255, 255, 0.12);
    box-shadow: 0 0 0 3px rgba(142, 68, 173, 0.2);
}

.form-control.is-invalid {
    border-color: #e74c3c;
}

.invalid-feedback {
    color: #e74c3c;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.remember-group {
    flex-direction: row;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
    font-size: 0.875rem;
}

.remember-checkbox {
    width: 18px;
    height: 18px;
    accent-color: #8e44ad;
    cursor: pointer;
}

.login-button {
    background: linear-gradient(45deg, #6a1b9a, #8e44ad);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    padding: 1rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.login-button:hover:not(:disabled) {
    background: linear-gradient(45deg, #8e44ad, #6a1b9a);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(142, 68, 173, 0.3);
}

.login-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid transparent;
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Responsividade */
@media (max-width: 480px) {
    .login-card {
        padding: 1.5rem;
    }

    .login-title {
        font-size: 2rem;
    }
}

/* Tema claro */
[data-theme="light"] .login-page {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

[data-theme="light"] .login-card {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(0, 0, 0, 0.1);
}

[data-theme="light"] .login-subtitle {
    color: rgba(0, 0, 0, 0.6);
}

[data-theme="light"] .form-label {
    color: rgba(0, 0, 0, 0.8);
}

[data-theme="light"] .form-control {
    background: rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.15);
    color: #1a1a2e;
}

[data-theme="light"] .form-control::placeholder {
    color: rgba(0, 0, 0, 0.4);
}

[data-theme="light"] .remember-label {
    color: rgba(0, 0, 0, 0.7);
}
</style>
