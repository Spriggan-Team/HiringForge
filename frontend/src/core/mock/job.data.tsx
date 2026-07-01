import type { 
    JobSummary,
    JobView
} from "../../features/jobs/JobOffer";


export const jobsData: JobSummary[] = [
  {
    id: "1",
    title: "Développeur Front-end React",
    address: "Paris",
    publicationStatus: "published",
    activityStatus: "active",
    cardinal: {
      candidates: 18,
      interviews: 9,
      offers: 3,
      hired: 2,
    },
  },
  {
    id: "2",
    title: "Développeur Back-end Node.js",
    address: "Lyon",
    publicationStatus: "published",
    activityStatus: "active",
    cardinal: {
      candidates: 14,
      interviews: 6,
      offers: 2,
      hired: 1,
    },
  },
  {
    id: "3",
    title: "Développeur Full-stack",
    address: "Bordeaux",
    publicationStatus: "published",
    activityStatus: "pending",
    cardinal: {
      candidates: 22,
      interviews: 10,
      offers: 4,
      hired: 3,
    },
  },
  {
    id: "4",
    title: "UX/UI Designer",
    address: "Nantes",
    publicationStatus: "published",
    activityStatus: "active",
    cardinal: {
      candidates: 11,
      interviews: 5,
      offers: 2,
      hired: 1,
    },
  },
  {
    id: "5",
    title: "Product Designer",
    address: "Lille",
    publicationStatus: "draft",
    activityStatus: "pending",
    cardinal: {
      candidates: 4,
      interviews: 1,
      offers: 0,
      hired: 0,
    },
  },
  {
    id: "6",
    title: "Product Manager",
    address: "Marseille",
    publicationStatus: "published",
    activityStatus: "active",
    cardinal: {
      candidates: 27,
      interviews: 12,
      offers: 5,
      hired: 2,
    },
  },
  {
    id: "7",
    title: "DevOps Engineer",
    address: "Toulouse",
    publicationStatus: "published",
    activityStatus: "active",
    cardinal: {
      candidates: 15,
      interviews: 8,
      offers: 2,
      hired: 1,
    },
  },
  {
    id: "8",
    title: "Cloud Engineer AWS",
    address: "Montpellier",
    publicationStatus: "published",
    activityStatus: "pending",
    cardinal: {
      candidates: 9,
      interviews: 4,
      offers: 1,
      hired: 0,
    },
  },
  {
    id: "9",
    title: "Data Engineer",
    address: "Strasbourg",
    publicationStatus: "published",
    activityStatus: "active",
    cardinal: {
      candidates: 19,
      interviews: 9,
      offers: 3,
      hired: 2,
    },
  },
  {
    id: "10",
    title: "Data Scientist",
    address: "Nice",
    publicationStatus: "closed",
    activityStatus: "pending",
    cardinal: {
      candidates: 31,
      interviews: 15,
      offers: 6,
      hired: 1,
    },
  },
  {
    id: "11",
    title: "QA Automation Engineer",
    address: "Rennes",
    publicationStatus: "published",
    activityStatus: "active",
    cardinal: {
      candidates: 13,
      interviews: 7,
      offers: 2,
      hired: 1,
    },
  },
  {
    id: "12",
    title: "Mobile Developer Flutter",
    address: "Grenoble",
    publicationStatus: "draft",
    activityStatus: "pending",
    cardinal: {
      candidates: 5,
      interviews: 2,
      offers: 0,
      hired: 0,
    },
  },
  {
    id: "13",
    title: "Ingénieur IA",
    address: "Paris",
    publicationStatus: "published",
    activityStatus: "active",
    cardinal: {
      candidates: 38,
      interviews: 17,
      offers: 8,
      hired: 4,
    },
  },
  {
    id: "14",
    title: "Architecte Logiciel",
    address: "Lyon",
    publicationStatus: "published",
    activityStatus: "active",
    cardinal: {
      candidates: 16,
      interviews: 8,
      offers: 3,
      hired: 2,
    },
  },
  {
    id: "15",
    title: "Scrum Master",
    address: "Bordeaux",
    publicationStatus: "closed",
    activityStatus: "pending",
    cardinal: {
      candidates: 24,
      interviews: 11,
      offers: 4,
      hired: 1,
    },
  },
];


export const jobsViewData: JobView[] = [
{
    id: "1",
    title: "Développeur Front-end React",
    categories: ["Développement", "Frontend", "React"],
    salary: 48000,
    devise: "EUR",
    status: "published",
    activityStatus: "active",
    mainImage: undefined,
    content: {
      ops: [
        {
          insert: "Nous recherchons un Développeur Front-end React expérimenté pour rejoindre notre équipe de développement. Le candidat idéal aura une expérience de plusieurs années dans le développement de applications web avec React, ainsi qu'une bonne connaissance de HTML, CSS et JavaScript.",
        },
        {
          insert: "\n",
        },
        {
          insert: "Responsabilités :\n",
        },
        {
          insert: "- Développer des applications web avec React\n",
        },
        {
          insert: "- Collaborer avec l'équipe de design pour créer des interfaces utilisateur intuitives\n",
        },
        {
          insert: "- Travailler avec l'équipe de back-end pour intégrer les API\n",
        },
        {
          insert: "- Déboguer et optimiser les performances des applications\n",
        },
        {
          insert: "\n",
        },
        {
          insert: "Exigences :\n",
        },
        {
          insert: "- 3+ ans d'expérience dans le développement de applications web avec React\n",
        },
        {
          insert: "- Connaissance de HTML, CSS et JavaScript\n",
        },
        {
          insert: "- Compréhension des principes de développement de applications web\n",
        },
      ],
    },
    createdAt: new Date("2025-01-10"),
    updatedAt: new Date("2025-02-01"),
  },
  {
    id: "2",
    title: "Développeur Back-end Node.js",
    categories: ["Développement", "Backend", "Node.js"],
    salary: 50000,
    devise: "EUR",
    status: "published",
    activityStatus: "active",
    mainImage: undefined,
    content: {
      ops: [
        {
          insert: "Nous recherchons un Développeur Back-end Node.js expérimenté pour rejoindre notre équipe de développement. Le candidat idéal aura une expérience de plusieurs années dans le développement de applications web avec Node.js, ainsi qu'une bonne connaissance de JavaScript et de bases de données.",
        },
        {
          insert: "\n",
        },
        {
          insert: "Responsabilités :\n",
        },
        {
          insert: "- Développer des applications web avec Node.js\n",
        },
        {
          insert: "- Collaborer avec l'équipe de front-end pour créer des interfaces utilisateur intuitives\n",
        },
        {
          insert: "- Travailler avec l'équipe de base de données pour concevoir et implémenter des schémas de données\n",
        },
        {
          insert: "- Déboguer et optimiser les performances des applications\n",
        },
        {
          insert: "\n",
        },
        {
          insert: "Exigences :\n",
        },
        {
          insert: "- 3+ ans d'expérience dans le développement de applications web avec Node.js\n",
        },
        {
          insert: "- Connaissance de JavaScript et de bases de données\n",
        },
        {
          insert: "- Compréhension des principes de développement de applications web\n",
        },
      ],
    },
    createdAt: new Date("2025-01-15"),
    updatedAt: new Date("2025-02-08"),
  },
  {
    id: "3",
    title: "Développeur Full-stack",
    categories: ["Développement", "Full Stack"],
    salary: 55000,
    devise: "EUR",
    status: "published",
    activityStatus: "pending",
    mainImage: undefined,
    content: {
      ops: [
        {
          insert: "Nous recherchons un Développeur Full-stack expérimenté pour rejoindre notre équipe de développement. Le candidat idéal aura une expérience de plusieurs années dans le développement de applications web avec des technologies full-stack, ainsi qu'une bonne connaissance de HTML, CSS, JavaScript et de bases de données.",
        },
        {
          insert: "\n",
        },
        {
          insert: "Responsabilités :\n",
        },
        {
          insert: "- Développer des applications web avec des technologies full-stack\n",
        },
        {
          insert: "- Collaborer avec l'équipe de design pour créer des interfaces utilisateur intuitives\n",
        },
        {
          insert: "- Travailler avec l'équipe de back-end pour intégrer les API\n",
        },
        {
          insert: "- Déboguer et optimiser les performances des applications\n",
        },
        {
          insert: "\n",
        },
        {
          insert: "Exigences :\n",
        },
        {
          insert: "- 3+ ans d'expérience dans le développement de applications web avec des technologies full-stack\n",
        },
        {
          insert: "- Connaissance de HTML, CSS, JavaScript et de bases de données\n",
        },
        {
          insert: "- Compréhension des principes de développement de applications web\n",
        },
      ],
    },
    createdAt: new Date("2025-01-22"),
    updatedAt: new Date("2025-02-14"),
  },
  {
    id: "4",
    title: "UX/UI Designer",
    categories: ["Design", "UX", "UI"],
    salary: 43000,
    devise: "EUR",
    status: "published",
    activityStatus: "active",
    mainImage: undefined,
    content: {
      ops: [
        {
          insert: "Nous recherchons un UX/UI Designer expérimenté pour rejoindre notre équipe de design. Le candidat idéal aura une expérience de plusieurs années dans la conception d'interfaces utilisateur intuitives et une bonne connaissance des outils de design.",
        },
        {
          insert: "\n",
        },
        {
          insert: "Responsabilités :\n",
        },
        {
          insert: "- Concevoir des interfaces utilisateur intuitives\n",
        },
        {
          insert: "- Collaborer avec l'équipe de développement pour intégrer les designs\n",
        },
        {
          insert: "- Travailler avec l'équipe de produit pour comprendre les besoins des utilisateurs\n",
        },
        {
          insert: "- Développer des prototypes et des maquettes pour valider les designs\n",
        },
        {
          insert: "\n",
        },
        {
          insert: "Exigences :\n",
        },
        {
          insert: "- 3+ ans d'expérience dans la conception d'interfaces utilisateur\n",
        },
        {
          insert: "- Connaissance des outils de design tels que Sketch, Figma, Adobe XD\n",
        },
        {
          insert: "- Compréhension des principes de design d'interfaces utilisateur\n",
        },
      ],
    },
    createdAt: new Date("2025-01-27"),
    updatedAt: new Date("2025-02-11"),
  },
    {
        id: "4",
        title: "UX/UI Designer",
        categories: ["Design", "UX", "UI"],
        salary: 43000,
        devise: "EUR",
        status: "published",
        activityStatus: "active",
        mainImage: undefined,
        content: {
            ops: [
            {
                insert: "Nous recherchons un UX/UI Designer expérimenté pour rejoindre notre équipe de design. Le candidat idéal aura une expérience de plusieurs années dans la conception d'interfaces utilisateur intuitives et une bonne connaissance des outils de design.",
            },
            {
                insert: "\n",
            },
            {
                insert: "Responsabilités :\n",
            },
            {
                insert: "- Concevoir des interfaces utilisateur intuitives\n",
            },
            {
                insert: "- Collaborer avec l'équipe de développement pour intégrer les designs\n",
            },
            {
                insert: "- Travailler avec l'équipe de produit pour comprendre les besoins des utilisateurs\n",
            },
            {
                insert: "- Développer des prototypes et des maquettes pour valider les designs\n",
            },
            {
                insert: "\n",
            },
            {
                insert: "Exigences :\n",
            },
            {
                insert: "- 3+ ans d'expérience dans la conception d'interfaces utilisateur\n",
            },
            {
                insert: "- Connaissance des outils de design tels que Sketch, Figma, Adobe XD\n",
            },
            {
                insert: "- Compréhension des principes de design d'interfaces utilisateur\n",
            },
            ],
        },
        createdAt: new Date("2025-01-27"),
        updatedAt: new Date("2025-02-11"),
    },
    {
        id: "5",
        title: "Product Designer",
        categories: ["Design", "Product"],
        salary: 46000,
        devise: "EUR",
        status: "draft",
        activityStatus: "pending",
        mainImage: undefined,
        content: {
            ops: [
            {
                insert: "Nous recherchons un Product Designer expérimenté pour rejoindre notre équipe de produit. Le candidat idéal aura une expérience de plusieurs années dans la conception de produits et une bonne connaissance des outils de design.",
            },
            {
                insert: "\n",
            },
            {
                insert: "Responsabilités :\n",
            },
            {
                insert: "- Concevoir des produits innovants et utilisateur-friendly\n",
            },
            {
                insert: "- Collaborer avec l'équipe de développement pour intégrer les designs\n",
            },
            {
                insert: "- Travailler avec l'équipe de produit pour comprendre les besoins des utilisateurs\n",
            },
            {
                insert: "- Développer des prototypes et des maquettes pour valider les designs\n",
            },
            {
                insert: "\n",
            },
            {
                insert: "Exigences :\n",
            },
            {
                insert: "- 3+ ans d'expérience dans la conception de produits\n",
            },
            {
                insert: "- Connaissance des outils de design tels que Sketch, Figma, Adobe XD\n",
            },
            {
                insert: "- Compréhension des principes de design de produits\n",
            },
            ],
        },
        createdAt: new Date("2025-02-02"),
    },
    {
        id: "6",
        title: "Product Manager",
        categories: ["Product", "Management"],
        salary: 62000,
        devise: "EUR",
        status: "published",
        activityStatus: "active",
        mainImage: undefined,
        content: {
            ops: [
            {
                insert: "Nous recherchons un Product Manager expérimenté pour rejoindre notre équipe de produit. Le candidat idéal aura une expérience de plusieurs années dans la gestion de produits et une bonne connaissance des principes de développement de produits.",
            },
            {
                insert: "\n",
            },
            {
                insert: "Responsabilités :\n",
            },
            {
                insert: "- Gérer le cycle de vie des produits\n",
            },
            {
                insert: "- Collaborer avec l'équipe de développement pour définir les priorités des produits\n",
            },
            {
                insert: "- Travailler avec l'équipe de vente pour comprendre les besoins des clients\n",
            },
            {
                insert: "- Développer des plans de lancement de produits\n",
            },
            {
                insert: "\n",
            },
            {
                insert: "Exigences :\n",
            },
            {
                insert: "- 3+ ans d'expérience dans la gestion de produits\n",
            },
            {
                insert: "- Connaissance des principes de développement de produits\n",
            },
            {
                insert: "- Compréhension des besoins des clients\n",
            },
            ],
        },
        createdAt: new Date("2025-02-05"),
        updatedAt: new Date("2025-02-28"),
    },
    {
        id: "7",
        title: "DevOps Engineer",
        categories: ["Infrastructure", "DevOps", "Cloud"],
        salary: 58000,
        devise: "EUR",
        status: "published",
        activityStatus: "active",
        mainImage: undefined,
        content: {
            ops: [
            {
                insert: "Nous recherchons un DevOps Engineer expérimenté pour rejoindre notre équipe de développement. Le candidat idéal aura une expérience de plusieurs années dans la mise en œuvre de solutions DevOps et une bonne connaissance des outils de développement et d'infrastructure.",
            },
            {
                insert: "\n",
            },
            {
                insert: "Responsabilités :\n",
            },
            {
                insert: "- Mettre en œuvre des solutions DevOps\n",
            },
            {
                insert: "- Collaborer avec l'équipe de développement pour automatiser les processus de déploiement\n",
            },
            {
                insert: "- Travailler avec l'équipe d'infrastructure pour garantir la stabilité et la sécurité des systèmes\n",
            },
            {
                insert: "- Développer des scripts de déploiement et de mise à jour\n",
            },
            {
                insert: "\n",
            },
            {
                insert: "Exigences :\n",
            },
            {
                insert: "- 3+ ans d'expérience dans la mise en œuvre de solutions DevOps\n",
            },
            {
                insert: "- Connaissance des outils de développement et d'infrastructure tels que Docker, Kubernetes, Jenkins\n",
            },
            {
                insert: "- Compréhension des principes de développement et d'infrastructure\n",
            },
            ],
        },
        createdAt: new Date("2025-02-10"),
        updatedAt: new Date("2025-03-01"),
    },
  {
    id: "8",
    title: "Cloud Engineer AWS",
    categories: ["Cloud", "AWS", "Infrastructure"],
    salary: 60000,
    devise: "EUR",
    status: "published",
    activityStatus: "pending",
    mainImage: undefined,
    content: {
        ops: [
            { insert: "Cloud Engineer AWS\n", attributes: { header: 1 } },
            { insert: "\nNous recherchons un Cloud Engineer AWS pour renforcer notre infrastructure cloud et accompagner notre croissance.\n\n" },
            { insert: "Missions\n", attributes: { bold: true } },
            { insert: "- Concevoir et maintenir des architectures AWS scalables\n- Automatiser les déploiements (CI/CD)\n- Optimiser les coûts cloud\n\n" },
            { insert: "Compétences\n", attributes: { bold: true } },
            { insert: "- AWS (EC2, S3, Lambda)\n- Terraform / CloudFormation\n- Docker & Kubernetes\n\n" },
            { insert: "Profil\n", attributes: { bold: true } },
            { insert: "- 3+ ans en cloud engineering\n- Culture DevOps\n- Sens de la performance\n" },
        ],
    },
    createdAt: new Date("2025-02-15"),
    updatedAt: new Date("2025-03-03"),
  },
  {
    id: "9",
    title: "Data Engineer",
    categories: ["Data", "Engineering"],
    salary: 57000,
    devise: "EUR",
    status: "published",
    activityStatus: "active",
    mainImage: undefined,
    content:{
        ops: [
            { insert: "Data Engineer\n", attributes: { header: 1 } },
            { insert: "\nVous rejoindrez une équipe data en pleine expansion pour construire des pipelines robustes.\n\n" },
            { insert: "Missions\n", attributes: { bold: true } },
            { insert: "- Construire des pipelines ETL\n- Gérer les flux de données\n- Optimiser les bases analytiques\n\n" },
            { insert: "Stack\n", attributes: { bold: true } },
            { insert: "- Python\n- Spark / Airflow\n- BigQuery / Snowflake\n\n" },
            { insert: "Profil\n", attributes: { bold: true } },
            { insert: "- Expérience data engineering\n- Esprit analytique\n- Bonnes pratiques data\n" },
        ],
    },
    createdAt: new Date("2025-02-18"),
    updatedAt: new Date("2025-03-08"),
  },
  {
    id: "10",
    title: "Data Scientist",
    categories: ["Data", "Machine Learning", "AI"],
    salary: 65000,
    devise: "EUR",
    status: "closed",
    activityStatus: "pending",
    mainImage: undefined,
    content: {
        ops: [
        { insert: "Data Scientist\n", attributes: { header: 1 } },
        { insert: "\nNous recherchons un Data Scientist pour travailler sur des modèles prédictifs avancés.\n\n" },
        { insert: "Missions\n", attributes: { bold: true } },
        { insert: "- Développer des modèles ML\n- Analyse statistique avancée\n- Mise en production des modèles\n\n" },
        { insert: "Compétences\n", attributes: { bold: true } },
        { insert: "- Python / R\n- Scikit-learn / PyTorch\n- SQL\n\n" },
        { insert: "Profil\n", attributes: { bold: true } },
        { insert: "- Bac+5 data science\n- Expérience ML production\n- Esprit recherche\n" },
        ],
    },
    createdAt: new Date("2025-02-22"),
    updatedAt: new Date("2025-03-15"),
  },
  {
    id: "11",
    title: "QA Automation Engineer",
    categories: ["QA", "Automation", "Testing"],
    salary: 47000,
    devise: "EUR",
    status: "published",
    activityStatus: "active",
    mainImage: undefined,
    content: {
        ops: [
            { insert: "QA Automation Engineer\n", attributes: { header: 1 } },
            { insert: "\nVous serez responsable de garantir la qualité produit via des tests automatisés.\n\n" },
            { insert: "Missions\n", attributes: { bold: true } },
            { insert: "- Automatisation des tests E2E\n- Mise en place de frameworks QA\n- Intégration CI/CD\n\n" },
            { insert: "Stack\n", attributes: { bold: true } },
            { insert: "- Cypress / Playwright\n- Jest\n- GitLab CI\n\n" },
            { insert: "Profil\n", attributes: { bold: true } },
            { insert: "- QA automation\n- Sens du détail\n- Expérience agile\n" },
        ],
    },
    createdAt: new Date("2025-03-01"),
    updatedAt: new Date("2025-03-20"),
  },
  {
    id: "12",
    title: "Mobile Developer Flutter",
    categories: ["Mobile", "Flutter"],
    salary: 49000,
    devise: "EUR",
    status: "draft",
    activityStatus: "pending",
    mainImage: undefined,
    content: {
        ops: [
            { insert: "Mobile Developer Flutter\n", attributes: { header: 1 } },
            { insert: "\nNous développons une application mobile multi-plateforme en Flutter.\n\n" },
            { insert: "Missions\n", attributes: { bold: true } },
            { insert: "- Développement mobile Flutter\n- Intégration API backend\n- Optimisation UX mobile\n\n" },
            { insert: "Compétences\n", attributes: { bold: true } },
            { insert: "- Dart / Flutter\n- Firebase\n- REST APIs\n\n" },
            { insert: "Profil\n", attributes: { bold: true } },
            { insert: "- 2+ ans Flutter\n- Autonomie mobile\n- Sens UI\n" },
        ],
    },
    createdAt: new Date("2025-03-05"),
  },
  {
    id: "13",
    title: "Ingénieur IA",
    categories: ["IA", "Machine Learning", "Python"],
    salary: 72000,
    devise: "EUR",
    status: "published",
    activityStatus: "active",
    mainImage: undefined,
    content: {
        ops: [
            { insert: "Ingénieur IA\n", attributes: { header: 1 } },
            { insert: "\nVous travaillerez sur des modèles d’intelligence artificielle avancés.\n\n" },
            { insert: "Missions\n", attributes: { bold: true } },
            { insert: "- Développement modèles IA\n- NLP / Computer Vision\n- Déploiement ML\n\n" },
            { insert: "Stack\n", attributes: { bold: true } },
            { insert: "- Python\n- PyTorch / TensorFlow\n- Hugging Face\n\n" },
            { insert: "Profil\n", attributes: { bold: true } },
            { insert: "- Bac+5 IA ou recherche\n- Expérience ML\n- Curiosité forte\n" },
        ],
    },
    createdAt: new Date("2025-03-10"),
    updatedAt: new Date("2025-03-28"),
  },
  {
    id: "14",
    title: "Architecte Logiciel",
    categories: ["Architecture", "Software"],
    salary: 76000,
    devise: "EUR",
    status: "published",
    activityStatus: "active",
    mainImage: undefined,
        content: {
        ops: [
            { insert: "Architecte Logiciel\n", attributes: { header: 1 } },
            { insert: "\nVous définirez l’architecture globale des systèmes de l’entreprise.\n\n" },
            { insert: "Responsabilités\n", attributes: { bold: true } },
            { insert: "- Définition architecture logicielle\n- Choix technologiques\n- Scalabilité et performance\n\n" },
            { insert: "Stack\n", attributes: { bold: true } },
            { insert: "- Microservices\n- Cloud AWS\n- Docker / Kubernetes\n\n" },
            { insert: "Profil\n", attributes: { bold: true } },
            { insert: "- 7+ ans expérience\n- Vision système\n- Leadership technique\n" },
        ],
    },
    createdAt: new Date("2025-03-12"),
    updatedAt: new Date("2025-04-02"),
  },
  {
    id: "15",
    title: "Scrum Master",
    categories: ["Agile", "Scrum", "Management"],
    salary: 54000,
    devise: "EUR",
    status: "closed",
    activityStatus: "pending",
    mainImage: undefined,
    content: {
        ops: [
            { insert: "Scrum Master\n", attributes: { header: 1 } },
            { insert: "\nVous faciliterez les process agiles et l’organisation des équipes produit.\n\n" },
            { insert: "Missions\n", attributes: { bold: true } },
            { insert: "- Animation cérémonies Scrum\n- Coaching équipes\n- Amélioration continue\n\n" },
            { insert: "Compétences\n", attributes: { bold: true } },
            { insert: "- Agile / Scrum\n- Communication\n- Gestion d’équipe\n\n" },
            { insert: "Profil\n", attributes: { bold: true } },
            { insert: "- Expérience Scrum Master\n- Leadership soft skills\n- Sens produit\n" },
        ],
    },
    createdAt: new Date("2025-03-18"),
    updatedAt: new Date("2025-04-05"),
  },
];