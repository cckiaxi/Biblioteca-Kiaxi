function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.toggle-password');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = 'visibility_off'; // Muda o ícone para "olho cortado"
    } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = 'visibility'; // Volta para o ícone de "olho aberto"
    }
}