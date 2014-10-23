<?php
/**
 * @author Antoine Hedgcock
 */

namespace ZfrRest\Resource\Metadata\Driver;

use Metadata\Driver\AbstractFileDriver;
use ZfrRest\Exception\DomainException;
use ZfrRest\Resource\Metadata\ResourceMetadata;

class PhpDriver extends AbstractFileDriver
{
    /**
     * Parses the content of the file, and converts it to the desired metadata.
     *
     * @param \ReflectionClass $class
     * @param string           $file
     *
     * @return \Metadata\ClassMetadata|null
     */
    protected function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        $metadata = include $file;
        if ($metadata === 1) {
            throw new DomainException(sprintf('The configuration file %s did not return anything.', $file));
        }

        if (!$metadata instanceof ResourceMetadata) {
            throw new DomainException(
                sprintf(
                    'The configuration file %s must return an instance of %s received %s',
                    $file,
                    'ZfrRest\Resource\Metadata\ResourceMetadata',
                    is_object($metadata) ? get_class($metadata) : gettype($metadata)
                )
            );
        }

        if ($metadata->getReflectionClass()->name != $class->name) {
            throw new DomainException(
                sprintf(
                    'Configuration miss-match the file "%s" should return a metadata configuration for "%s"' .
                    'but returned for "%s"',
                    $file,
                    $class->name,
                    $metadata->name
                )
            );
        }

        return $metadata;
    }

    /**
     * Returns the extension of the file.
     *
     * @return string
     */
    protected function getExtension()
    {
        return 'config.php';
    }
}
