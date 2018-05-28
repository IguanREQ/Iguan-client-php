<?php

namespace Iguan\Event\Subscriber;

use Iguan\Event\Common\EventDescriptor;

/**
 * Class SubjectNotifier
 * Notifier that realize event token parsing.
 *
 * @author Vishnevskiy Kirill
 */
class SubjectNotifier
{
    /**
     * Subject MUST be notified with event
     */
    const HAS_MATCH = 1;

    /**
     * Subject MUST NOT be notified with event
     */
    const NO_MATCH = -1;

    /**
     * Subject CAN be notified with event, but in
     * case of matching with default rules of notifier
     */
    const UNDEFINED = 0;

    /**
     * Notify subject with matched events.
     * Matching logic described at Event::setToken() method
     * comment.
     *
     * @param Subject $subject
     * @param EventDescriptor[] $descriptors
     */
    public function notifyMatched(Subject $subject, array $descriptors)
    {
        //TODO reduce method complexity

        $subjectToken = $subject->getToken();

        foreach ($descriptors as $descriptor) {
            if ($descriptor->raisedEvent === null) continue;
            $isMatched = false;

            if ($subject instanceof Selective) {
                $shouldNotify = $subject->isMatched($descriptor);
                if ($shouldNotify === SubjectNotifier::HAS_MATCH) {
                    $this->onMatch($subject, $descriptor);
                    continue;
                } else if ($shouldNotify === SubjectNotifier::NO_MATCH) {
                    continue;
                }
            }

            $token = $descriptor->raisedEvent->getName();
            //full match
            if ($subjectToken === $token) {
                $isMatched = true;
            } else {
                $subjectTokenChunks = explode('.', $subjectToken);
                $tokenChunks = explode('.', $token);

                $matchedChunks = 0;
                $exceptedChunksCount = count($tokenChunks);
                $isSuperWildcardFound = false;
                for ($i = 0; $i < $exceptedChunksCount; $i++) {
                    if (count($subjectTokenChunks) <= $i) break;

                    $tokenChunk = $tokenChunks[$i];
                    $subjectTokenChunk = $subjectTokenChunks[$i];
                    if ($subjectTokenChunk === $tokenChunk || $subjectTokenChunk === '*') {
                        $matchedChunks++;
                    } else if ($subjectTokenChunk === '#') {
                        $isSuperWildcardFound = true;
                        break;
                    } else {
                        break;
                    }
                }
                if ($isSuperWildcardFound || ($matchedChunks === $exceptedChunksCount && $exceptedChunksCount === count($subjectTokenChunks))) {
                    $isMatched = true;
                }
            }

            if ($isMatched) {
                $this->onMatch($subject, $descriptor);
            }
        }
    }

    /**
     * Notify subject with event only if
     * event is not prevented in previous subjects.
     *
     * @param Subject $subject to be notified
     * @param EventDescriptor $descriptor
     */
    protected function onMatch(Subject $subject, EventDescriptor $descriptor)
    {
        if (!$descriptor->raisedEvent->isPrevented()) {
            $subject->notifyAll($descriptor);
        }
    }
}