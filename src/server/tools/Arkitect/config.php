<?php

declare(strict_types=1);

use Tools\Arkitect\ComponentMaps\AdminUserComponent;
use Tools\Arkitect\ComponentMaps\AuthComponent;
use Tools\Arkitect\ComponentMaps\LibraryComponent;
use Tools\Arkitect\ComponentMaps\MediaComponent;
use Tools\Arkitect\ComponentMaps\PersonComponent;
use Tools\Arkitect\ComponentMaps\SongComponent;
use Tools\Arkitect\ComponentMaps\SupportComponent;
use Tools\Arkitect\ComponentMaps\VenueComponent;
use Tools\Arkitect\Define;

return [
    new Define(LibraryComponent::DateType),
    new Define(LibraryComponent::ResultType),

    new Define(SupportComponent::Domain, [
        SupportComponent::Domain,
        LibraryComponent::DateType,
        LibraryComponent::ResultType,
    ]),
    new Define(SupportComponent::Contracts),
    new Define(SupportComponent::Optional),
    new Define(SupportComponent::Collection),
    new Define(SupportComponent::UseCase, [
        SupportComponent::UseCase,
        AdminUserComponent::Domain,
        SupportComponent::Contracts,
        SupportComponent::Domain,
        SupportComponent::Optional,
        LibraryComponent::ResultType,
    ]),

    new Define(AdminUserComponent::Domain, [
        AdminUserComponent::Domain,
        SupportComponent::Domain,
        SupportComponent::Contracts,
        SupportComponent::Collection,
        LibraryComponent::ResultType,
    ]),
    new Define(AdminUserComponent::UseCase, [
        AdminUserComponent::Domain,
        AuthComponent::Domain,
        SupportComponent::Contracts,
        SupportComponent::Domain,
        SupportComponent::UseCase,
        LibraryComponent::ResultType,
    ]),

    new Define(AuthComponent::Domain, [
        AuthComponent::Domain,
        AdminUserComponent::Domain,
        SupportComponent::Domain,
        SupportComponent::Contracts,
        LibraryComponent::ResultType,
    ]),
    new Define(AuthComponent::UseCase, [
        AuthComponent::Domain,
        AdminUserComponent::Domain,
        SupportComponent::Contracts,
        SupportComponent::Domain,
        SupportComponent::UseCase,
        LibraryComponent::ResultType,
    ]),

    new Define(PersonComponent::Domain, [
        PersonComponent::Domain,
        SupportComponent::Domain,
        SupportComponent::Contracts,
        SupportComponent::Optional,
        LibraryComponent::ResultType,
    ]),
    new Define(PersonComponent::UseCase, [
        PersonComponent::Domain,
        AdminUserComponent::Domain,
        AuthComponent::Domain,
        SupportComponent::Contracts,
        SupportComponent::Domain,
        SupportComponent::Optional,
        SupportComponent::UseCase,
        LibraryComponent::ResultType,
    ]),

    new Define(VenueComponent::Domain, [
        VenueComponent::Domain,
        SupportComponent::Domain,
        SupportComponent::Contracts,
        SupportComponent::Optional,
        LibraryComponent::ResultType,
    ]),
    new Define(VenueComponent::UseCase, [
        VenueComponent::Domain,
        AdminUserComponent::Domain,
        AuthComponent::Domain,
        SupportComponent::Contracts,
        SupportComponent::Domain,
        SupportComponent::Optional,
        SupportComponent::UseCase,
        LibraryComponent::ResultType,
    ]),

    new Define(MediaComponent::Domain, [
        MediaComponent::Domain,
        LibraryComponent::DateType,
        SupportComponent::Domain,
        SupportComponent::Contracts,
        SupportComponent::Optional,
        LibraryComponent::ResultType,
    ]),

    new Define(SongComponent::Domain, [
        SongComponent::Domain,
        MediaComponent::Domain,
        PersonComponent::Domain,
        SupportComponent::Domain,
        SupportComponent::Contracts,
        SupportComponent::Collection,
        SupportComponent::Optional,
        LibraryComponent::ResultType,
    ]),
    new Define(SongComponent::Query, [
        SongComponent::Domain,
        MediaComponent::Domain,
    ]),
    new Define(SongComponent::Assemble, [
        SongComponent::Domain,
        MediaComponent::Domain,
        PersonComponent::Domain,
    ]),
    new Define(SongComponent::UseCase, [
        SongComponent::Domain,
        SongComponent::Query,
        SongComponent::Assemble,
        AdminUserComponent::Domain,
        AuthComponent::Domain,
        SupportComponent::Contracts,
        SupportComponent::Domain,
        SupportComponent::Optional,
        SupportComponent::UseCase,
        LibraryComponent::ResultType,
    ]),
];
