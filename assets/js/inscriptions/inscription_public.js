// Helpers
function showMessage(text, type) {
    const el = document.getElementById('message');
    el.textContent = text;
    el.className = 'message ' + type + ' show';
    setTimeout(() => el.classList.remove('show'), 4000);
}

function calculerAge(dateNaissance) {
    const today = new Date();
    const dob = new Date(dateNaissance);
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
    return age;
}

// Stockage des données de classes (id → {age_minimum, age_maximum, nom})
let classesData = {};

// Vérifie la compatibilité âge/classe et affiche un avertissement en temps réel
function verifierCompatibiliteAgeClasse() {
    const date   = document.getElementById('enfant_date_naissance').value;
    const classeId = document.getElementById('classe_id').value;
    const msgEl  = document.getElementById('message');

    if (!date || !classeId) return;

    const classe = classesData[String(classeId)];
    if (!classe || (!classe.age_minimum && !classe.age_maximum)) return;

    const age = calculerAge(date);
    const min = classe.age_minimum;
    const max = classe.age_maximum;

    if (age < min || age > max) {
        showMessage(
            `Incompatibilité : l'enfant a ${age} ans mais la classe "${classe.nom}" accepte les ${min}-${max} ans.`,
            'error'
        );
    } else {
        // Effacer le message d'erreur si tout est bon
        if (msgEl.classList.contains('error')) {
            msgEl.classList.remove('show');
        }
    }
}

// Validation
function validerFormulaire() {
    const nom    = document.getElementById('enfant_nom').value.trim();
    const prenom = document.getElementById('enfant_prenom').value.trim();
    const date   = document.getElementById('enfant_date_naissance').value;
    const classeId = document.getElementById('classe_id').value;

    if (!nom) {
        showMessage('Le nom est obligatoire', 'error');
        return false;
    }
    if (!prenom) {
        showMessage('Le prénom est obligatoire', 'error');
        return false;
    }
    if (!date) {
        showMessage('La date de naissance est obligatoire', 'error');
        return false;
    }
    if (!classeId) {
        showMessage('La classe est obligatoire', 'error');
        return false;
    }

    // Validation nom/prénom (lettres uniquement, min 2 caractères)
    if (!/^[a-zA-ZÀ-ÿ\s'-]{2,}$/.test(nom)) {
        showMessage('Le nom doit contenir au moins 2 lettres', 'error');
        return false;
    }
    if (!/^[a-zA-ZÀ-ÿ\s'-]{2,}$/.test(prenom)) {
        showMessage('Le prénom doit contenir au moins 2 lettres', 'error');
        return false;
    }

    const dob   = new Date(date);
    const today = new Date();

    if (dob > today) {
        showMessage('La date de naissance ne peut pas être dans le futur', 'error');
        return false;
    }

    const age = calculerAge(date);
    if (age < 2) {
        showMessage("L'enfant doit avoir au moins 2 ans", 'error');
        return false;
    }
    if (age > 6) {
        showMessage("L'enfant ne peut pas avoir plus de 6 ans", 'error');
        return false;
    }

    // Validation âge / classe
    const classe = classesData[String(classeId)];
    if (classe && (classe.age_minimum || classe.age_maximum)) {
        const min = classe.age_minimum;
        const max = classe.age_maximum;
        if (age < min || age > max) {
            showMessage(
                `L'enfant a ${age} ans mais la classe "${classe.nom}" accepte les ${min}-${max} ans. Veuillez choisir la bonne classe.`,
                'error'
            );
            return false;
        }
    }

    // Validation téléphone (si renseigné)
    // Format accepté : +213 555 123 456 ou 0555-123-456 (8 à 15 chiffres)
    const tel = document.getElementById('contact_urgence_telephone').value.trim();
    if (tel) {
        const digitsOnly = tel.replace(/[\s\-\(\)]/g, '');
        if (!/^\+?[0-9]{8,15}$/.test(digitsOnly)) {
            showMessage('Le numéro de téléphone est invalide (ex: +213 555 123 456)', 'error');
            return false;
        }
    }

    return true;
}

// Soumission
document.getElementById('inscriptionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!validerFormulaire()) return;

    const champs = [
        'enfant_nom', 'enfant_prenom', 'enfant_date_naissance',
        'groupe_sanguin', 'allergies_alimentaires', 'allergies_medicales',
        'maladies_chroniques', 'vaccinations', 'contact_urgence_nom',
        'contact_urgence_lien', 'contact_urgence_telephone', 'aliments_preferes',
        'aliments_interdits', 'horaire_sieste', 'notes_sante', 'notes_speciales'
    ];

    const data = new FormData();
    champs.forEach(id => data.append(id, document.getElementById(id).value.trim()));
    data.append('classe_id', document.getElementById('classe_id').value);
    data.append('autorise_photos',  document.getElementById('autorise_photos').checked ? 1 : 0);
    data.append('autorise_sorties', document.getElementById('autorise_sorties').checked ? 1 : 0);

    fetch('./api/save_enfant.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(d => {
            if (d.succes) {
                showMessage(d.message, 'success');
                document.getElementById('inscriptionForm').reset();
                chargerClasses();
            } else {
                showMessage(d.message, 'error');
            }
        })
        .catch(() => showMessage('Erreur de connexion au serveur', 'error'));
});

// Charger classes avec les données d'âge complètes
function chargerClasses() {
    fetch('./api/get_classes.php')
        .then(r => r.json())
        .then(d => {
            const select = document.getElementById('classe_id');
            select.innerHTML = '<option value="">Sélectionner</option>';
            classesData = {};
            if (d.succes && d.classes) {
                d.classes.forEach(classe => {
                    // Clé en string pour correspondre à select.value qui est toujours string
                    classesData[String(classe.id)] = {
                        nom:         classe.nom,
                        age_minimum: parseInt(classe.age_minimum),
                        age_maximum: parseInt(classe.age_maximum)
                    };
                    const ageInfo = (classe.age_minimum && classe.age_maximum)
                        ? ` (${classe.age_minimum}-${classe.age_maximum} ans)`
                        : '';
                    select.innerHTML += `<option value="${classe.id}">${classe.nom}${ageInfo}</option>`;
                });
            }
        })
        .catch(() => console.error('Erreur chargement classes'));
}

// Vérification en temps réel quand l'utilisateur change la date ou la classe
document.getElementById('enfant_date_naissance').addEventListener('change', verifierCompatibiliteAgeClasse);
document.getElementById('classe_id').addEventListener('change', verifierCompatibiliteAgeClasse);

// Init
chargerClasses();
