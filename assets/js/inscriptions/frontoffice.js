// ── Helpers ──────────────────────────────────────────────────────────────────

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

// ── Detail renderer (lecture seule) ──────────────────────────────────────────

function renderDetails(enfant) {
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
    `;
}

// ── State ─────────────────────────────────────────────────────────────────────

let tousLesEnfants = [];

// ── Navigation ────────────────────────────────────────────────────────────────

function afficherListe() {
    document.getElementById('listView').classList.add('active');
    document.getElementById('detailsView').classList.remove('active');
}

function afficherDetails(id) {
    const enfant = tousLesEnfants.find(e => e.id == id);
    if (!enfant) return;
    document.getElementById('detailsContent').innerHTML = renderDetails(enfant);
    document.getElementById('listView').classList.remove('active');
    document.getElementById('detailsView').classList.add('active');
}

// ── Table ─────────────────────────────────────────────────────────────────────

function afficherTable(enfants) {
    const tbody = document.getElementById('enfantsBody');
    if (!enfants.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-message">Aucun enfant inscrit</td></tr>';
        return;
    }
    tbody.innerHTML = enfants.map(enfant => {
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
            <td><button class="btn-view" onclick="afficherDetails(${enfant.id})">Voir</button></td>
        </tr>`;
    }).join('');
}

// ── Recherche ─────────────────────────────────────────────────────────────────

function filtrerEnfants() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    afficherTable(q
        ? tousLesEnfants.filter(e =>
            e.enfant_nom.toLowerCase().includes(q) ||
            e.enfant_prenom.toLowerCase().includes(q))
        : tousLesEnfants
    );
}

// ── Chargement ────────────────────────────────────────────────────────────────

function charger() {
    fetch('/TinyTrack/api/inscriptions/get_enfants.php')
        .then(r => r.json())
        .then(d => {
            tousLesEnfants = (d.succes && d.enfants) ? d.enfants : [];
            afficherTable(tousLesEnfants);
        })
        .catch(() => {
            document.getElementById('enfantsBody').innerHTML =
                '<tr><td colspan="6" class="empty-message"> Serveur inaccessible — ouvrez via http://localhost</td></tr>';
        });
}

// ── Init ──────────────────────────────────────────────────────────────────────
charger();
