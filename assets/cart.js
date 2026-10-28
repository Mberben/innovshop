/*
 * Panier en AJAX (fetch) : ajout et suppression sans recharger la page.
 * Les formulaires restent de vrais formulaires : sans JavaScript, ils fonctionnent
 * quand même (le contrôleur renvoie alors une redirection au lieu de JSON).
 */

function updateCount(count) {
    document.querySelectorAll('[data-cart-count]').forEach((el) => {
        el.textContent = count;
        el.classList.add('bump');
        setTimeout(() => el.classList.remove('bump'), 250);
    });
}

function notify(message, success = true) {
    const toastEl = document.getElementById('cart-toast');
    if (!toastEl || !window.bootstrap) {
        return;
    }
    toastEl.querySelector('[data-toast-message]').textContent = message;
    toastEl.classList.toggle('toast-success', success);
    toastEl.classList.toggle('toast-error', !success);
    window.bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2500 }).show();
}

async function send(form) {
    const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });

    return response.json();
}

document.addEventListener('submit', async (event) => {
    const form = event.target;

    // Ajout au panier
    if (form.matches('[data-cart-add]')) {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        try {
            const data = await send(form);
            notify(data.message, data.success);
            if (data.success) {
                updateCount(data.count);
            }
        } catch (e) {
            form.submit(); // en cas de problème réseau : envoi classique
        } finally {
            button.disabled = false;
        }
    }

    // Suppression d'une ligne
    if (form.matches('[data-cart-remove]')) {
        event.preventDefault();
        try {
            const data = await send(form);
            if (!data.success) {
                notify(data.message, false);
                return;
            }
            const line = form.closest('[data-cart-line]');
            line.classList.add('removing');
            setTimeout(() => {
                line.remove();
                if (data.count === 0) {
                    window.location.reload(); // affiche l'état « panier vide »
                }
            }, 250);
            updateCount(data.count);
            document.querySelectorAll('[data-cart-total]').forEach((el) => { el.textContent = data.total; });
        } catch (e) {
            form.submit();
        }
    }
});

// Galerie de la fiche produit : clic sur une miniature = image principale
document.addEventListener('click', (event) => {
    const thumb = event.target.closest('[data-gallery-thumb]');
    if (!thumb) {
        return;
    }
    const gallery = thumb.closest('[data-gallery]');
    gallery.querySelector('[data-gallery-main]').src = thumb.dataset.galleryThumb;
    gallery.querySelectorAll('[data-gallery-thumb]').forEach((t) => t.classList.toggle('active', t === thumb));

    // Synchronisation inverse : la photo cliquée sélectionne l'option correspondante (ex. Rouge)
    const select = document.querySelector('[data-option-select]');
    if (select) {
        const match = Array.from(select.options).find((o) => o.dataset.image === thumb.dataset.galleryThumb);
        if (match) {
            select.value = match.value;
        }
    }
});

// Fiche produit : choisir une option (ex. couleur Blanc) affiche la photo correspondante
document.addEventListener('change', (event) => {
    const select = event.target.closest('[data-option-select]');
    const gallery = document.querySelector('[data-gallery]');
    if (!select || !gallery) {
        return;
    }
    const image = select.selectedOptions[0]?.dataset.image;
    if (!image) {
        return;
    }
    gallery.querySelector('[data-gallery-main]').src = image;
    gallery.querySelectorAll('[data-gallery-thumb]').forEach((t) => t.classList.toggle('active', t.dataset.galleryThumb === image));
});

// Au chargement (ou retour arrière du navigateur), l'image suit l'option déjà sélectionnée
const initialSelect = document.querySelector('[data-option-select]');
if (initialSelect && initialSelect.value) {
    initialSelect.dispatchEvent(new Event('change', { bubbles: true }));
}
