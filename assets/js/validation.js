/**
 * TinyTrack — Contrôle de saisie JavaScript (Événements)
 * PAS de validation HTML5 (interdit par le prof)
 */

function showError(fieldId, message) {
    var field = document.getElementById(fieldId);
    var errDiv = document.getElementById('err_' + fieldId);
    if (field) { field.classList.add('is-invalid'); field.classList.remove('is-valid'); }
    if (errDiv) { errDiv.textContent = message; errDiv.style.display = 'block'; }
}

function clearError(fieldId) {
    var field = document.getElementById(fieldId);
    var errDiv = document.getElementById('err_' + fieldId);
    if (field) { field.classList.remove('is-invalid'); field.classList.add('is-valid'); }
    if (errDiv) { errDiv.textContent = ''; errDiv.style.display = 'none'; }
}

// Validate event form
function validerEvenement() {
    var ok = true;
    var fields = ['titre', 'date', 'heure_debut', 'heure_fin', 'lieu', 'capacite_max', 'prix'];
    fields.forEach(clearError);

    var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    var timeRegex = /^\d{1,2}:\d{2}(:\d{2})?$/;

    var titre = document.getElementById('titre').value.trim();
    if (!titre) { showError('titre', 'Le titre est obligatoire.'); ok = false; }
    else if (titre.length < 3) { showError('titre', 'Min. 3 caractères.'); ok = false; }

    var date = document.getElementById('date').value.trim();
    if (!date) { showError('date', 'La date est obligatoire.'); ok = false; }
    else if (!dateRegex.test(date)) { showError('date', 'Format: AAAA-MM-JJ'); ok = false; }

    var hd = document.getElementById('heure_debut').value.trim();
    if (!hd) { showError('heure_debut', "L'heure de début est obligatoire."); ok = false; }
    else if (!timeRegex.test(hd)) { showError('heure_debut', 'Format: HH:MM'); ok = false; }

    var hf = document.getElementById('heure_fin').value.trim();
    if (!hf) { showError('heure_fin', "L'heure de fin est obligatoire."); ok = false; }
    else if (!timeRegex.test(hf)) { showError('heure_fin', 'Format: HH:MM'); ok = false; }
    else if (hd && hf && hf <= hd) { showError('heure_fin', "Doit être après l'heure de début."); ok = false; }

    var lieu = document.getElementById('lieu').value.trim();
    if (!lieu) { showError('lieu', 'Le lieu est obligatoire.'); ok = false; }

    var cap = document.getElementById('capacite_max').value.trim();
    if (!cap) { showError('capacite_max', 'Obligatoire.'); ok = false; }
    else if (!/^\d+$/.test(cap) || parseInt(cap) < 1) { showError('capacite_max', 'Doit être un entier positif.'); ok = false; }

    var prix = document.getElementById('prix').value.trim();
    if (!prix) { showError('prix', 'Obligatoire.'); ok = false; }
    else if (isNaN(prix) || parseFloat(prix) < 0) { showError('prix', 'Doit être un nombre positif.'); ok = false; }

    // Check type (select or radio)
    var typeSelect = document.querySelector('select[name="type"]');
    var typeRadio = document.querySelector('input[name="type"]:checked');
    if (!typeSelect && !typeRadio) { ok = false; alert('Veuillez sélectionner un type.'); }
    else if (typeSelect && !typeSelect.value) { ok = false; alert('Veuillez sélectionner un type.'); }

    // Check statut (select or radio)
    var statutSelect = document.querySelector('select[name="statut"]');
    var statutRadio = document.querySelector('input[name="statut"]:checked');
    if (!statutSelect && !statutRadio) { ok = false; alert('Veuillez sélectionner un statut.'); }
    else if (statutSelect && !statutSelect.value) { ok = false; alert('Veuillez sélectionner un statut.'); }

    if (!ok) {
        var first = document.querySelector('.is-invalid');
        if (first) { first.scrollIntoView({ behavior: 'smooth', block: 'center' }); first.focus(); }
    }

    return ok;
}

// Real-time validation on blur
document.addEventListener('DOMContentLoaded', function() {
    var titre = document.getElementById('titre');
    if (titre) titre.addEventListener('blur', function() {
        if (!this.value.trim()) showError('titre', 'Obligatoire');
        else if (this.value.trim().length < 3) showError('titre', 'Min. 3 caractères');
        else clearError('titre');
    });

    var lieu = document.getElementById('lieu');
    if (lieu) lieu.addEventListener('blur', function() {
        if (!this.value.trim()) showError('lieu', 'Obligatoire');
        else clearError('lieu');
    });
});
