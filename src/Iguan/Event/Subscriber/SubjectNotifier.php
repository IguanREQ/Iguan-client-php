<?php

namespace Iguan\Event\Subscriber;

use Iguan\Event\Common\EventDescriptor;

class SubjectNotifier
{

    /**
     * @param Subject $subject
     * @param EventDescriptor[] $descriptors
     */
    public static function notifyMatched(Subject $subject, array $descriptors)
    {
        $subjectToken = $subject->getToken();

        foreach ($descriptors as $descriptor) {
            if ($descriptor->raisedEvent === null) continue;
            $isMatched = false;

            if ($descriptor->raisedSubjectToken !== null) {
                if ($subjectToken === $descriptor->raisedSubjectToken) {
                    $isMatched = true;
                }
            } else {
                $token = $descriptor->raisedEvent->getToken();
                if ($subjectToken === $token) {
                    $isMatched = true;
                } else {
                    $subjectTokenChunks = explode('.', $subjectToken);
                    $tokenChunks = explode('.', $token);

                    $matchedChunks = 0;
                    $exceptedChunksCount = count($tokenChunks);
                    for ($i = 0; $i < $exceptedChunksCount; $i++) {
                        $tokenChunk = $tokenChunks[$i];
                        $subjectTokenChunk = $subjectTokenChunks[$i];
                        if ($subjectTokenChunk === $tokenChunk || $subjectTokenChunk === '*') {
                            $matchedChunks++;
                        } else if ($subjectTokenChunk === '#') {
                            $matchedChunks = $exceptedChunksCount;
                            break;
                        } else {
                            break;
                        }
                    }
                    if ($matchedChunks === $exceptedChunksCount) {
                        $isMatched = true;
                    }
                }
            }

            if ($isMatched) {
                $subject->invoke($descriptor);
            }
        }
    }
}