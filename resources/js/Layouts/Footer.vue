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
                &copy; 2023 <strong>DivanScore</strong> | Desenvolvido com ❤️ por <a href="#" class="footer-link">Zekke</a>
            </p>
        </div>
    </footer>
</template>

<style scoped>
/* Design moderno e colado ao fundo */
.footer {
    background: linear-gradient(45deg, #6a1b9a, #8e44ad);
    color: #ffffff;
    padding: 1.5rem 1rem;
    border-top: 5px solid rgba(255, 255, 255, 0.15);
    position: relative;
    flex-shrink: 0;
    font-size: 0.9rem;
    box-shadow: 0 -5px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

/* Efeito de hover no footer */
.footer:hover {
    background: linear-gradient(45deg, #8e44ad, #6a1b9a);
    transform: translateY(-3px);
}

/* Logotipo */
.footer-logo {
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 1rem;
    text-transform: uppercase;
}

/* Texto menor */
.footer-text {
    font-size: 0.85rem;
    opacity: 0.85;
    margin-top: 1rem;
}

/* Stylização dos links */
.footer-link {
    color: #ffd700;
    font-weight: bold;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-link:hover {
    color: #ffffff; /* Fica branco ao passar o mouse */
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
    color: #ffd700;
    transform: scale(1.1);
}
</style>
