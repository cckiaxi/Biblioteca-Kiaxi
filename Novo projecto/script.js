document.addEventListener('DOMContentLoaded', () => {
    
    // INTERATIVIDADE: MODO ESCURO (DARK MODE) ---
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeIcon = themeToggleBtn.querySelector('i');
    
    // Verifica se o utilizador já tinha uma preferência salva
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    themeToggleBtn.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
        if (theme === 'dark') {
            themeIcon.className = 'fas fa-sun';
        } else {
            themeIcon.className = 'fas fa-moon';
        }
    }

    // 2. INTERATIVIDADE: FORMULÁRIO DE CONTACTO
    const contactForm = document.getElementById('contact-form');
    const formFeedback = document.getElementById('form-feedback');

    contactForm.addEventListener('submit', (e) => {
        e.preventDefault(); // faz a página não recarregar

        // Captura dos dados inseridos
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const message = document.getElementById('message').value.trim();

        // Simulação de validação/envio técnico
        if (name && email && message) {
            // Sucesso simulado
            formFeedback.textContent = `Obrigado, ${name}! A sua mensagem foi enviada com sucesso.`;
            formFeedback.className = 'success';
            
            // Limpa os campos do formulário
            contactForm.reset();
        } else {
            // Falha simulada
            formFeedback.textContent = 'Por favor, preencha todos os campos corretamente.';
            formFeedback.className = 'error';
        }

        // Remove a mensagem após 5 segundos
        setTimeout(() => {
            formFeedback.className = 'hidden';
        }, 5000);
    });
});