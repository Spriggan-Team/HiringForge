# ADR-001 — Choix de l'éditeur de texte riche pour la création d'offres d'emploi

| Champ | Valeur |
|---|---|
| **Statut** | ✅ Accepted |
| **Date** | 2026-07-02 |
| **Décideurs** | Équipe frontend, Tech Lead |
| **Contexte produit** | ATS Pro — module de création d'offres d'emploi |
| **Remplace** | Quill / react-quill (solution initiale écartée) |

---

## 1. Contexte

Dans notre ATS, les recruteurs créent et publient des offres d'emploi avec du contenu riche : titres, listes à puces, mise en gras, paragraphes structurés. Ce contenu est sérialisé puis stocké en base de données (colonne `content` de type `json` dans la table `job_offer`), et rendu côté candidat sous forme HTML.

La solution initiale envisagée était **Quill** via `react-quill`. Plusieurs signaux d'alerte ont motivé cette réévaluation :

- `react-quill` n'a pas reçu de mise à jour depuis **3 ans** (dernière version : 2.0.0, publiée en 2022) et est considéré comme un projet potentiellement abandonné par Snyk.
- `react-quill` est toujours couplé à Quill **1.x** dans sa version stable publiée — il ne supporte pas encore Quill 2.0 (sorti en avril 2024), malgré une demande ouverte depuis mai 2024 sur le dépôt GitHub.
- Le format de données natif de Quill est un **Delta** propriétaire, qui nécessite une couche de conversion pour être stocké ou rendu en HTML standard.
- Des alternatives fork comme `react-quill-new` existent mais restent communautaires et non officielles.

Cette ADR documente l'analyse comparative et la décision retenue.

---

## 2. Critères de décision

Les critères ont été pondérés selon les besoins spécifiques d'un ATS professionnel :

| Critère | Poids | Justification |
|---|---|---|
| **Maintenance active** | Éliminatoire | Outil critique, on ne peut pas s'appuyer sur un projet abandonné |
| **Intégration React / TypeScript** | Très élevé | Stack frontend React + TypeScript |
| **Format de sortie HTML** | Très élevé | Le backend stocke du HTML dans `content json`, rendu côté candidat |
| **Headless / design system** | Élevé | L'éditeur doit s'intégrer visuellement dans l'ATS (pas de UI imposée) |
| **Extensibilité** | Élevé | Besoins futurs : placeholders RH, champs structurés, templates |
| **Sécurité XSS** | Élevé | Le HTML généré est rendu tel quel — sanitization obligatoire |
| **Coût / licence** | Moyen | Open source préféré, budget limité pour des cloud features |
| **Courbe d'apprentissage** | Moyen | L'équipe est en montée en compétences |

---

## 3. Options évaluées

### Option A — Quill 2.0 + react-quill-new

**Description** : Utiliser Quill 2.0 (sorti avril 2024, réécrit en TypeScript) via le fork communautaire `react-quill-new`.

| Dimension | Évaluation |
|---|---|
| Maintenance core (Quill 2.x) | ✅ Maintenu par Slab depuis 2024 |
| Wrapper React officiel | ❌ Inexistant — `react-quill` bloqué sur Quill 1.x, forks non officiels |
| TypeScript | ✅ Quill 2.0 natif TS, `react-quill-new` partiellement |
| Sortie HTML | ⚠️ Natif Delta — conversion en HTML possible mais non triviale |
| Headless | ❌ UI intégrée, difficile à personnaliser |
| Extensibilité | ⚠️ Système de Blots, moins flexible que ProseMirror |
| Écosystème React | ❌ Fragmenté — forks multiples, pas de maintainer officiel |
| Licence | ✅ BSD |

**Verdict** : Le core Quill 2.0 est techniquement solide, mais l'absence de wrapper React officiel et maintenu est rédhibitoire. S'appuyer sur un fork communautaire (`react-quill-new`, 77 usagers sur npm) pour un outil critique n'est pas acceptable dans un contexte professionnel.

---

### Option B — Lexical (Meta)

**Description** : Framework d'édition React-first open-source développé et maintenu par Meta.

| Dimension | Évaluation |
|---|---|
| Maintenance | ✅ Activement maintenu par Meta |
| TypeScript | ✅ Natif |
| Sortie HTML | ✅ Via `@lexical/html` |
| Headless | ✅ Totalement headless |
| Extensibilité | ✅ Système de plugins bas niveau |
| Courbe d'apprentissage | ❌ Élevée — API de bas niveau, beaucoup de plumbing à écrire |
| Adapté ATS | ⚠️ Taillé pour les surfaces haute-performance (chat, social) — overkill pour une fiche de poste |
| Licence | ✅ MIT |

**Verdict** : Techniquement excellent, mais optimisé pour des cas de performance extrême (Meta, Workplace, Facebook). Pour un éditeur de description de poste dans un ATS, la complexité de Lexical dépasse largement le besoin. Le ratio effort/valeur est défavorable.

---

### Option C — Tiptap (ProseMirror)

**Description** : Framework d'édition headless basé sur ProseMirror, avec une API React de première classe, un écosystème de +100 extensions, et une licence MIT.

| Dimension | Évaluation |
|---|---|
| Maintenance | ✅ Activement maintenu, entreprise derrière (Tiptap GmbH) |
| TypeScript | ✅ Natif |
| Sortie HTML | ✅ `editor.getHTML()` — natif, sans conversion |
| Stockage JSON | ✅ `editor.getJSON()` — alternative structurée si besoin |
| Headless | ✅ Zéro UI imposée — intégration design system totale |
| Extensibilité | ✅ Extensions composables, surchargeables sans fork |
| React | ✅ Package `@tiptap/react` officiel et maintenu |
| Courbe d'apprentissage | ✅ Accessible — API haut niveau sur ProseMirror |
| Collaboration (futur) | ✅ Via Hocuspocus (open source) ou Tiptap Cloud |
| Licence | ✅ MIT (core) — Pro extensions payantes optionnelles |
| XSS | ⚠️ HTML sanitization à ajouter côté backend (DOMPurify / côté serveur) |

**Verdict** : Meilleur équilibre entre puissance, maintenabilité, et adéquation au besoin. Utilisé en production par des éditeurs de niveau New York Times, Guardian, Atlassian (via ProseMirror). Le core reste gratuit et MIT.

---

### Option D — TinyMCE / CKEditor 5

**Description** : Éditeurs WYSIWYG enterprise avec UI complète.

| Dimension | Évaluation |
|---|---|
| Maintenance | ✅ Maintenu |
| Sortie HTML | ✅ Natif |
| Headless | ❌ UI imposée, surcharges CSS complexes |
| Licence | ⚠️ Gratuit limité — plans payants à partir de ~75$/mois pour usage production |
| Taille bundle | ❌ Lourds (TinyMCE > 300 KB gzip) |
| Intégration design system | ❌ Difficile sans hacks |

**Verdict** : Écarté. L'UI imposée et le coût des licences enterprise sont incompatibles avec nos contraintes.

---

## 4. Tableau comparatif synthétique

| Critère | Quill+fork | Lexical | **Tiptap** | TinyMCE |
|---|---|---|---|---|
| Maintenance React wrapper | ❌ | ✅ | ✅ | ✅ |
| TypeScript natif | ⚠️ | ✅ | ✅ | ⚠️ |
| Sortie HTML directe | ⚠️ | ✅ | ✅ | ✅ |
| Headless / design system | ❌ | ✅ | ✅ | ❌ |
| Extensibilité | ⚠️ | ✅ | ✅ | ⚠️ |
| Courbe d'apprentissage | ✅ | ❌ | ✅ | ✅ |
| Coût | ✅ | ✅ | ✅ | ❌ |
| Adapté ATS (offres d'emploi) | ❌ | ⚠️ | ✅ | ⚠️ |
| Collaboration future | ⚠️ | ⚠️ | ✅ | 💰 |

---

## 5. Décision

**➡️ Tiptap (`@tiptap/react`) est retenu comme éditeur de contenu pour la création d'offres d'emploi.**

### Justification

1. **Maintenu et stable** : Tiptap GmbH maintient activement le projet avec des releases régulières. Le core ProseMirror est battle-tested (10+ ans) et alimente des éditeurs en production à l'échelle mondiale.

2. **Sortie HTML native** : `editor.getHTML()` produit du HTML standard, directement stockable dans notre colonne `content json` et rendu sans transformation côté candidat. Pas de format propriétaire à convertir.

3. **Headless** : Aucune UI imposée. La toolbar, les menus et les boutons sont entièrement sous notre contrôle — le design de l'ATS reste cohérent.

4. **React + TypeScript** : `@tiptap/react` est le package officiel, maintenu par la même équipe. L'API avec `useEditor()` et `<EditorContent />` est idiomatique dans notre stack.

5. **Extensibilité adaptée à l'ATS** : Les extensions Tiptap permettront d'ajouter progressivement des fonctionnalités spécifiques (templates d'offres, placeholders RH, variables dynamiques) sans refonte.

6. **Chemin vers la collaboration** : Si un besoin de co-édition d'offres émerge, Hocuspocus (open source) est disponible sans changer d'éditeur.

7. **Coût** : Le core MIT est suffisant pour notre cas d'usage. Les Pro extensions (collaboration cloud) sont optionnelles et activables à la demande.

---

## 6. Conséquences

### Positives
- Sortie HTML propre, directement stockable et rendu sans transformation.
- Intégration dans le design system de l'ATS sans surcharge CSS.
- Migration future vers du JSON structuré possible sans changer d'éditeur (`editor.getJSON()`).
- Extensibilité pour des fonctionnalités RH spécifiques (variables, templates).

### Négatives / risques à mitiger

| Risque | Mitigation |
|---|---|
| **XSS** : le HTML généré doit être sanitizé avant stockage et rendu | Ajouter `DOMPurify` côté client + sanitization côté backend (Symfony : `HtmlSanitizer`) |
| **Surpoids** si toutes les extensions sont importées | Importer uniquement les extensions nécessaires (`StarterKit` + sélection) |
| **Dépendance ProseMirror** pour customisations avancées | Documenter les extensions custom, former l'équipe aux bases ProseMirror |
| **Pro extensions payantes** si collaboration future | Budget à anticiper — Hocuspocus en self-hosted reste une alternative gratuite |

---

## 7. Stack d'implémentation retenue

```
@tiptap/react          → package React officiel
@tiptap/starter-kit    → extensions de base (Bold, Italic, Heading, Lists, …)
@tiptap/extension-*    → extensions à la carte selon le besoin
DOMPurify              → sanitization XSS côté client avant envoi API
```

**Format de stockage backend** : HTML string dans `content json` (colonne existante).

**Rendu côté candidat** : `dangerouslySetInnerHTML` avec le HTML sanitizé, ou composant `<JobOfferRenderer>` dédié.

---

## 8. Références

- [Tiptap — documentation officielle](https://tiptap.dev/docs)
- [Tiptap — Export JSON & HTML](https://tiptap.dev/docs/guides/output-json-html)
- [Liveblocks — Which rich text editor in 2025?](https://liveblocks.io/blog/which-rich-text-editor-framework-should-you-choose-in-2025)
- [PkgPulse — Tiptap vs Quill vs Lexical vs Slate 2026](https://www.pkgpulse.com/guides/tiptap-vs-lexical-vs-slate-vs-quill-rich-text-editor-2026)
- [Snyk — react-quill maintenance status](https://security.snyk.io/package/npm/react-quill)
- [GitHub — react-quill issue #979 : Quill 2.0 support](https://github.com/zenoamaro/react-quill/issues/979)
- [Nutrient — Headless vs WYSIWYG editors 2025](https://www.nutrient.io/blog/headless-vs-wysiwyg/)

---

*Document généré le 2026-07-02 — à réviser si le contexte technique ou produit évolue significativement.*
