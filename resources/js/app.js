import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'; // Helper do Laravel para paths
import '../css/app.css'; // Importa o CSS principal/Bootstrap/etc.
import '@fortawesome/fontawesome-free/css/all.css';

// Tratamento global de erros para prevenir que erros impeçam a navegação
window.addEventListener('error', (event) => {
    console.error('Erro global capturado:', event.error);
    // Não previne o comportamento padrão para permitir navegação mesmo com erros
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('Promise rejeitada não tratada:', event.reason);
    // Não previne o comportamento padrão
});

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin);
        
        // Tratamento de erros global do Vue
        app.config.errorHandler = (err, instance, info) => {
            console.error('Erro Vue:', err);
            console.error('Info:', info);
            // Não lança o erro novamente para não quebrar a aplicação
        };
        
        app.mount(el);
    },
});
