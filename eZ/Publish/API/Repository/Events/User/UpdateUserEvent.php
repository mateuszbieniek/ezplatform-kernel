<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\User;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class UpdateUserEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $user;

    /** @var \eZ\Publish\API\Repository\Values\User\UserUpdateStruct */
    private $userUpdateStruct;

    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $updatedUser;

    public function __construct(
        User $updatedUser,
        User $user,
        UserUpdateStruct $userUpdateStruct
    ) {
        $this->user = $user;
        $this->userUpdateStruct = $userUpdateStruct;
        $this->updatedUser = $updatedUser;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserUpdateStruct(): UserUpdateStruct
    {
        return $this->userUpdateStruct;
    }

    public function getUpdatedUser(): User
    {
        return $this->updatedUser;
    }
}
