<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

// Implemented by Project (technology tags) and Position (relevant-project
// tags for CV generation) — mechanically identical ManyToMany to Tag, so
// one TagPicker component serves both instead of one-per-entity.
interface TaggableEntity
{
    /** @return Collection<int, Tag> */
    public function getTags(): Collection;

    public function addTag(Tag $tag): static;

    public function removeTag(Tag $tag): static;
}