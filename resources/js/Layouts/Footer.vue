<template>
    <footer class="footer text-center py-4">
        <div class="container">
            <!-- Logotipo ou Título -->
            <h2 class="footer-logo">DivanScore</h2>

            <!-- Menus sociais (exemplo) -->
            <div class="social-links mb-3">
                <a href="#" aria-label="Facebook" class="social-link">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" aria-label="Instagram" class="social-link">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" aria-label="Twitter" class="social-link">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" aria-label="LinkedIn" class="social-link">
                    <i class="fab fa-linkedin"></i>
                </a>
            </div>

            <!-- Direitos Autorais -->
            <p class="footer-text">
                &copy; 2023 <strong>DivanScore</strong> | Desenvolvido por <a href="#" class="footer-link">Zekke</a>
            </p>
        </div>
    </footer>
</template>

<style scoped>
/* Design moderno e colado ao fundo */
.footer {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #ffffff;
    padding: 1.5rem 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    position: relative;
    flex-shrink: 0;
    font-size: 0.9rem;
    box-shadow: 0 -10px 25px rgba(15, 23, 42, 0.2);
    transition: all 0.3s ease;
}

:global([data-theme="dark"]) .footer {
    background: linear-gradient(135deg, #1e1b4b, #4c1d95);
    box-shadow: 0 -10px 25px rgba(0, 0, 0, 0.6);
}

/* Efeito de hover no footer */
.footer:hover {
    background: linear-gradient(135deg, #4338ca, #6d28d9);
    transform: translateY(-2px);
}

/* Logotipo */
.footer-logo {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

/* Texto menor */
.footer-text {
    font-size: 0.85rem;
    opacity: 0.85;
    margin-top: 1rem;
}

/* Stylização dos links */
.footer-link {
    color: #f8fafc;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-link:hover {
    color: #e2e8f0;
}

/* Ícones sociais */
.social-links {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.social-link {
    color: #ffffff;
    font-size: 1.5rem;
    transition: color 0.3s ease, transform 0.3s ease;
}

.social-link:hover {
    color: #f8fafc;
    transform: scale(1.1);
}
</style>
