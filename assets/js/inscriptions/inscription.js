// ── Helpers ──────────────────────────────────────────────────────────────────

function showMessage(text, type) {
    const el = document.getElementById('message');
    el.textContent = text;
    el.className = 'message ' + type + ' show';
    setTimeout(() => el.classList.remove('show'), 4000);
}

function showMessageClasse(text, type) {
    const el = document.getElementById('messageClasse');
    el.textContent = text;
    el.className = 'message ' + type + ' show';
    setTimeout(() => el.classList.remove('show'), 4000);
}

function calculerAge(dateNaissance) {
    const today = new Date();
    const dob   = new Date(dateNaissance);
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
    return age;
}

function infoRow(label, value) {
    if (!value && value !== 0) return '';
    return `<div class="info-row"><span class="label">${label}:</span><span class="value">${value}</span></div>`;
}

// Stockage des données de classes (id → {age_minimum, age_maximum, nom})
let classesData = {};

// ── Detail renderer ───────────────────────────────────────────────────────────

function renderDetails(enfant, avecSuppression = false) {
    const age          = calculerAge(enfant.enfant_date_naissance);
    const dateAffichee = new Date(enfant.enfant_date_naissance).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });
    const classeText   = enfant.classe_nom || 'Non assigné';

    const hasMedical = enfant.groupe_sanguin || enfant.allergies_alimentaires || enfant.allergies_medicales
                    || enfant.maladies_chroniques || enfant.vaccinations || enfant.notes_sante;
    const hasUrgence = enfant.contact_urgence_nom || enfant.contact_urgence_telephone;
    const hasPrefs   = enfant.aliments_preferes || enfant.aliments_interdits || enfant.horaire_sieste;

    return `
        <div class="header">
            <div class="avatar">&#128100;</div>
            <h1>${enfant.enfant_prenom} ${enfant.enfant_nom}</h1>
            <p style="color:#888">${classeText}</p>
        </div>

        <div class="card">
            <h2>Informations Personnelles</h2>
            ${infoRow('Nom', enfant.enfant_nom)}
            ${infoRow('Prénom', enfant.enfant_prenom)}
            ${infoRow('Date de naissance', dateAffichee)}
            <div class="info-row"><span class="label">Âge:</span><span class="value"><span class="age-badge">${age} ans</span></span></div>
            ${infoRow('Classe', classeText)}
        </div>

        ${hasMedical ? `<div class="card urgent">
            <h2> Dossier Médical</h2>
            ${enfant.groupe_sanguin ? `<div class="info-row"><span class="label">Groupe sanguin:</span><span class="value"><strong>${enfant.groupe_sanguin}</strong></span></div>` : ''}
            ${infoRow('Allergies alimentaires', enfant.allergies_alimentaires)}
            ${infoRow('Allergies médicales', enfant.allergies_medicales)}
            ${infoRow('Maladies chroniques', enfant.maladies_chroniques)}
            ${infoRow('Vaccinations', enfant.vaccinations)}
            ${infoRow('Notes de santé', enfant.notes_sante)}
        </div>` : ''}

        ${hasUrgence ? `<div class="card urgent">
            <h2>Contact d'Urgence</h2>
            ${infoRow('Nom', enfant.contact_urgence_nom)}
            ${infoRow('Lien', enfant.contact_urgence_lien)}
            ${enfant.contact_urgence_telephone ? `<div class="info-row"><span class="label">Téléphone:</span><span class="value"><a href="tel:${enfant.contact_urgence_telephone}" style="color:#dc3545;text-decoration:none"><strong>${enfant.contact_urgence_telephone}</strong></a></span></div>` : ''}
        </div>` : ''}

        ${hasPrefs ? `<div class="card">
            <h2> Préférences & Habitudes</h2>
            ${infoRow('Aliments préférés', enfant.aliments_preferes)}
            ${infoRow('Aliments à éviter', enfant.aliments_interdits)}
            ${infoRow('Horaire de sieste', enfant.horaire_sieste)}
        </div>` : ''}

        <div class="card">
            <h2>Autorisations</h2>
            <div class="info-row"><span class="label">Photos:</span><span class="value">${parseInt(enfant.autorise_photos) ? 'Autorisé' : 'Non autorisé'}</span></div>
            <div class="info-row"><span class="label">Sorties:</span><span class="value">${parseInt(enfant.autorise_sorties) ? 'Autorisé' : 'Non autorisé'}</span></div>
        </div>

        ${enfant.notes_speciales ? `<div class="card">
            <h2> Observations Spéciales</h2>
            <div class="info-row" style="display:block"><span class="value" style="white-space:pre-wrap">${enfant.notes_speciales}</span></div>
        </div>` : ''}

        ${avecSuppression ? `<div class="action-buttons">
            <button class="btn-delete" onclick="supprimer(${enfant.id})">Supprimer</button>
        </div>` : ''}
    `;
}

function renderClasseDetails(classe) {
    const enfantsHtml = classe.enfants && classe.enfants.length > 0 
        ? classe.enfants.map(enfant => `
            <tr>
                <td><strong>${enfant.enfant_prenom} ${enfant.enfant_nom}</strong></td>
                <td>${new Date(enfant.enfant_date_naissance).toLocaleDateString('fr-FR')}</td>
                <td>${calculerAge(enfant.enfant_date_naissance)} ans</td>
                <td>${enfant.groupe_sanguin || '-'}</td>
                <td>
                    <button class="btn-view" onclick="afficherDetails(${enfant.id})">Détails</button>
                </td>
            </tr>
        `).join('')
        : '<tr><td colspan="5" class="empty-message">Aucun enfant dans cette classe</td></tr>';

    return `
        <div class="header">
            <div class="avatar">&#128100;</div>
            <h1>${classe.nom}</h1>
            <p style="color:#888">Couleur: ${classe.couleur}</p>
        </div>

        <div class="card">
            <h2>Informations de la Classe</h2>
            ${infoRow('Nom', classe.nom)}
            ${infoRow('Couleur', classe.couleur)}
            ${infoRow('Description', classe.description)}
            ${classe.age_minimum && classe.age_maximum ? infoRow('Âges acceptés', `${classe.age_minimum} - ${classe.age_maximum} ans`) : ''}
            ${infoRow('Capacité maximum', classe.capacite_max)}
            ${infoRow('Éducateur principal', classe.educateur_principal)}
            ${infoRow('Salle', classe.salle)}
            ${infoRow('Horaires', classe.horaires)}
        </div>

        <div class="card">
            <h2>Enfants Inscrits (${classe.enfants ? classe.enfants.length : 0})</h2>
            <table style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th>Enfant</th>
                        <th>Date Naissance</th>
                        <th>Âge</th>
                        <th>Groupe Sanguin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${enfantsHtml}
                </tbody>
            </table>
        </div>

        <div class="action-buttons">
            <button class="btn-delete" onclick="supprimerClasse(${classe.id})">Supprimer Classe</button>
        </div>
    `;
}

// ── Navigation ────────────────────────────────────────────────────────────────

function switchTab(e, tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    e.target.classList.add('active');
    
    // Masquer toutes les vues
    document.getElementById('inscriptionForm').style.display = 'none';
    document.getElementById('tableView').style.display = 'none';
    document.getElementById('classesView').style.display = 'none';
    
    // Afficher la vue correspondante
    if (tab === 'list') {
        document.getElementById('tableView').style.display = 'block';
        charger();
    } else if (tab === 'classes') {
        document.getElementById('classesView').style.display = 'block';
        chargerClasses();
    }
}

function afficherFormulaire() {
    document.getElementById('formView').classList.add('active');
    document.getElementById('detailsView').classList.remove('active');
    document.getElementById('classeFormView').classList.remove('active');
    document.getElementById('classeDetailsView').classList.remove('active');
    
    // Cacher le formulaire d'inscription, afficher la liste
    document.getElementById('inscriptionForm').style.display = 'none';
    
    // Afficher l'onglet enfants par défaut
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-btn')[0].classList.add('active');
    
    document.getElementById('tableView').style.display = 'block';
    document.getElementById('classesView').style.display = 'none';
    
    charger();
}

function afficherClasses() {
    document.getElementById('formView').classList.add('active');
    document.getElementById('detailsView').classList.remove('active');
    document.getElementById('classeFormView').classList.remove('active');
    document.getElementById('classeDetailsView').classList.remove('active');
    
    // Activer l'onglet classes
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-btn')[1].classList.add('active');
    
    document.getElementById('tableView').style.display = 'none';
    document.getElementById('classesView').style.display = 'block';
    
    chargerClasses();
}

function afficherFormulaireClasse() {
    document.getElementById('formView').classList.remove('active');
    document.getElementById('classeFormView').classList.add('active');
    document.getElementById('classeForm').reset();
}

function afficherNouvelleInscription() {
    document.getElementById('tableView').style.display = 'none';
    document.getElementById('classesView').style.display = 'none';
    document.getElementById('inscriptionForm').style.display = 'block';
    chargerClassesPourSelect();
}

function cacherFormulaireEnfant() {
    document.getElementById('inscriptionForm').style.display = 'none';
    document.getElementById('tableView').style.display = 'block';
    charger();
}

function afficherDetails(id) {
    fetch(`/TinyTrack/api/inscriptions/get_details_enfant.php?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.succes && data.enfant) {
                document.getElementById('detailsContent').innerHTML = renderDetails(data.enfant, true);
                document.getElementById('formView').classList.remove('active');
                document.getElementById('detailsView').classList.add('active');
            } else {
                showMessage('' + (data.message || 'Enfant non trouvé'), 'error');
            }
        })
        .catch(() => showMessage('Impossible de contacter le serveur. Ouvrez ce fichier via http://localhost et non directement.', 'error'));
}

function afficherDetailsClasse(id) {
    fetch(`/TinyTrack/api/inscriptions/get_details_classe.php?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.succes && data.classe) {
                document.getElementById('classeDetailsContent').innerHTML = renderClasseDetails(data.classe);
                document.getElementById('formView').classList.remove('active');
                document.getElementById('classeDetailsView').classList.add('active');
            } else {
                showMessageClasse('' + (data.message || 'Classe non trouvée'), 'error');
            }
        })
        .catch(() => showMessageClasse('Impossible de contacter le serveur.', 'error'));
}

function afficherDetailsEnfantFromClasse(id) {
    fetch(`/TinyTrack/api/inscriptions/get_details_enfant.php?id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.succes && data.enfant) {
                document.getElementById('detailsContent').innerHTML = renderDetails(data.enfant, false);
                document.getElementById('formView').classList.remove('active');
                document.getElementById('detailsView').classList.add('active');
            } else {
                showMessage('' + (data.message || 'Enfant non trouvé'), 'error');
            }
        })
        .catch(() => showMessage('Impossible de contacter le serveur.', 'error'));
}

// ── Validation JS uniquement ──────────────────────────────────────────────────

// Vérifie la compatibilité âge/classe en temps réel
function verifierCompatibiliteAgeClasse() {
    const date     = document.getElementById('enfant_date_naissance').value;
    const classeId = document.getElementById('classe_id').value;
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
        const msgEl = document.getElementById('message');
        if (msgEl.classList.contains('error')) msgEl.classList.remove('show');
    }
}

function validerFormulaire() {
    const nom    = document.getElementById('enfant_nom').value.trim();
    const prenom = document.getElementById('enfant_prenom').value.trim();
    const date   = document.getElementById('enfant_date_naissance').value;
    const classe = document.getElementById('classe_id').value;

    if (!nom)    { showMessage('Le nom est obligatoire', 'error');               return false; }
    if (!prenom) { showMessage('Le prénom est obligatoire', 'error');            return false; }
    if (!date)   { showMessage('La date de naissance est obligatoire', 'error'); return false; }
    if (!classe) { showMessage('La classe est obligatoire', 'error');            return false; }

    // Validation du nom (lettres uniquement, min 2 caractères)
    if (!/^[a-zA-ZÀ-ÿ\s'-]{2,}$/.test(nom)) {
        showMessage('Le nom doit contenir au moins 2 lettres', 'error');
        return false;
    }
    
    // Validation du prénom (lettres uniquement, min 2 caractères)
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
    if (calculerAge(date) < 2) {
        showMessage("L'enfant doit avoir au moins 2 ans", 'error');
        return false;
    }
    if (calculerAge(date) > 6) {
        showMessage("L'enfant ne peut pas avoir plus de 6 ans", 'error');
        return false;
    }

    // Validation âge / classe
    const classeInfo = classesData[String(classe)];
    if (classeInfo && (classeInfo.age_minimum || classeInfo.age_maximum)) {
        const age = calculerAge(date);
        const min = classeInfo.age_minimum;
        const max = classeInfo.age_maximum;
        if (age < min || age > max) {
            showMessage(
                ` L'enfant a ${age} ans mais la classe "${classeInfo.nom}" accepte les ${min}-${max} ans.`,
                'error'
            );
            return false;
        }
    }

    // Validation du téléphone d'urgence (si renseigné)
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

function validerFormulaireClasse() {
    const nom      = document.getElementById('classe_nom').value.trim();
    const couleur  = document.getElementById('classe_couleur').value.trim();
    const educateur = document.getElementById('educateur_principal').value.trim();
    const horaires = document.getElementById('horaires').value.trim();
    const ageMin   = document.getElementById('age_minimum').value;
    const ageMax   = document.getElementById('age_maximum').value;
    const capacite = document.getElementById('capacite_max').value;

    if (!nom)    { showMessageClasse('Le nom est obligatoire', 'error');    return false; }
    if (!couleur){ showMessageClasse('La couleur est obligatoire', 'error'); return false; }

    // Validation du nom de la classe (min 2 caractères)
    if (nom.length < 2) {
        showMessageClasse('Le nom doit contenir au moins 2 caractères', 'error');
        return false;
    }

    // Validation de l'éducateur (si renseigné)
    if (educateur && !/^[a-zA-ZÀ-ÿ\s'-]{2,}$/.test(educateur)) {
        showMessageClasse("Le nom de l'éducateur doit contenir au moins 2 lettres", 'error');
        return false;
    }

    // Validation des horaires (si renseignés)
    if (horaires && horaires.length < 5) {
        showMessageClasse('Les horaires doivent être plus détaillés (ex: Lundi-Vendredi 8h30-16h30)', 'error');
        return false;
    }

    // Validation des âges (si renseignés)
    if (ageMin !== '') {
        const min = parseInt(ageMin);
        if (isNaN(min) || min < 1 || min > 10) {
            showMessageClasse("L'âge minimum doit être entre 1 et 10 ans", 'error');
            return false;
        }
    }
    if (ageMax !== '') {
        const max = parseInt(ageMax);
        if (isNaN(max) || max < 1 || max > 10) {
            showMessageClasse("L'âge maximum doit être entre 1 et 10 ans", 'error');
            return false;
        }
    }
    if (ageMin !== '' && ageMax !== '') {
        if (parseInt(ageMin) >= parseInt(ageMax)) {
            showMessageClasse("L'âge minimum doit être inférieur à l'âge maximum", 'error');
            return false;
        }
    }

    // Validation de la capacité
    if (capacite !== '') {
        const cap = parseInt(capacite);
        if (isNaN(cap) || cap < 1 || cap > 50) {
            showMessageClasse('La capacité doit être entre 1 et 50', 'error');
            return false;
        }
    }

    return true;
}

// ── Soumission formulaires ────────────────────────────────────────────────────

document.getElementById('inscriptionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!validerFormulaire()) return;

    const champs = [
        'enfant_nom','enfant_prenom','enfant_date_naissance',
        'groupe_sanguin','allergies_alimentaires','allergies_medicales',
        'maladies_chroniques','vaccinations','contact_urgence_nom',
        'contact_urgence_lien','contact_urgence_telephone','aliments_preferes',
        'aliments_interdits','horaire_sieste','notes_sante','notes_speciales'
    ];

    const data = new FormData();
    champs.forEach(id => data.append(id, document.getElementById(id).value.trim()));
    data.append('classe_id', document.getElementById('classe_id').value);
    data.append('autorise_photos',  document.getElementById('autorise_photos').checked  ? 1 : 0);
    data.append('autorise_sorties', document.getElementById('autorise_sorties').checked ? 1 : 0);

    fetch('/TinyTrack/api/inscriptions/save_enfant.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(d => {
            if (d.succes) {
                showMessage(d.message, 'success');
                document.getElementById('inscriptionForm').reset();
                cacherFormulaireEnfant();
            } else {
                showMessage('' + d.message, 'error');
            }
        })
        .catch(() => showMessage('Impossible de contacter le serveur. Ouvrez ce fichier via http://localhost et non directement.', 'error'));
});

document.getElementById('classeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!validerFormulaireClasse()) return;

    const champs = ['nom', 'couleur', 'description', 'age_minimum', 'age_maximum', 'capacite_max', 'educateur_principal', 'salle', 'horaires'];
    const data = new FormData();
    champs.forEach(id => {
        const element = document.getElementById(id === 'nom' ? 'classe_nom' : id === 'couleur' ? 'classe_couleur' : id);
        data.append(id, element.value.trim());
    });

    fetch('/TinyTrack/api/inscriptions/save_classe.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(d => {
            if (d.succes) {
                showMessageClasse(d.message, 'success');
                document.getElementById('classeForm').reset();
                setTimeout(() => afficherClasses(), 1000);
            } else {
                showMessageClasse('' + d.message, 'error');
            }
        })
        .catch(() => showMessageClasse('Impossible de contacter le serveur.', 'error'));
});

// ── Chargement données ────────────────────────────────────────────────────────

function charger() {
    fetch('/TinyTrack/api/inscriptions/get_enfants.php')
        .then(r => r.json())
        .then(d => {
            const tbody = document.getElementById('inscriptionsBody');
            if (d.succes && d.enfants && d.enfants.length > 0) {
                tbody.innerHTML = d.enfants.map(enfant => {
                    const allergies = [
                        enfant.allergies_alimentaires ? enfant.allergies_alimentaires.substring(0, 30) + '…' : null,
                        enfant.allergies_medicales    ? enfant.allergies_medicales.substring(0, 30)    + '…' : null
                    ].filter(Boolean).join(', ');
                    return `<tr>
                        <td><strong>${enfant.enfant_prenom} ${enfant.enfant_nom}</strong></td>
                        <td>${new Date(enfant.enfant_date_naissance).toLocaleDateString('fr-FR')}</td>
                        <td>${enfant.classe_nom || 'Non assigné'}</td>
                        <td>${enfant.groupe_sanguin || '-'}</td>
                        <td>${allergies || '-'}</td>
                        <td>
                            <button class="btn-view"   onclick="afficherDetails(${enfant.id})">Détails</button>
                            <button class="btn-delete" onclick="supprimer(${enfant.id})">Supprimer</button>
                        </td>
                    </tr>`;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="empty-message">Aucune inscription</td></tr>';
            }
        })
        .catch(() => {
            document.getElementById('inscriptionsBody').innerHTML =
                '<tr><td colspan="6" class="empty-message"> Serveur inaccessible — ouvrez via http://localhost</td></tr>';
        });
}

function chargerClasses() {
    fetch('/TinyTrack/api/inscriptions/get_classes.php')
        .then(r => r.json())
        .then(d => {
            const container = document.getElementById('classesContainer');
            if (d.succes && d.classes && d.classes.length > 0) {
                container.innerHTML = d.classes.map(classe => {
                    const colorEmoji = {
                        'Jaune': 'J',
                        'Bleu': 'B',
                        'Vert': 'V',
                        'Rouge': 'R',
                        'Orange': 'O',
                        'Violet': 'Vi',
                        'Rose': 'Ro'
                    }[classe.couleur] || '';
                    
                    return `
                        <div class="classe-card">
                            <div class="classe-header">
                                <div class="classe-title">
                                    <span class="classe-color-badge">${colorEmoji}</span>
                                    <span>${classe.nom}</span>
                                </div>
                            </div>
                            
                            <div class="classe-info">
                                <div class="classe-info-item">
                                    <span class="classe-info-label">Couleur:</span>
                                    <span class="classe-info-value">${classe.couleur}</span>
                                </div>
                                <div class="classe-info-item">
                                    <span class="classe-info-label">Enfants:</span>
                                    <span class="classe-info-value">${classe.nb_enfants || 0}</span>
                                </div>
                                <div class="classe-info-item">
                                    <span class="classe-info-label">Âges:</span>
                                    <span class="classe-info-value">${classe.age_minimum && classe.age_maximum ? `${classe.age_minimum}-${classe.age_maximum} ans` : '-'}</span>
                                </div>
                                <div class="classe-info-item">
                                    <span class="classe-info-label">Capacité:</span>
                                    <span class="classe-info-value">${classe.capacite_max}</span>
                                </div>
                                ${classe.educateur_principal ? `
                                <div class="classe-info-item">
                                    <span class="classe-info-label">Éducateur:</span>
                                    <span class="classe-info-value">${classe.educateur_principal}</span>
                                </div>
                                ` : ''}
                                ${classe.salle ? `
                                <div class="classe-info-item">
                                    <span class="classe-info-label">Salle:</span>
                                    <span class="classe-info-value">${classe.salle}</span>
                                </div>
                                ` : ''}
                            </div>
                            
                            <div class="classe-children">
                                <div class="classe-children-title">Enfants inscrits</div>
                                <div class="children-list" id="children-${classe.id}">
                                    <div class="no-children">Chargement...</div>
                                </div>
                            </div>
                            
                            <div class="classe-actions">
                                <button class="btn-view" onclick="afficherDetailsClasse(${classe.id})">Détails</button>
                                <button class="btn-delete" onclick="supprimerClasse(${classe.id})">Supprimer</button>
                            </div>
                        </div>
                    `;
                }).join('');
                
                // Charger les enfants pour chaque classe
                d.classes.forEach(classe => chargerEnfantsClasse(classe.id));
            } else {
                container.innerHTML = '<div class="empty-message" style="grid-column: 1/-1; padding: 40px;">Aucune classe créée</div>';
            }
        })
        .catch(err => {
            console.error('Erreur:', err);
            document.getElementById('classesContainer').innerHTML = '<div class="empty-message" style="grid-column: 1/-1; padding: 40px;"> Erreur serveur - Vérifiez que la base de données est créée via setup.sql</div>';
        });
}

function chargerEnfantsClasse(classeId) {
    fetch(`/TinyTrack/api/inscriptions/get_details_classe.php?id=${classeId}`)
        .then(r => r.json())
        .then(d => {
            const container = document.getElementById(`children-${classeId}`);
            if (d.succes && d.classe && d.classe.enfants && d.classe.enfants.length > 0) {
                container.innerHTML = d.classe.enfants.map(enfant => {
                    const age = calculerAge(enfant.enfant_date_naissance);
                    return `
                        <div class="child-item" onclick="afficherDetailsEnfantFromClasse(${enfant.id})">
                            <span class="child-name">${enfant.enfant_prenom} ${enfant.enfant_nom}</span>
                            <span class="child-age">${age} ans</span>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = '<div class="no-children">Aucun enfant dans cette classe</div>';
            }
        })
        .catch(() => {
            document.getElementById(`children-${classeId}`).innerHTML = '<div class="no-children">Erreur chargement</div>';
        });
}

function chargerClassesPourSelect() {
    fetch('/TinyTrack/api/inscriptions/get_classes.php')
        .then(r => r.json())
        .then(d => {
            const select = document.getElementById('classe_id');
            select.innerHTML = '<option value="">-- Sélectionner --</option>';
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

// ── Suppression ───────────────────────────────────────────────────────────────

function supprimer(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet enfant ?')) return;
    const data = new FormData();
    data.append('id', id);
    fetch('/TinyTrack/api/inscriptions/delete_enfant.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(d => {
            if (d.succes) {
                showMessage(d.message, 'success');
                afficherFormulaire();
                charger();
            } else {
                showMessage('' + d.message, 'error');
            }
        })
        .catch(() => showMessage('Impossible de contacter le serveur.', 'error'));
}

function supprimerClasse(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette classe ?')) return;
    const data = new FormData();
    data.append('id', id);
    fetch('/TinyTrack/api/inscriptions/delete_classe.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(d => {
            if (d.succes) {
                showMessageClasse(d.message, 'success');
                chargerClasses();
            } else {
                showMessageClasse('' + d.message, 'error');
            }
        })
        .catch(() => showMessageClasse('Impossible de contacter le serveur.', 'error'));
}

// ── Init ──────────────────────────────────────────────────────────────────────
charger();
chargerClassesPourSelect();

// Vérification en temps réel âge/classe
document.getElementById('enfant_date_naissance').addEventListener('change', verifierCompatibiliteAgeClasse);
document.getElementById('classe_id').addEventListener('change', verifierCompatibiliteAgeClasse);