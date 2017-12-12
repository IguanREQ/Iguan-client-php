<?php

namespace Iguan\Event\Subscriber;

use Iguan\Event\Common\EventDescriptor;

class SubjectNotifier
{

    /**
     * @param Subject $subject
     * @param EventDescriptor[] $descriptors
     */
    public function notifyMatched(Subject $subject, array $descriptors)
    {
        $subjectToken = $subject->getToken();

        foreach ($descriptors as $descriptor) {
            if ($descriptor->raisedEvent === null) continue;
            $isMatched = false;
            $token = $descriptor->raisedEvent->getToken();
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

    protected function onMatch(Subject $subject, EventDescriptor $descriptor)
    {
        if (!$descriptor->raisedEvent->isPrevented()) {
            $subject->notifyAll($descriptor);
        }
    }
}