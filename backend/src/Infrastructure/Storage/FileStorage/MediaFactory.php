<?php

namespace App\Infrastructure\Storage\FileStorage;

use App\Domain\File\StaticMedia;
use App\Domain\File\TimedMedia;
use App\Domain\File\MediaFactoryInterface;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * This class maps framework-level uploaded files into domain-driven media objects.
 * It acts as the bridge between infrastructure file handling and domain business rules.
 */
class MediaFactory implements MediaFactoryInterface
{
    private \getID3 $analyser;
    
    public function __construct()
    {
        $this->analyser = new \getID3();
    }
    
    /**
     * Determines whether a file is a timed media (video, audio, etc.) based on its MIME type.
     * * @param mixed $file The infrastructure file instance to evaluate.
     * @return bool True if the file is an audio or video format, false otherwise.
     * @throws \TypeError If the provided object is not an instance of Symfony's UploadedFile.
     */
    public function isTimedMedia(mixed $file): bool
    {
        if (!$file instanceof UploadedFile) {
            throw new \TypeError(
                sprintf(
                    'Expected %s, got %s',
                    UploadedFile::class,
                    get_debug_type($file)
                )
            );
        }


        // Determine MIME type using server-side analysis
        $mime = $file->getMimeType();

        // Fallback to client-provided MIME type if server-side detection fails
        if ($mime === null) {
            $mime = $file->getClientMimeType();
        }

        if ($mime === null) {
            return false;
        }

        return str_starts_with($mime, 'video/') || str_starts_with($mime, 'audio/');
    }

    /**
     * Converts a static asset (e.g., images, PDF documents) into a StaticMedia domain object.
     * @param mixed|UploadedFile $file The infrastructure file instance to convert.
     * @return StaticMedia The resulting domain object enforcing static asset business constraints.
     * @throws \TypeError If the file matches a timed media format instead of a static one.
     */
    public function createStaticMedia(mixed $file, array $expectedTypes = []): StaticMedia
    {
        // Guard Clause: Prevent processing timed media assets (audio/video) as static files
        if ($this->isTimedMedia($file)) {
            throw new \TypeError("You mustn't try to parse a timed media as a static one");
        }

        $mime = $file->getClientMimeType();

        $mime = $file->getMimeType() ?? $file->getClientMimeType();

        if ($mime === null) {
            throw new \InvalidArgumentException(
                'Unable to determine file MIME type.'
            );
        }

        if ($expectedTypes !== [] && !in_array($mime, $expectedTypes, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Not expected type detected for file. Detected: "%s". Expected: %s',
                    $mime,
                    implode(', ', $expectedTypes)
                )
            );
        }

        $extension = $file->guessExtension();

        if ($extension === null) {
            throw new \InvalidArgumentException(
                'Unable to determine file extension.'
            );
        }

        // Generate a cryptographically secure random filename to prevent overwrites and security flaws
        $secureName = Uuid::uuid4()->toString() . '.' . $file->guessExtension();

        return new StaticMedia(
            name: $secureName,
            size: $file->getSize(), // Stored in bytes
            mime: $file->getMimeType(),
            originalName: $file->getClientOriginalName()
        );
    }

    /**
     * Converts a streamable asset (e.g., MP4 videos, MP3 audio) into a TimedMedia domain object.
     * * @param mixed $file The infrastructure file instance to convert.
     * @return TimedMedia The resulting domain object enforcing playable asset business constraints.
     * @throws \DomainException If the asset lacks valid duration metrics or isn't a timed file.
     */
    public function createTimedMedia(mixed $file): TimedMedia
    {
        // Guard Clause: Ensure the file is categorized as a timed media before analysis
        if (!$this->isTimedMedia($file)) {
            throw new \DomainException("Your file must match with a timed one (ex: video, audio... etc.)");
        }

        // Analyze file headers to extract multimedia metadata (duration, ...)
        $info = $this->analyser->analyze($file->getPathname());
        $originalName = $file->getClientOriginalName();

        //  Validate that the media payload possesses a measurable, non-empty runtime
        if (!isset($info['playtime_seconds']) || $info['playtime_seconds'] <= 0) {
            throw new \DomainException(
                sprintf('[File] %s is not a valid timed media', $originalName ?? 'unknown')
            );
        }
        
        // Generate a cryptographically secure random filename
        $secureName = Uuid::uuid4()->toString() . '.' . $file->guessExtension();

        return new TimedMedia(
            name: $secureName,
            size: $file->getSize(), // Stored in bytes
            mime: $file->getMimeType(),
            duration: $info['playtime_seconds'], // Runtime duration in seconds
            originalName: $originalName
        );
    }
}