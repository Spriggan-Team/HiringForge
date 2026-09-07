-- Nettoyage optionnel
DELETE FROM skill_aliases;
DELETE FROM skills_translation;
DELETE FROM skills;

-- 1. Insertion  12 skills
INSERT INTO skills (id, canonical_name, esco_uri, onet_code, is_default) VALUES
(1, 'python programming', 'http://data.europa.eu/esco/skill/1', '15-1132.00', 1),
(2, 'project management', 'http://data.europa.eu/esco/skill/2', '11-9151.00', 1),
(3, 'data analysis', 'http://data.europa.eu/esco/skill/3', '15-2051.00', 1),
(4, 'machine learning', 'http://data.europa.eu/esco/skill/4', '15-2051.01', 1),
(5, 'docker containerization', 'http://data.europa.eu/esco/skill/5', '15-1199.08', 1),
(6, 'sql database management', 'http://data.europa.eu/esco/skill/6', '15-1141.00', 1),
(7, 'git version control', 'http://data.europa.eu/esco/skill/7', '15-1134.00', 1),
(8, 'agile methodology', 'http://data.europa.eu/esco/skill/8', '11-9151.02', 1),
(9, 'symfony framework', 'http://data.europa.eu/esco/skill/9', '15-1132.09', 1),
(10, 'api design', 'http://data.europa.eu/esco/skill/10', '15-1133.00', 1),
(11, 'problem solving', 'http://data.europa.eu/esco/skill/11', '00-0000.01', 1),
(12, 'technical writing', 'http://data.europa.eu/esco/skill/12', '27-3042.00', 1);

-- 2. Insertion des traductions (language_id: 1 -> en, 2 -> fr)
INSERT INTO skills_translation (id, skill_id, language_id, name, slug) VALUES
(1, 1, 1, 'Python Programming', 'python-programming'),
(2, 1, 2, 'Programmation Python', 'programmation-python'),
(3, 2, 1, 'Project Management', 'project-management'),
(4, 2, 2, 'Gestion de projet', 'gestion-de-projet'),
(5, 3, 1, 'Data Analysis', 'data-analysis'),
(6, 3, 2, 'Analyse de données', 'analyse-de-donnees'),
(7, 4, 1, 'Machine Learning', 'machine-learning'),
(8, 4, 2, 'Apprentissage automatique', 'apprentissage-automatique'),
(9, 5, 1, 'Docker Containerization', 'docker-containerization'),
(10, 5, 2, 'Conteneurisation Docker', 'conteneurisation-docker'),
(11, 6, 1, 'SQL Database Management', 'sql-database-management'),
(12, 6, 2, 'Gestion de base de données SQL', 'gestion-de-base-de-donnees-sql'),
(13, 7, 1, 'Git Version Control', 'git-version-control'),
(14, 7, 2, 'Gestion de version Git', 'gestion-de-version-git'),
(15, 8, 1, 'Agile Methodology', 'agile-methodology'),
(16, 8, 2, 'Méthodologie Agile', 'methodologie-agile'),
(17, 9, 1, 'Symfony Framework', 'symfony-framework'),
(18, 9, 2, 'Framework Symfony', 'framework-symfony'),
(19, 10, 1, 'API Design', 'api-design'),
(20, 10, 2, 'Conception d API', 'conception-d-api'),
(21, 11, 1, 'Problem Solving', 'problem-solving'),
(22, 11, 2, 'Résolution de problèmes', 'resolution-de-problemes'),
(23, 12, 1, 'Technical Writing', 'technical-writing'),
(24, 12, 2, 'Rédaction technique', 'redaction-technique');

-- 3. Insertion des alias
INSERT INTO skill_aliases (id, skill_id, alias) VALUES
(1, 1, 'Python'),
(2, 1, 'Py'),
(3, 2, 'Management de projet'),
(4, 2, 'PM'),
(5, 3, 'Data Analytics'),
(6, 4, 'ML'),
(7, 5, 'Docker'),
(8, 6, 'SQL'),
(9, 7, 'Git'),
(10, 8, 'Agile'),
(11, 9, 'Symfony'),
(12, 10, 'REST API');

