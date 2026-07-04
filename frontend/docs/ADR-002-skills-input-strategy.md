# ADR-002 — Stratégie de saisie des compétences (Skills) dans les offres d'emploi

| Champ | Valeur |
|---|---|
| **Statut** | ✅ Accepted |
| **Date** | 2026-07-04 |
| **Décideurs** | Équipe frontend, Tech Lead, Product Owner |
| **Contexte produit** | ATS Pro — champ `skills` du formulaire de création d'offre d'emploi |
| **Lié à** | ADR-001 (éditeur Tiptap), ADR-003 (catégories) |

---

## 1. Contexte

Dans notre ATS, les recruteurs saisissent les compétences requises pour chaque offre d'emploi. Ce champ alimente :

- La **recherche et le matching** entre offres et candidats
- Les **filtres** de la liste d'offres
- Les **analytics** internes (compétences les plus demandées, taux de couverture)
- À terme, un moteur de **recommandation de candidats**

La qualité de ce champ est donc critique. Une donnée libre et non structurée (`"js"`, `"JavaScript"`, `"JS / React"`, `"javascrpt"`) rend le matching impossible et les analytics inutilisables.

Trois stratégies ont été évaluées : input libre avec sanitization, vocabulaire contrôlé (sélection), et approche hybride.

---

## 2. Critères de décision

| Critère | Poids | Justification |
|---|---|---|
| **Qualité de la donnée** | Éliminatoire | Sans donnée normalisée, matching et analytics sont impossibles |
| **Sécurité (XSS/injection)** | Éliminatoire | Les skills sont stockés et affichés à d'autres utilisateurs |
| **Ergonomie recruteur** | Très élevé | Le recruteur ne doit pas être bloqué par une liste trop restrictive |
| **Couverture des compétences émergentes** | Élevé | Les taxonomies publiques ont un décalage de 12–18 mois |
| **Maintenabilité** | Élevé | Pas de base skills propriétaire à maintenir manuellement |
| **Performance matching** | Élevé | Queries sur des valeurs normalisées vs texte libre |
| **Coût d'intégration** | Moyen | APIs publiques gratuites vs build interne |

---

## 3. Options évaluées

### Option A — Input libre avec sanitization

**Description** : Un champ texte libre où le recruteur saisit n'importe quelle compétence. Les valeurs sont sanitizées côté client (DOMPurify) et côté backend (strip HTML, trim, longueur max) avant stockage.

**Analyse sécurité**

L'OWASP Input Validation Cheat Sheet (2025) est explicite sur ce point : pour un champ à valeurs d'un ensemble discret connu, l'allowlisting (validation par liste blanche) est la méthode la plus robuste. L'input libre est considéré comme le contexte le plus difficile à valider, et la sanitization seule ne suffit pas comme défense primaire contre XSS — elle doit être couplée à un encodage contextuel en sortie.

Concrètement, stocker `"<script>alert(1)</script>"` comme skill, même sanitizé, crée une surface d'attaque si la sanitization est mal configurée ou si une régression est introduite. Les compétences sont affichées à d'autres utilisateurs (candidats, managers) — c'est un vecteur de **stored XSS**.

**Analyse qualité donnée**

`"JavaScript"`, `"JS"`, `"js"`, `"javascrpt"`, `"JavaScript (ES6+)"` seraient cinq entrées distinctes en base. Le matching devient impossible sans une couche de normalisation post-stockage (NLP, fuzzy matching), qui représente une complexité additionnelle significative.

**Verdict** : ❌ Rejeté. Risque XSS stored non négligeable, qualité donnée ingérable, matching impossible sans infrastructure supplémentaire.

---

### Option B — Vocabulaire contrôlé strict (sélection uniquement)

**Description** : Le recruteur choisit uniquement parmi des compétences proposées par une API externe (ESCO, ROME 4.0, ou autre). Aucune entrée libre n'est possible.

**Analyse qualité**

Excellente pour le matching et les analytics. Toutes les valeurs sont normalisées, indexables, et comparables.

**Analyse couverture**

Les taxonomies publiques majeure ont un décalage structurel :

- **ESCO** (Commission Européenne) : référentiel multilingue de 13 890 compétences, mis à jour périodiquement. Fiable pour les compétences établies.
- **ROME 4.0** (France Travail) : 1 584 fiches métiers avec compétences associées, mis à jour deux fois par an. Idéal pour le marché français.
- **O\*NET** (US DoL) : exhaustif sur le marché américain, moins pertinent pour un ATS français.

Le problème documenté est un **décalage de 12 à 18 mois** entre l'émergence d'une compétence sur le marché et son intégration dans une taxonomie officielle. Des compétences comme `"Prompt Engineering"`, `"LangChain"`, ou `"Supabase"` peuvent être absentes pendant des mois.

**Verdict** : ⚠️ Rejeté seul. Couverture insuffisante pour les compétences émergentes, frustrant pour les recruteurs tech.

---

### Option C — Approche hybride : autocomplete sur taxonomie + création libre contrôlée ✅

**Description** : Un composant `ComboBox` ou `TagInput` qui :

1. Propose en **autocomplete** les compétences issues d'une taxonomie standardisée (ESCO/ROME 4.0)
2. Permet la **création d'une valeur libre** si elle n'existe pas dans la taxonomie, avec un flag `is_custom: true`
3. Applique une **validation stricte** sur les valeurs libres (longueur max, caractères autorisés via allowlist, pas de HTML)
4. Les valeurs libres les plus utilisées alimentent une **revue périodique** pour enrichir le référentiel interne

**Architecture de la donnée**

```json
{
  "skills": [
    {
      "id": "esco:http://data.europa.eu/esco/skill/S1.2.1",
      "label": "JavaScript",
      "source": "esco",
      "is_custom": false
    },
    {
      "id": "custom:uuid-xxxx",
      "label": "LangChain",
      "source": "custom",
      "is_custom": true
    }
  ]
}
```

**Sécurité sur les valeurs libres**

Les valeurs libres sont traitées comme du texte brut — jamais comme du HTML. La validation applique une **allowlist stricte** côté serveur :

- Caractères autorisés : `[a-zA-Z0-9\s\.\-\+\#\/]` + Unicode letters (accents, CJK pour internationalisation)
- Longueur maximale : 80 caractères
- Pas de balises HTML — la valeur est stockée comme `text/plain`, jamais rendue via `dangerouslySetInnerHTML`
- Validation identique côté client (UX) et côté serveur (sécurité)

Cette approche élimine le risque XSS stored sans recourir à DOMPurify, car **le champ n'accepte pas de HTML**. C'est l'allowlisting recommandé par OWASP pour les champs à sémantique définie.

**Verdict** : ✅ Retenu. Meilleur équilibre entre qualité de donnée, couverture, ergonomie et sécurité.

---

## 4. Tableau comparatif

| Critère | Input libre | Contrôlé strict | **Hybride** |
|---|---|---|---|
| Qualité / normalisation | ❌ | ✅ | ✅ |
| Sécurité XSS | ⚠️ | ✅ | ✅ |
| Couverture émergents | ✅ | ❌ | ✅ |
| Ergonomie recruteur | ✅ | ⚠️ | ✅ |
| Matching / analytics | ❌ | ✅ | ✅ |
| Maintenabilité | ✅ | ⚠️ | ✅ |
| Complexité implem | ✅ | ⚠️ | ⚠️ |

---

## 5. Décision

**➡️ Approche hybride — autocomplete ESCO/ROME 4.0 + création libre contrôlée (texte brut uniquement).**

### Taxonomie principale retenue : ESCO

<cite index="16-1">ESCO est spécifiquement conçu pour être utilisé par des organisations tierces, avec une API locale disponible en plus de l'API web, permettant un hébergement local sans dépendance aux performances d'un tiers lors de la montée en charge.</cite> Sa couverture multilingue et son adoption européenne en font le choix naturel pour un ATS opérant sur le marché français et européen.

### Taxonomie complémentaire : ROME 4.0

<cite index="23-1">Le ROME 4.0, mis à jour au moins deux fois par an grâce à une plateforme collaborative, comprend depuis juin 2025 1 584 fiches métiers avec compétences associées (savoir-faire, savoir-être, savoirs).</cite> Son API REST est disponible sur `francetravail.io` avec authentification OAuth2, réponse JSON, et documentation Swagger.

---

## 6. Conséquences

### Positives
- Données normalisées dès la saisie → matching et analytics fiables immédiatement
- Surface XSS nulle sur le champ skills (texte brut, jamais HTML)
- Couverture complète : compétences établies via ESCO/ROME + émergentes via custom
- Chemin d'enrichissement du référentiel interne via les custom skills populaires

### Négatives / risques à mitiger

| Risque | Mitigation |
|---|---|
| Latence API ESCO/ROME | Cache Redis local des résultats d'autocomplete (TTL 24h) |
| Drift des custom skills | Revue mensuelle des skills custom, proposition d'ajout au référentiel |
| Disponibilité API externe | Fallback sur le référentiel local caché si API indisponible |
| Caractères Unicode inattendus | Normalisation NFC + trim + regex allowlist côté serveur |

---

## 7. Stack d'implémentation

```
Frontend
  └── ComboBox / TagInput (Tiptap-compatible ou composant dédié)
        ├── Autocomplete → GET /api/skills/search?q=... (proxy interne)
        └── Création libre → validation regex client + flag is_custom: true

Backend (Symfony)
  ├── Proxy ESCO API  → cache Redis TTL 24h
  ├── Proxy ROME 4.0  → cache Redis TTL 24h
  ├── Validation allowlist serveur (Assert\Regex, Assert\Length)
  └── Stockage JSON dans colonne `skills` (type json, table job_offer)
```

---

## 8. Références

- [OWASP — Input Validation Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Input_Validation_Cheat_Sheet.html)
- [ESCO API — European Commission](https://ec.europa.eu/esco/portal/api)
- [ROME 4.0 API — francetravail.io](https://francetravail.io/data/api/rome-4-0-competences)
- [JobsPikr — Open Skills and Talent Graphs 2025](https://www.jobspikr.com/blog/open-skills-and-talent-graphs-2025/)
- [365Talents — Skills Taxonomy Guide 2026](https://365talents.com/en/resources/your-comprehensive-guide-to-skills-taxonomy/)
- [Tabiya — Why ESCO](https://docs.tabiya.org/our-tech-stack/inclusive-livelihoods-taxonomy/why-esco)

---

*Document généré le 2026-07-04 — à réviser si un fournisseur de taxonomie change ses conditions d'accès ou si le besoin de matching évolue vers un modèle vectoriel.*
