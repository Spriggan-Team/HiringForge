# ADR-003 — Stratégie de saisie des catégories métier dans les offres d'emploi

| Champ | Valeur |
|---|---|
| **Statut** | ✅ Accepted |
| **Date** | 2026-07-04 |
| **Décideurs** | Équipe frontend, Tech Lead, Product Owner |
| **Contexte produit** | ATS Pro — champ `categories` du formulaire de création d'offre d'emploi |
| **Lié à** | ADR-001 (éditeur Tiptap), ADR-002 (skills), table `category` et `job_category` |

---

## 1. Contexte

La table `category` existe déjà dans notre schéma (`id`, `name`) et est liée aux offres via `job_category`. Le champ catégorie sert à :

- **Organiser et filtrer** les offres dans l'interface ATS
- **Orienter les candidats** vers les offres de leur domaine
- **Produire des analytics** : répartition des recrutements par domaine, tendances sectorielles
- **Alimenter les alertes** : un candidat abonné à "Développement Web" reçoit les nouvelles offres de cette catégorie

Contrairement aux skills (qui peuvent être des centaines et très spécifiques), les catégories métier sont un ensemble **limité et stable** — typiquement 10 à 50 entrées pour un ATS professionnel couvrant plusieurs secteurs.

La question est : doit-on laisser les recruteurs créer des catégories librement, ou les contraindre à choisir parmi une liste maîtrisée ?

---

## 2. Différence fondamentale avec les skills

| Dimension | Skills (ADR-002) | Catégories (ADR-003) |
|---|---|---|
| Volume | Des centaines | 10 à 50 |
| Volatilité | Élevée (émergents tous les mois) | Faible (stable sur des années) |
| Granularité | Fine (`TypeScript`, `Kubernetes`) | Large (`Développement Web`, `Finance`) |
| Utilisateur | Recruteur tech | Tous recruteurs |
| Impact si fragmenté | Matching cassé | Navigation et filtres cassés |

Les catégories sont **structurellement différentes** des skills. Leur valeur réside précisément dans leur **nombre limité et leur stabilité** — une catégorie n'a de sens que si toutes les offres du même domaine la partagent exactement.

---

## 3. Options évaluées

### Option A — Input libre avec sanitization

**Description** : Le recruteur saisit librement le nom de la catégorie. Les valeurs sont sanitizées avant stockage.

**Analyse qualité**

C'est le pire scénario pour les catégories. Avec une équipe de 10 recruteurs, on obtient rapidement :
`"Dev Web"`, `"Développement Web"`, `"développement web"`, `"Dev Frontend"`, `"Web Dev"` — cinq catégories distinctes en base pour ce qui est conceptuellement une seule.

Les filtres, les alertes candidats, et les analytics deviennent immédiatement incohérents. La fragmentation est irrémédiable sans une migration de données lourde.

**Analyse sécurité**

M�me problème que pour les skills : les catégories sont affichées à des candidats. Un input libre mal contrôlé est un vecteur de stored XSS. <cite index="34-1">L'OWASP Input Validation Cheat Sheet est explicite : si le champ provient d'un ensemble discret d'options (liste déroulante, boutons radio), l'input doit correspondre exactement à l'une des valeurs proposées. Tout échec à valider côté serveur est un événement de sécurité à haute sévérité, car il indique qu'un attaquant manipule le code côté client.</cite>

**Verdict** : ❌ Rejeté. Fragmentation des données garantie, risque sécurité, aucun avantage compensatoire.

---

### Option B — Vocabulaire contrôlé strict depuis une API externe

**Description** : Les catégories sont importées depuis une taxonomie externe (grand domaines ROME 4.0, nomenclature NAF/INSEE, ISCO-08) et le recruteur ne peut choisir que parmi cette liste.

**Analyse**

<cite index="23-1">Le ROME 4.0 structure ses 1 584 fiches métiers par domaines d'activité organisés en arborescence.</cite> Cette arborescence de haut niveau (une vingtaine de grands domaines) pourrait servir de référentiel de catégories.

Le problème est que les nomenclatures publiques sont conçues pour la classification statistique et administrative, pas pour la navigation dans un ATS. Les libellés sont souvent bureaucratiques (`"M — Activités spécialisées, scientifiques et techniques"`) et inadaptés à une interface produit.

De plus, contraindre les catégories à une taxonomie externe signifie perdre la capacité à personnaliser selon le positionnement de l'ATS (un ATS spécialisé IT aura besoin de sous-catégories que le ROME ne distingue pas).

**Verdict** : ⚠️ Partiellement utile comme inspiration, mais pas comme contrainte exclusive. Les libellés et la granularité ne conviennent pas directement.

---

### Option C — Liste maîtrisée interne, gérée par l'administrateur ✅

**Description** : Les catégories sont définies et maintenues par un **administrateur de l'ATS** (ou un super-admin). Les recruteurs choisissent uniquement parmi cette liste via une interface de sélection (single ou multi-select avec recherche).

L'administrateur peut créer, renommer, fusionner ou archiver des catégories via une interface dédiée. La liste est initialisée avec un seed cohérent (inspiré du ROME, personnalisable) et évolue de façon contrôlée.

**Sécurité**

La validation est triviale : la valeur soumise doit correspondre à un `id` existant dans la table `category`. <cite index="34-1">Tout échec de cette validation côté serveur est immédiatement détecté et loggé comme tentative de manipulation.</cite> Aucun HTML n'est accepté, aucune sanitization complexe n'est nécessaire — la donnée est un entier (foreign key).

**Qualité donnée**

Garantie par construction. Toutes les offres d'un même domaine partagent exactement le même `category_id`. Les filtres, alertes et analytics fonctionnent immédiatement sans post-traitement.

**Ergonomie**

Un composant de sélection avec recherche (type `<Select searchable>` ou drawer multi-select) permet de naviguer rapidement dans 10–50 catégories. L'expérience est fluide même avec une liste contrôlée.

**Évolutivité**

La liste peut évoluer sans modifier le code — seulement des opérations CRUD en base via l'interface admin. Les catégories obsolètes peuvent être archivées (soft delete) sans casser les offres existantes.

**Verdict** : ✅ Retenu. Seule approche garantissant la cohérence des données sans complexité excessive.

---

### Option D — Hybride : liste maîtrisée + suggestion libre pour les admins

**Description** : Les recruteurs choisissent parmi la liste. Les recruteurs peuvent **suggérer** une nouvelle catégorie, qui entre dans une file d'approbation admin avant d'être ajoutée officiellement.

**Analyse**

Cette option ajoute une bonne gouvernance pour un ATS multi-tenant ou avec de nombreux secteurs différents. Elle est plus complexe à implémenter (workflow d'approbation, notifications admin) mais offre un mécanisme d'évolution maîtrisé.

Pour notre contexte actuel (ATS à portée limitée, équipe de recruteurs cohérente), ce workflow est prématuré. Il peut être ajouté en Release 2 si le besoin émerge.

**Verdict** : ⚠️ Viable à terme, prématuré maintenant. Reporté en backlog.

---

## 4. Tableau comparatif

| Critère | Input libre | API externe | **Liste maîtrisée** | Hybride |
|---|---|---|---|---|
| Cohérence données | ❌ | ✅ | ✅ | ✅ |
| Sécurité | ⚠️ | ✅ | ✅ | ✅ |
| Ergonomie recruteur | ✅ | ⚠️ | ✅ | ✅ |
| Personnalisation ATS | ✅ | ❌ | ✅ | ✅ |
| Complexité implem | ✅ | ⚠️ | ✅ | ❌ |
| Analytics fiables | ❌ | ✅ | ✅ | ✅ |
| Gouvernance | ❌ | ❌ | ✅ | ✅ |

---

## 5. Décision

**➡️ Liste maîtrisée interne, gérée par l'administrateur. Sélection via composant searchable multi-select. Aucun input libre côté recruteur.**

---

## 6. Seed initial des catégories

Liste de départ inspirée du ROME 4.0 et adaptée à un ATS généraliste :

| ID | Catégorie |
|---|---|
| 1 | Développement Web & Mobile |
| 2 | Data & Intelligence Artificielle |
| 3 | DevOps, Cloud & Infrastructure |
| 4 | Cybersécurité |
| 5 | Design UX/UI |
| 6 | Marketing Digital & Communication |
| 7 | Finance & Comptabilité |
| 8 | Ressources Humaines |
| 9 | Commercial & Vente |
| 10 | Logistique & Supply Chain |
| 11 | Juridique & Conformité |
| 12 | Management & Direction |

Cette liste est **configurable par l'administrateur** dès le premier lancement — elle n'est pas hardcodée.

---

## 7. Conséquences

### Positives
- Cohérence garantie par conception — aucune fragmentation possible
- Validation sécurité triviale (FK integer) — surface d'attaque nulle
- Filtres, alertes et analytics opérationnels immédiatement
- Évolution maîtrisée via interface admin sans déploiement

### Négatives / risques à mitiger

| Risque | Mitigation |
|---|---|
| Liste trop restrictive pour certains secteurs | Seed initial généraliste + interface admin de personnalisation |
| Recruteur frustré de ne pas trouver sa catégorie | Bouton "Suggérer une catégorie" → email admin (simple, sans workflow complet) |
| Catégorie devenue obsolète (ex: fusion de métiers) | Soft delete + migration des offres orphelines vers la catégorie parente |
| Performance sur grande liste | La liste est chargée en cache au démarrage — aucune requête par saisie |

---

## 8. Stack d'implémentation

```
Frontend
  └── Select (searchable, multi) ou Drawer filtrable
        ├── Données : GET /api/categories (liste complète, mise en cache)
        └── Validation : id doit être dans la liste locale avant soumission

Backend (Symfony)
  ├── Endpoint GET /api/categories → retourne liste triée alphabétiquement
  ├── Validation : Assert\Choice sur les ids valides (liste depuis BDD)
  ├── Endpoint admin CRUD /admin/categories → créer / renommer / archiver
  └── Soft delete : colonne `archived_at` sur la table `category`

Base de données
  ├── Table `category` : id, name, archived_at (nullable)
  └── Table `job_category` : relation many-to-many existante
```

---

## 9. Comparaison synthétique des deux ADRs (skills vs catégories)

| Dimension | Skills (ADR-002) | Catégories (ADR-003) |
|---|---|---|
| **Stratégie retenue** | Hybride : autocomplete taxonomie + custom libre | Liste maîtrisée admin |
| **Raison principale** | Compétences émergentes non couvertes par les taxonomies | Nombre limité, stabilité requise, cohérence analytics |
| **Source de vérité** | ESCO + ROME 4.0 + custom interne | Référentiel interne ATS |
| **Validation sécurité** | Allowlist regex (texte brut, pas HTML) | FK integer (trivial) |
| **Évolution** | Continue, via custom skills + revue mensuelle | Contrôlée, via interface admin |

---

## 10. Références

- [OWASP — Input Validation Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Input_Validation_Cheat_Sheet.html)
- [ROME 4.0 — France Travail opendata](https://www.francetravail.org/opendata/repertoire-operationnel-des-meti.html)
- [ROME 4.0 API — data.gouv.fr](https://www.data.gouv.fr/dataservices/api-repertoire-operationnel-des-metiers-et-des-emplois-rome-4-0)
- [365Talents — Skills Taxonomy Guide 2026](https://365talents.com/en/resources/your-comprehensive-guide-to-skills-taxonomy/)
- [OWASP — XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)

---

*Document généré le 2026-07-04 — à réviser si l'ATS évolue vers un modèle multi-tenant avec des secteurs très hétérogènes nécessitant un workflow d'approbation de catégories.*
