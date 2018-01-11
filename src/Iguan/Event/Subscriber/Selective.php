<?php

namespace Iguan\Event\Subscriber;
use Iguan\Event\Common\EventDescriptor;

/**
 * Interface Selective
 * Can be implemented by custom subject.
 * When notifier gets a selective subject,
 * it asks subject about applicability with descriptor.
 *
 * @author Vishnevskiy Kirill
 */
interface Selective
{
    /**
     * Check if current subject can be notified
     * with passed event.
     *
     * @param EventDescriptor $descriptor
     * @return int one of SubjectNotifier constants
     */
    public function isMatched(EventDescriptor $descriptor);
}