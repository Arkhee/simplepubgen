<?php

namespace Simplepubgen\Xml;

interface Resource
{
    public function getContent(): string;

    public function getId(): string;

    public function getResourceId(): string;

    public function getFileName(): string;

    public function getResourceContent(): string;

    public function getProperties(): string;

    public function getMediaType(): string;
}
