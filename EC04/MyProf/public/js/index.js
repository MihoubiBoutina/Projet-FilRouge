console.log("Chatbot chargé !");

document.addEventListener("DOMContentLoaded", () => {
  // ===== Burger menu =====
  const header = document.querySelector(".site-header");
  const burger = document.querySelector(".burger");
  const links = document.querySelectorAll(".navbar a");

  if (burger && header) {
    burger.addEventListener("click", () => {
      const isOpen = header.classList.toggle("menu-open");
      burger.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });

    // Ferme le menu quand on clique un lien
    links.forEach((a) => {
      a.addEventListener("click", () => {
        header.classList.remove("menu-open");
        burger.setAttribute("aria-expanded", "false");
      });
    });
  }

  // Optionnel : effet au scroll (si header existe)
  if (header) {
    window.addEventListener("scroll", () => {
      header.classList.toggle("is-scrolled", window.scrollY > 10);
    });
  }

  // ===== Flash messages auto-hide =====
  const flashes = document.querySelectorAll(".flash");
  flashes.forEach((flash) => {
    const visibleTime = 1000; // 1 secondes

    setTimeout(() => {
      flash.classList.add("hide");
      setTimeout(() => flash.remove(), 600);
    }, visibleTime);
  });

  // ===== Inscription modal (si utilisé) =====
  // Note: cette fonction doit être appelée par un addEventListener sur ton form
  window.submitInscriptionBtn = function (event) {
    event.preventDefault();
    console.log("Inscription");

    const nom = document.getElementById("nom")?.value;
    const email = document.getElementById("email")?.value;
    console.log("Nom :", nom, "Email :", email);

    const dialog = document.getElementById("dialog");
    if (dialog) dialog.close();
    };
});



document.addEventListener('DOMContentLoaded', () => {
    const chatSendBtn = document.getElementById('chat-send');
    if (chatSendBtn) {
        chatSendBtn.addEventListener('click', async (event) => {

             // 1. Afficher le message de l'utilisateur
    window.innerHTML += `<div style="background: #007bff; color: white; padding: 8px; border-radius: 10px; align-self: flex-end;">${message}</div>`;
    input.value = '';

    // 2. Envoyer à Symfony
    const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ 'user_message': message })
    });
    

    // 3. Afficher la réponse de l'IA (On prend le premier match trouvé)
    if(data.matches && data.matches.length > 0) {
        const topMatch = data.matches[0];
        window.innerHTML += `<div style="background: #f1f1f1; padding: 8px; border-radius: 10px; align-self: flex-start;">
            Je vous conseille : <strong>${topMatch.reason}</strong>
        </div>`;
    }
    window.scrollTop = window.scrollHeight; // Scroll automatique vers le bas
        });
    }
});


    