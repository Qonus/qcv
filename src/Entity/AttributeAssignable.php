<?php

namespace App\Entity;

interface AttributeAssignable
{
    /** @return Attribute[] */
    public function getAttributes(): array;

    public function addAttribute(Attribute $attribute): static;

    public function removeAttribute(Attribute $attribute): static;
}