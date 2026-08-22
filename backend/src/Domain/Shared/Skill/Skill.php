<?php


namespace App\Domain\Shared\Skill;


final class Skill
{
    /**
     * @param array<string> $aliases Liste  altLabels / synonymes
     * @param array<int, float>|null $embedding Vector generated (ex: Qdrant)
     */
    public function __construct(
        private string $name,
        private string $slug,
        private string $canonicalName,
        private string $locale = 'en',
        private bool $isDefault = false,
        private array $aliases = [],
        private ?string $escoUri = null,
        private ?string $onetCode = null,
        private ?array $embedding = null,
        private ?string $id = null,
    ) {}

    /**
     * Named Constructor for the Initial Creation of a Skill
     *
     * @param array<string> $aliases
     */
    public static function create(
        string $name,
        string $slug,
        string $canonicalName,
        string $locale = 'en',
        array $aliases = [],
        ?string $escoUri = null,
        ?string $onetCode = null,
        bool $isDefault = false,
        ?string $id = null,
    ): self {
        return new self(
            name: $name,
            slug: $slug,
            canonicalName: $canonicalName,
            locale: $locale,
            isDefault: $isDefault,
            aliases: $aliases,
            escoUri: $escoUri,
            onetCode: $onetCode,
            id: $id
        );
    }

    //--------------------------------------------------------------------------
    // GETTERS
    //--------------------------------------------------------------------------

    public function id(): ?string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function canonicalName(): string
    {
        return $this->canonicalName;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @return array<string>
     */
    public function aliases(): array
    {
        return $this->aliases;
    }

    public function escoUri(): ?string
    {
        return $this->escoUri;
    }

    public function onetCode(): ?string
    {
        return $this->onetCode;
    }

    /**
     * @return array<int, float>|null
     */
    public function embedding(): ?array
    {
        return $this->embedding;
    }

    //--------------------------------------------------------------------------
    // SETTERS & DOMAIN BEHAVIOUR
    //--------------------------------------------------------------------------

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function setCanonicalName(string $canonicalName): self
    {
        $this->canonicalName = $canonicalName;
        return $this;
    }

    public function setDefault(bool $isDefault = true): self
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function setEscoUri(?string $escoUri): self
    {
        $this->escoUri = $escoUri;
        return $this;
    }

    public function setOnetCode(?string $onetCode): self
    {
        $this->onetCode = $onetCode;
        return $this;
    }

    /**
     * @param array<int, float>|null $embedding
     */
    public function setEmbedding(?array $embedding): self
    {
        $this->embedding = $embedding;
        return $this;
    }

    /**
     * Add an alias (avoid case-insensitive duplicates)
     */
    public function addAlias(string $alias): self
    {
        $normalized = trim($alias);
        if ($normalized === '') {
            return $this;
        }

        foreach ($this->aliases as $existingAlias) {
            if (mb_strtolower($existingAlias) === mb_strtolower($normalized)) {
                return $this;
            }
        }

        $this->aliases[] = $normalized;
        return $this;
    }

    /**
     *Removes an alias from the list
     */
    public function removeAlias(string $alias): self
    {
        $normalized = mb_strtolower(trim($alias));

        $this->aliases = array_values(
            array_filter(
                $this->aliases,
                fn(string $existing) => mb_strtolower($existing) !== $normalized
            )
        );

        return $this;
    }
}