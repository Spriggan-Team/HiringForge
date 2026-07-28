# ADR-004 — Modèle d'embedding pour le matching de compétences (Skill Matching)

| Champ | Valeur |
|---|---|
| **Statut** | ✅ Accepted |
| **Date** | 2026-07-28 |
| **Décideurs** | Équipe IA, Tech Lead, Product Owner |
| **Contexte produit** | ATS Pro — moteur de matching compétences candidat ↔ offre d'emploi |
| **Lié à** | ADR-002 (saisie skills), ADR-005 (base de données vectorielle) |

---

## 1. Contexte

Le matching de compétences est le cœur différenciateur de l'ATS. L'objectif est de mesurer la **similarité sémantique** entre :

- Les compétences d'un **profil candidat** (`skills[]` en JSON)
- Les compétences requises d'une **offre d'emploi** (`skills[]` en JSON)

La correspondance exacte par mot-clé est insuffisante : `"JavaScript"` et `"JS"`, `"Gestion de projet"` et `"Project management"`, `"React.js"` et `"ReactJS"` sont sémantiquement identiques mais textuellement différents. Seule une représentation vectorielle (embedding) capture ces équivalences.

Les exigences spécifiques à notre ATS sont :

- **Multilingue** : CV en français ET en anglais sur le marché cible
- **Spécialisation domaine** : les compétences techniques ont une sémantique dense (`"Spring Boot"` ≠ `"Spring Water"`)
- **Latence acceptable** : le matching doit être quasi-temps-réel (<200ms pour un query)
- **Déployable en self-hosted** : contraintes RGPD, données candidats sensibles
- **Coût maîtrisé** : pas de facturation par token à l'infini

---

## 2. Critères de décision

| Critère | Poids | Justification |
|---|---|---|
| **Qualité sémantique (MTEB score)** | Éliminatoire | Un mauvais embedding = un mauvais matching |
| **Support multilingue FR/EN** | Éliminatoire | Marché français, CVs bilingues fréquents |
| **Self-hostable / data residency** | Très élevé | Données candidats = données personnelles RGPD |
| **Latence d'inférence** | Élevé | Matching temps-réel attendu par le recruteur |
| **Licence commerciale** | Élevé | Usage production, pas de restriction NC |
| **Dimension des vecteurs** | Moyen | Impact direct sur les coûts de stockage vectoriel |
| **Support hybrid search (dense + sparse)** | Moyen | Améliore la précision sur les termes rares (noms de frameworks) |
| **Fine-tuning possible** | Moyen | Adaptation future sur un corpus RH spécifique |

---

## 3. Modèles évalués

### 3.1 Modèles open-source / self-hostables

---

#### A — `paraphrase-multilingual-MiniLM-L12-v2` (SBERT)

**Paramètres :** 118M · **Dimensions :** 384 · **Langues :** 50+ · **Licence :** Apache 2.0

**Avantages**
- Très léger, inférence rapide même sur CPU
- Multilingue natif, couvre FR/EN
- Gratuit, pas de dépendance externe
- Écosystème `sentence-transformers` mature

**Inconvénients**
- <cite index="29-1">Les modèles all-MiniLM originaux ont été dépassés sur les tâches de retrieval par les générations suivantes</cite>
- 384 dimensions — précision sémantique limitée sur les compétences techniques pointues
- Pas de support sparse natif (hybrid search impossible sans pipeline additionnel)
- Entraîné sur données généralistes, pas RH-spécifique

**Score MTEB (retrieval)** : ~50–54 (MTEB v1 English)

**Verdict** : Bon choix MVP / prototype. Insuffisant pour la production.

---

#### B — `all-mpnet-base-v2` (SBERT)

**Paramètres :** 109M · **Dimensions :** 768 · **Langues :** EN principalement · **Licence :** Apache 2.0

**Avantages**
- <cite index="20-1">Utilisé pour matcher des descriptions de postes avec les activités O\*NET via similarité cosinus — approche validée en recherche académique sur le matching CV/offre</cite>
- Meilleure qualité que MiniLM sur l'anglais
- Léger, CPU-friendly

**Inconvénients**
- Anglais principalement — performances dégradées sur FR
- Pas adapté à un ATS sur le marché français sans fine-tuning
- Dépassé en qualité par BGE-M3 et Jina v3

**Score MTEB (retrieval)** : ~57 (MTEB v1 English)

**Verdict** : Écarté pour le marché francophone.

---

#### C — `BGE-M3` (BAAI) ✅ **Candidat retenu**

**Paramètres :** 568M · **Dimensions :** 1024 · **Langues :** 100+ · **Licence :** MIT

**Avantages**
- <cite index="23-1">BGE-M3 produit en un seul forward pass trois types de vecteurs : dense (similarité cosinus), sparse (précision BM25-like), et multi-vecteur ColBERT — permettant le hybrid search depuis un seul modèle</cite>
- <cite index="28-1">Meilleur choix budget / self-hosted à l'échelle — pratique pour les équipes qui ont besoin de contrôle, de coûts prévisibles et de solides performances</cite>
- Licence MIT — usage commercial libre, self-hosting sans restriction
- <cite index="24-1">BGE-M3 (568M) montre une légère dégradation à 8K tokens (0.92) — reste performant sur les contextes longs</cite>
- Fine-tuning possible sur un corpus RH interne

**Inconvénients**
- 568M paramètres — GPU recommandé pour l'inférence en production (ou batching sur CPU)
- 1024 dimensions — coût de stockage vectoriel plus élevé que MiniLM

**Score MTEB (retrieval)** : ~63.0 (MTEB v1 multilingual)

**Verdict** : ✅ Retenu comme modèle principal.

---

#### D — `Jina Embeddings v3`

**Paramètres :** 570M · **Dimensions :** 1024 (réductible à 32 via MRL) · **Langues :** 100+ · **Licence :** CC BY-NC 4.0 (API) / commercial via contrat

**Avantages**
- <cite index="27-1">Jina v3 surpasse les embeddings propriétaires d'OpenAI et Cohere sur les tâches anglaises MTEB, tout en dépassant multilingual-e5-large-instruct sur toutes les tâches multilingues</cite>
- Adapters LoRA task-spécifiques (retrieval, classification, clustering)
- Dimensions réductibles via Matryoshka Representation Learning

**Inconvénients**
- <cite index="26-1">Licence CC-BY-NC-4.0 — restreint l'usage commercial direct des poids. Pour un usage commercial en production, il faut passer par l'API Jina ou un contrat commercial</cite>
- Dépendance à une API externe si on ne veut pas gérer la licence commerciale

**Verdict** : Écarté en self-hosting (licence NC). Viable si Jina API + contrat commercial.

---

### 3.2 Modèles propriétaires (API)

---

#### E — `text-embedding-3-large` (OpenAI)

**Dimensions :** 3072 · **Langues :** Multilingue · **Licence :** API payante

**Avantages**
- <cite index="25-1">Très forte qualité générale sur les benchmarks MTEB, meilleur score de cross-lingual retrieval (0.997)</cite>
- Zero ops — pas d'infra à gérer
- Contexte 8191 tokens

**Inconvénients**
- Données candidats envoyées à des serveurs OpenAI — **incompatible avec une data residency stricte RGPD**
- Coût variable et non prévisible selon le volume
- Dépendance à la disponibilité et aux changements de tarifs d'un tiers
- Impossible à fine-tuner sur un corpus RH propriétaire

**Verdict** : Écarté pour les données candidats. Acceptable uniquement pour des données non personnelles (descriptions de postes publiques).

---

#### F — `Cohere embed-v4`

**Dimensions :** 1024 · **Langues :** Multilingue · **Licence :** API payante

**Avantages**
- <cite index="25-1">Score MTEB 65.2 — meilleur parmi les modèles commerciaux listés en juillet 2026</cite>
- Intégration native avec Cohere Rerank pour un pipeline retrieval + reranking unifié

**Inconvénients**
- Mêmes problèmes de data residency que OpenAI pour les données candidats
- Coût à l'usage

**Verdict** : Écarté pour les mêmes raisons que OpenAI.

---

## 4. Tableau comparatif synthétique

| Modèle | MTEB Score | Multilingue | Self-hostable | Licence | Hybrid search | Verdict |
|---|---|---|---|---|---|---|
| MiniLM-L12-v2 | ~52 | ✅ 50L | ✅ | Apache 2.0 | ❌ | MVP uniquement |
| all-mpnet-base-v2 | ~57 | ⚠️ EN | ✅ | Apache 2.0 | ❌ | Écarté (FR) |
| **BGE-M3** | **~63** | **✅ 100L** | **✅** | **MIT** | **✅ natif** | **✅ Retenu** |
| Jina v3 | ~64 | ✅ 100L | ⚠️ (NC) | CC BY-NC | ✅ | Écarté (licence) |
| OpenAI text-3-large | ~64.6 | ✅ | ❌ | API payante | ❌ | Écarté (RGPD) |
| Cohere embed-v4 | ~65.2 | ✅ | ❌ | API payante | ❌ | Écarté (RGPD) |

---

## 5. Décision

**➡️ BGE-M3 (BAAI) est retenu comme modèle d'embedding principal.**

### Justification

1. **Triple output en un seul modèle** — dense + sparse + multi-vecteur. <cite index="23-1">La sortie sparse produit un dictionnaire terme-poids indexable comme un BM25 appris, permettant d'obtenir à la fois la précision BM25 et le rappel dense depuis le même modèle.</cite> Pour le skill matching, c'est crucial : `"Kubernetes"` ou `"LangChain"` sont des termes rares que le dense seul peut rater.

2. **Licence MIT** — usage commercial libre, self-hosting sans restriction, fine-tuning autorisé sur un corpus RH interne.

3. **100+ langues** — couvre FR/EN natif sans dégradation croisée.

4. **Data residency** — inférence 100% locale, aucune donnée candidat ne quitte l'infrastructure.

5. **Coût maîtrisé** — <cite index="28-1">BGE-M3 est le choix par défaut pour les équipes qui ont besoin de contrôle et de coûts prévisibles.</cite>

---

## 6. Architecture de matching recommandée

```
Candidat (skills[]) → BGE-M3 encoder → vecteur dense 1024-dim
                                      → vecteur sparse (BM25-like)

Offre (skills[])    → BGE-M3 encoder → vecteur dense 1024-dim
                                      → vecteur sparse

Qdrant hybrid query:
  score = α × cosine_similarity(dense_candidat, dense_offre)
        + β × sparse_overlap(sparse_candidat, sparse_offre)

Top-K offres → Reranker (optionnel) → Score de matching final
```

**Paramètres suggérés au démarrage :** α = 0.7, β = 0.3 (ajustable via A/B testing)

---

## 7. Stratégie d'évolution

| Phase | Modèle | Justification |
|---|---|---|
| MVP | MiniLM-L12-v2 | Démarrage rapide, CPU suffisant |
| Production | BGE-M3 | Qualité + hybrid search + MIT |
| Production avancée | BGE-M3 fine-tuné | Fine-tuning sur paires (offre, CV) annotées |
| Long terme | Reranker BAAI/bge-reranker-v2 | Améliore la précision du top-K |

---

## 8. Conséquences

### Positives
- Matching sémantique FR/EN sans dégradation croisée
- Hybrid search natif — robustesse sur les termes techniques rares
- Zéro dépendance API externe pour les données personnelles
- Chemin clair vers le fine-tuning sur corpus RH propriétaire

### Risques à mitiger

| Risque | Mitigation |
|---|---|
| 568M params — GPU nécessaire en prod haute volumétrie | Déploiement sur instance GPU dédiée (A10 ou T4) ; batching des embeddings à l'ingestion |
| Latence d'inférence sur CPU seul | Quantisation INT8 via `sentence-transformers` ; cache des embeddings déjà calculés |
| Drift si le modèle est mis à jour | Versionner le modèle + re-indexer si changement de version |
| Fine-tuning sur données insuffisantes | Augmentation de données synthétiques via LLM avant fine-tuning |

---

## 9. Références

- [BGE-M3 paper — BAAI](https://arxiv.org/abs/2309.07597)
- [MTEB Leaderboard — juillet 2026](https://huggingface.co/spaces/mteb/leaderboard)
- [BGE-M3 vs Jina v3 benchmark 2026](https://learn.engineering.vips.edu/compare/bge-m3-vs-jina-embeddings-v3)
- [Best Embedding Models 2026 — Ailog RAG](https://app.ailog.fr/en/blog/guides/choosing-embedding-models)
- [SBERT — conSultantBERT resume matching](https://www.emergentmind.com/topics/sentence-bert-sbert)
- [Resume matching with SBERT + O*NET](https://arxiv.org/pdf/2307.08580)

---

*Document généré le 2026-07-28 — à réviser si un modèle open-source MIT dépasse BGE-M3 sur MMTEB multilingue ou si les besoins de fine-tuning domaine-spécifique s'intensifient.*