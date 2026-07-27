<?php

namespace App\Domain\Shared\Skill;

use DomainException;

class Skill
{
    public function __construct(
        public string $name,
        public string $slug,
        public bool $isDefault = false,
        public array $aliases = [],
        public ?string $id = null,
    ){}

    public static function create(
        string $name,
        string $slug,
        array $aliases = [],
        bool $isDefault = false,
        ?string $id = null,
    ){
        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            isDefault: $isDefault,
            aliases: $aliases
        );
    }

    public function hydrate(
        string $name,
        string $slug,
        bool $isDefault,
        string $id,
    ){
        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            isDefault: $isDefault
        );
    }

    //-----------------
    //-- GETTERS
    //---------------------

    public function id(){
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function  slug(){
        return $this->slug;
    }

    public function aliases(){
        return $this->aliases;
    }

    public function  isDefault()  {
        return $this->isDefault;
    }

    //-------------------
    //------ SETTERS
    //-------------------

    public function setId(string $id)
    { 
        $this->id = $id;
        return $this;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setSlug(string $slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function setDefault()
    {
        if(!$this->isDefault)
            $this->isDefault = true;
        return $this->isDefault;
    }

    public function addAlias(string $alias){
        $finded = array_find($this->aliases, fn($value) => $value === $alias);
        if(!$finded)
            $this->aliases[] = $alias;
        return $this;
    }

    public function removeAlias(string $alias){
        $key = array_find_key($this->aliases, fn($key) => $key === $alias);
        if($key){
            array_splice($this->aliases ,$key, 1);
            $this->aliases = array_values($this->aliases);
        }
        return $this;
    }
}