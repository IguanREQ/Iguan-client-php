<?php

namespace Iguan\Event\Subscriber\Guard;

use Iguan\Event\Subscriber\Subject;

/**
 * Class SubscriptionFileGuard.
 * A guard based on storing lock files in tmp folder.
 *
 * @author Vishnevskiy Kirill
 */
class SubscriptionFileGuard extends SubscriptionGuard
{
    private $lockFilesLocation;

    /**
     * SubscriptionFileGuard constructor.
     * @param string $appVersion current app version or tag
     * @param string $lockFilesLocation a dir for
     */
    public function __construct($appVersion, $lockFilesLocation = '/tmp')
    {
        parent::__construct($appVersion);
        if (!(is_dir($lockFilesLocation) && is_writable($lockFilesLocation))) {
            throw new \InvalidArgumentException('A lock files location must be valid writable folder.');
        }
        $this->lockFilesLocation = $lockFilesLocation;
    }

    /**
     * Check if guard already has cached subscription for version, passed subject and source tag.
     * Guard will check a lock file for existing.
     *
     * @param Subject $subject to be checked
     * @param string $sourceTag application/script tag
     * @return bool true, if subscription are still valid
     */
    public function hasSubscription(Subject $subject, $sourceTag)
    {
        $lockFile = $this->getLockFilePath($sourceTag, $subject->getEventName(), $subject->getNotifyWay()->hashCode());
        return file_exists($lockFile);
    }

    /**
     * Cache subscription.
     * A current app version, subject and source tag must combine exclusive
     * stamp.
     * Guard will create a lock file for future invalidation.
     *
     * @param Subject $subject to cache
     * @param string $sourceTag application/script tag
     */
    public function persistSubscription(Subject $subject, $sourceTag)
    {
        $lockFile = $this->getLockFilePath($sourceTag, $subject->getEventName(), $subject->getNotifyWay()->hashCode());
        touch($lockFile);
    }

    /**
     * Compose a lock file path.
     * File name is based on source tag, app version,
     * subject token and hash from notify way.
     * File will be in $this->lockFilesLocation dir.
     *
     * @param string $sourceTag application/script tag
     * @param string $subjectToken a token from subject
     * @param string $notifyWayHash a hash of subject notify way
     * @return string
     */
    private function getLockFilePath($sourceTag, $subjectToken, $notifyWayHash)
    {
        return $this->lockFilesLocation . DIRECTORY_SEPARATOR . "iguan_sl_{$sourceTag}_{$this->getAppVersion()}_{$subjectToken}_{$notifyWayHash}.lock";
    }

    /**
     * Check if version of app changed since last execution.
     * Guard will check a content of version file for matching
     * with current app version.
     *
     * @param string $sourceTag application/script tag
     * @return bool true if version updated
     */
    public function isVersionChanged($sourceTag)
    {
        $versionFilePath = $this->getVersionFilePath($sourceTag);
        if (!file_exists($versionFilePath)) return true;

        return file_get_contents($versionFilePath) !== $this->getAppVersion();
    }

    /**
     * Compose version file path based on source tag.
     * File will be in $this->lockFilesLocation dir.
     *
     * @param string $sourceTag application/script tag
     * @return string
     */
    private function getVersionFilePath($sourceTag)
    {
        return $this->lockFilesLocation . DIRECTORY_SEPARATOR . "iguan_sl_{$sourceTag}";
    }

    /**
     * Cache version.
     * A version MUST be cached in pair with source tag.
     * Guard will store a version inside a version file.
     * If there is an old version, all cached subscriptions
     * will be destroyed.
     *
     * @param string $sourceTag application/script tag
     */
    public function persistVersion($sourceTag)
    {
        $versionFilePath = $this->getVersionFilePath($sourceTag);
        if (file_exists($versionFilePath)) {
            $version = file_get_contents($versionFilePath);
            $this->clearOldLocks($version, $sourceTag);
        }

        file_put_contents($versionFilePath, $this->getAppVersion());
    }

    /**
     * Remove all subscription lock files for old app version.
     *
     * @param string $oldVersion
     * @param string $sourceTag application/script tag
     */
    private function clearOldLocks($oldVersion, $sourceTag)
    {
        $files = scandir($this->lockFilesLocation);
        foreach ($files as $file) {
            if (preg_match(preg_quote("/iguan_sl_{$sourceTag}_{$oldVersion}") . '_.*\.lock/', $file, $matches)) {
                unlink($this->lockFilesLocation . DIRECTORY_SEPARATOR . $file);
            }
        }
    }
}