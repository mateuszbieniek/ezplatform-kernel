<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Notification\CreateStruct;
use eZ\Publish\SPI\Persistence\Notification\Handler;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use eZ\Publish\SPI\Persistence\Notification\UpdateStruct;
use eZ\Publish\API\Repository\Values\Notification\Notification as APINotification;

/**
 * SPI cache for Notification Handler.
 *
 * @see \eZ\Publish\SPI\Persistence\Notification\Handler
 */
class NotificationHandler extends AbstractHandler implements Handler
{
    /**
     * {@inheritdoc}
     */
    public function createNotification(CreateStruct $createStruct): Notification
    {
        $this->logger->logCall(__METHOD__, [
            'createStruct' => $createStruct,
        ]);

        $this->cache->deleteItems([
            'ez-notification-count-' . $createStruct->ownerId,
            'ez-notification-pending-count-' . $createStruct->ownerId,
        ]);

        return $this->persistenceHandler->notificationHandler()->createNotification($createStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function updateNotification(APINotification $notification, UpdateStruct $updateStruct): Notification
    {
        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notification->id,
        ]);

        $this->cache->deleteItems([
            'ez-notification-' . $notification->id,
            'ez-notification-pending-count-' . $notification->ownerId,
        ]);

        return $this->persistenceHandler->notificationHandler()->updateNotification($notification, $updateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(APINotification $notification): void
    {
        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notification->id,
        ]);

        $this->cache->deleteItems([
            'ez-notification-' . $notification->id,
            'ez-notification-count-' . $notification->ownerId,
            'ez-notification-pending-count-' . $notification->ownerId,
        ]);

        $this->persistenceHandler->notificationHandler()->delete($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function countPendingNotifications(int $ownerId): int
    {
        $cacheItem = $this->cache->getItem('ez-notification-pending-count-' . $ownerId);

        $count = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $count;
        }

        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
        ]);

        $count = $this->persistenceHandler->notificationHandler()->countPendingNotifications($ownerId);

        $cacheItem->set($count);
        $this->cache->save($cacheItem);

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function countNotifications(int $ownerId): int
    {
        $cacheItem = $this->cache->getItem('ez-notification-count-' . $ownerId);

        $count = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $count;
        }

        $this->logger->logCall(__METHOD__, [
            'ownerId' => $ownerId,
        ]);

        $count = $this->persistenceHandler->notificationHandler()->countNotifications($ownerId);

        $cacheItem->set($count);
        $this->cache->save($cacheItem);

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationById(int $notificationId): Notification
    {
        $cacheItem = $this->cache->getItem('ez-notification-' . $notificationId);

        $notification = $cacheItem->get();
        if ($cacheItem->isHit()) {
            return $notification;
        }

        $this->logger->logCall(__METHOD__, [
            'notificationId' => $notificationId,
        ]);

        $notification = $this->persistenceHandler->notificationHandler()->getNotificationById($notificationId);

        $cacheItem->set($notification);
        $this->cache->save($cacheItem);

        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserNotifications(int $userId, int $offset, int $limit): array
    {
        $this->logger->logCall(__METHOD__, [
            'ownerId' => $userId,
            'offset' => $offset,
            'limit' => $limit,
        ]);

        return $this->persistenceHandler->notificationHandler()->loadUserNotifications($userId, $offset, $limit);
    }
}
