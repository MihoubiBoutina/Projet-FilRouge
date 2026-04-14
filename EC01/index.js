
// GESTION DE LA FENÊTRE MODALE (DIALOG)

/**
 * Ouvre la fenêtre modale d'inscription apprenant
 * Fonction appelée lors du clic sur le bouton "Inscription Apprenant"
 */
function onClickButtonInscriptionApprenant() {
    const dialog = document.getElementById('dialog');
    if (dialog) {
        dialog.showModal();
    }
}

/**
 * Ferme la fenêtre modale d'inscription
 * Fonction utilisée par le bouton "Annuler" et la croix de fermeture
 */

function closeDialog() {
    const dialog = document.getElementById('dialog');
    if (dialog) {
        dialog.close();
    }
}

// GESTION DES ÉVÉNEMENTS AU CHARGEMENT DE LA PAGE

document.addEventListener('DOMContentLoaded', function() {
    const dialog = document.getElementById('dialog');
    if (dialog) {
        dialog.addEventListener('click', function(event) {
            if (event.target === dialog) {
                dialog.close();
            }
        });

    }
});

// GESTION DU HEADER AU SCROLL (STICKY HEADER)

/**
 * Ajoute ou retire la classe "sticky" au header
 * en fonction de la position de scroll de la page
 */

window.addEventListener('scroll', function(){
    const header = document.querySelector('header');
    header.classList.toggle("sticky", window.scrollY > 0);
})

// GESTION DU MENU RESPONSIVE (BURGER)

/**
 * Ouvre et ferme le menu mobile
 * Active l'animation du bouton burger
 * et affiche ou masque la navigation
 */

function toggleMenu(){
    const menutoggle = document.querySelector('.menutoggle');
    const navbar = document.querySelector('.navbar');
    menutoggle.classList.toggle('active');
    navbar.classList.toggle('active')
    document.body.classList.toggle('menu-open');

}

// GESTION DU FORMULAIRE D'INSCRIPTION

/**
 * Gère la soumission du formulaire d'inscription apprenant
 * Empêche le rechargement de la page
 * Affiche un message de confirmation
 * Ferme la modale et réinitialise le formulaire
 */

function submitInscriptionBtn(event) {
    event.preventDefault(); // empêche le rechargement de la page

    // Récupération des valeurs saisies par l'utilisateur
    const nom = document.getElementById('nom').value;
    const email = document.getElementById('email').value;

    // Vérification simple des champs
    if (nom && email) {
        // Message de confirmation d'inscription
        alert("Inscription réussie 🎉\nBienvenue " + nom + " !");

        closeDialog();       // ferme la popup
        event.target.reset(); // vide le formulaire
    }
}
