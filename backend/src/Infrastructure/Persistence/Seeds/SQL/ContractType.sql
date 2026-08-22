
INSERT INTO contract_type (label, country, `default`, organization_id) VALUES
-- 🇫🇷 FRANCE (12)
('CDI (Contrat à Durée Indéterminée)', 'FR', 1, NULL),
('CDD (Contrat à Durée Déterminée)', 'FR', 1, NULL),
('Contrat d''Apprentissage', 'FR', 1, NULL),
('Contrat de Professionnalisation', 'FR', 1, NULL),
('Stage Conventionné', 'FR', 1, NULL),
('Intérim / Mission Temporaire', 'FR', 1, NULL),
('Freelance / Indépendant', 'FR', 1, NULL),
('CDI de Chantier ou d''Opération', 'FR', 1, NULL),
('CDD Saisonnier', 'FR', 1, NULL),
('Contrat de Portage Salarial', 'FR', 1, NULL),
('Contrat d''Extra (Restauration/Hôtellerie)', 'FR', 1, NULL),
('Volontariat International en Entreprise (VIE)', 'FR', 1, NULL),

-- 🇧🇪 BELGIQUE (8)
('Contrat à Durée Indéterminée (CDI)', 'BE', 1, NULL),
('Contrat à Durée Déterminée (CDD)', 'BE', 1, NULL),
('Contrat de Travail Intérimaire', 'BE', 1, NULL),
('Stage Professionnel', 'BE', 1, NULL),
('Freelance / Consultant Indépendant', 'BE', 1, NULL),
('Contrat de Travail Netudiant', 'BE', 1, NULL),
('Contrat de Remplacement', 'BE', 1, NULL),
('Contrat de Travail à Temps Partiel', 'BE', 1, NULL),

-- 🇨🇭 SUISSE (8)
('Contrat de Travail à Durée Indéterminée', 'CH', 1, NULL),
('Contrat de Travail à Durée Déterminée', 'CH', 1, NULL),
('Contrat d''Apprentissage (CFC)', 'CH', 1, NULL),
('Stage Académique / Post-grade', 'CH', 1, NULL),
('Freelance / Mandataire Indépendant', 'CH', 1, NULL),
('Contrat de Travail sur Appel / Horaire', 'CH', 1, NULL),
('Contrat Saisonnier (Hôtellerie/Montagne)', 'CH', 1, NULL),
('Contrat Temporaire / Mission', 'CH', 1, NULL),

-- 🇨🇦 CANADA / QUÉBEC (8)
('Emploi Permanent à Temps Plein', 'CA', 1, NULL),
('Emploi Permanent à Temps Partiel', 'CA', 1, NULL),
('Contrat à Durée Déterminée (Temporaire)', 'CA', 1, NULL),
('Stage Co-op / Universitaire', 'CA', 1, NULL),
('Travailleur Autonome / Contractuel', 'CA', 1, NULL),
('Emploi Saisonnier / Estival', 'CA', 1, NULL),
('Emploi Occasionnel / Sur Appel', 'CA', 1, NULL),
('Stage Postdoctoral', 'CA', 1, NULL),

-- 🇺🇸 ÉTATS-UNIS / STANDARDS INTERNATIONAUX (8)
('Full-Time (Permanent)', 'US', 1, NULL),
('Part-Time (Permanent)', 'US', 1, NULL),
('Contractor (W2)', 'US', 1, NULL),
('Contractor (1099 / Independent)', 'US', 1, NULL),
('Internship', 'US', 1, NULL),
('Temporary / Seasonal', 'US', 1, NULL),
('Casual / On-Call', 'US', 1, NULL),
('Apprenticeship', 'US', 1, NULL),

-- 🇬🇧 ROYAUME-UNI (6)
('Permanent Full-Time', 'GB', 1, NULL),
('Permanent Part-Time', 'GB', 1, NULL),
('Fixed-Term Contract (FTC)', 'GB', 1, NULL),
('Contractor / Freelance (Outside IR35)', 'GB', 1, NULL),
('Zero-Hours Contract', 'GB', 1, NULL),
('Graduate Scheme / Internship', 'GB', 1, NULL),

-- 🇩🇪 ALLEMAGNE (6)
('Unbefristeter Arbeitsvertrag (Permanent)', 'DE', 1, NULL),
('Befristeter Arbeitsvertrag (Fixed-Term)', 'DE', 1, NULL),
('Teilzeitbeschäftigung (Part-Time)', 'DE', 1, NULL),
('Freier Mitarbeiter / Freelancer', 'DE', 1, NULL),
('Praktikum (Internship)', 'DE', 1, NULL),
('Duales Studium / Ausbildung', 'DE', 1, NULL),

-- 🌍 GÉNÉRIQUES POUR LE TÉLÉTRAVAIL GLOBAL / REMOTE (6)
('International Contractor', 'ZZ', 1, NULL),
('Full-Remote Full-Time', 'ZZ', 1, NULL),
('Full-Remote Part-Time', 'ZZ', 1, NULL),
('Consulting Agreement', 'ZZ', 1, NULL),
('Vendor / B2B Services', 'ZZ', 1, NULL),
('Global Executive Contract', 'ZZ', 1, NULL);